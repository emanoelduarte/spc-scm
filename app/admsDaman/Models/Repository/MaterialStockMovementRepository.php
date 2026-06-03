<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class MaterialStockMovementRepository extends DbConnection
{
    public function createMovement(array $data): bool
    {
        try {
            $this->getConnection()->beginTransaction();

            // 0. Verificar saldo antes de executar saída
            if ($data['type'] === 'output') {
                $sqlCheck = "SELECT current_quantity FROM adms_daman_material_stock WHERE id = :stock_id";
                $stmtCheck = $this->getConnection()->prepare($sqlCheck);
                $stmtCheck->bindValue(':stock_id', $data['stock_id'], PDO::PARAM_INT);
                $stmtCheck->execute();
                $currentQuantity = (float) $stmtCheck->fetchColumn();

                if ((float) $data['quantity'] > $currentQuantity) {
                    GenerateLog::generateLog("warning", "Tentativa de saída sem saldo suficiente.", [
                        'stock_id'          => $data['stock_id'],
                        'quantity_requested' => $data['quantity'],
                        'current_quantity'  => $currentQuantity,
                        'user_id'           => $_SESSION['user_id']
                    ]);
                    return false;
                }
            }

            // 1. Registra a movimentação
            $sql = 'INSERT INTO adms_daman_material_stock_movements
                    (adms_daman_material_stock_id, adms_daman_user_id, 
                     adms_daman_project_id, type, quantity, observation, created_at';

            // Incluir campo motivo de saída do material
            if ($data['type'] === 'output') {
                $sql .= ', reason';
            }

            $sql .= ') VALUES 
                (:stock_id, :user_id, :adms_daman_project_id, :type, :quantity, :observation, :created_at';

            // Incluir campo motivo de saída do material
            if ($data['type'] === 'output') {
                $sql .= ', :reason';
            }
            $sql .= ')';


            $stmt = $this->getConnection()->prepare($sql);
            // Substitui o Link
            $stmt->bindValue(':stock_id',    $data['stock_id'],    PDO::PARAM_INT);
            $stmt->bindValue(':user_id',     $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_project_id',  (int) $data['adms_daman_project_id'],  PDO::PARAM_INT);
            $stmt->bindValue(':type',        $data['type'],        PDO::PARAM_STR);
            $stmt->bindValue(':quantity',    (float) $data['quantity']);
            $stmt->bindValue(':observation', $data['observation'] ?? null);
            $stmt->bindValue(':created_at',  date('Y-m-d H:i:s'));

            // Substituir link campo motivo de saída do material
            if ($data['type'] === 'output') {
                $stmt->bindValue(':reason',      $data['reason'],        PDO::PARAM_STR);
            }

            $stmt->execute();

            // 2. Atualiza a quantidade no estoque
            $operator = $data['type'] === 'input' ? '+' : '-';

            $sql = "UPDATE adms_daman_material_stock 
                SET current_quantity = current_quantity {$operator} :quantity,
                    updated_at = :updated_at
                WHERE id = :stock_id";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':quantity',   (float) $data['quantity']);
            $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
            $stmt->bindValue(':stock_id',   $data['stock_id'], PDO::PARAM_INT);
            $stmt->execute();

            // 3. Se for transferência, cria entrada na obra de destino
            if ($data['type'] === 'output' && ($data['reason'] ?? '') === 'transfer') {

                $sqlCheck = "SELECT id FROM adms_daman_material_stock 
                 WHERE name = :name 
                 AND adms_daman_project_id = :project_id";

                $stmtCheck = $this->getConnection()->prepare($sqlCheck);
                $stmtCheck->bindValue(':name',       $data['item_name']);
                $stmtCheck->bindValue(':project_id', $data['adms_daman_project_id'], PDO::PARAM_INT);
                $stmtCheck->execute();
                $existingItem = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                // ID do item na obra de destino
                $destinationStockId = null;

                if ($existingItem) {
                    $sqlTransfer = "UPDATE adms_daman_material_stock 
                        SET current_quantity = current_quantity + :quantity,
                            updated_at = :updated_at
                        WHERE id = :id";

                    $stmtTransfer = $this->getConnection()->prepare($sqlTransfer);
                    $stmtTransfer->bindValue(':quantity',   (float) $data['quantity']);
                    $stmtTransfer->bindValue(':updated_at', date('Y-m-d H:i:s'));
                    $stmtTransfer->bindValue(':id',         $existingItem['id'], PDO::PARAM_INT);
                    $stmtTransfer->execute();

                    $destinationStockId = $existingItem['id'];
                } else {
                    $sqlInsert = "INSERT INTO adms_daman_material_stock 
                        (adms_daman_project_id, adms_daman_category_id, adms_daman_measurement_units_id, name, current_quantity, min_quantity, created_at)
                      SELECT :project_id, :adms_daman_category_id, adms_daman_measurement_units_id, name, :quantity, min_quantity, :created_at
                      FROM adms_daman_material_stock
                      WHERE id = :stock_id";

                    $stmtInsert = $this->getConnection()->prepare($sqlInsert);
                    $stmtInsert->bindValue(':project_id', $data['adms_daman_project_id'], PDO::PARAM_INT);
                    $stmtInsert->bindValue(':adms_daman_category_id', $data['adms_daman_category_id'], PDO::PARAM_INT);
                    $stmtInsert->bindValue(':quantity',   (float) $data['quantity']);
                    $stmtInsert->bindValue(':created_at', date('Y-m-d H:i:s'));
                    $stmtInsert->bindValue(':stock_id',   $data['stock_id'], PDO::PARAM_INT);
                    $stmtInsert->execute();

                    $destinationStockId = $this->getConnection()->lastInsertId();
                }

                // Registra entrada na movimentação da obra de destino
                $sqlMovement = "INSERT INTO adms_daman_material_stock_movements
                        (adms_daman_material_stock_id, adms_daman_user_id, adms_daman_project_id, type, reason, quantity, observation, created_at)
                    VALUES 
                        (:stock_id, :user_id, :project_id, 'input', 'transfer', :quantity, :observation, :created_at)";

                $stmtMovement = $this->getConnection()->prepare($sqlMovement);
                $stmtMovement->bindValue(':stock_id',    $destinationStockId, PDO::PARAM_INT);
                $stmtMovement->bindValue(':user_id',     $_SESSION['user_id'], PDO::PARAM_INT);
                $stmtMovement->bindValue(':project_id',  $data['adms_daman_project_id'], PDO::PARAM_INT);
                $stmtMovement->bindValue(':quantity',    (float) $data['quantity']);
                $stmtMovement->bindValue(':observation', $data['observation'] ?? null);
                $stmtMovement->bindValue(':created_at',  date('Y-m-d H:i:s'));
                $stmtMovement->execute();
            }

            // commit só aqui, após tudo ter sido executado
            $this->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            GenerateLog::generateLog("error", "Movimentação não registrada.", [
                'error' => $e->getMessage(),
                "id" => $data['adms_daman_project_id']
            ]);
            return false;
        }
    }

    public function getMaterialMovement(int $id): array
    {

        $sql = "SELECT admsm.adms_daman_material_stock_id, admsm.adms_daman_project_id, admsm.adms_daman_user_id, admsm.type, admsm.reason, admsm.quantity, admsm.observation, admsm.created_at,
        adp.name AS project_name,
        adu.name AS user_name
        FROM adms_daman_material_stock_movements AS admsm
        INNER JOIN adms_daman_projects AS adp ON adp.id = admsm.adms_daman_project_id
        INNER JOIN adms_daman_users AS adu ON adu.id = admsm.adms_daman_user_id 
        WHERE adms_daman_material_stock_id = :adms_daman_material_stock_id";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->bindValue(':adms_daman_material_stock_id',  $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
