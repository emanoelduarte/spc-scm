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

        // 1. Registra a movimentação
        $sql = "INSERT INTO adms_daman_material_stock_movements
                    (adms_daman_material_stock_id, adms_daman_user_id, 
                     adms_daman_project_id, type, quantity, observation, created_at)
                VALUES 
                    (:stock_id, :user_id, :adms_daman_project_id, :type, :quantity, :observation, :created_at)";

        $stmt = $this->getConnection()->prepare($sql);
        // Substitui o Link
        $stmt->bindValue(':stock_id',    $data['stock_id'],    PDO::PARAM_INT);
        $stmt->bindValue(':user_id',     $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':adms_daman_project_id',  (int) $data['adms_daman_project_id'],  PDO::PARAM_INT);
        $stmt->bindValue(':type',        $data['type'],        PDO::PARAM_STR);
        $stmt->bindValue(':quantity',    (float) $data['quantity']);
        $stmt->bindValue(':observation', $data['observation'] ?? null);
        $stmt->bindValue(':created_at',  date('Y-m-d H:i:s'));
        $stmt->execute();

        // 2. Atualiza a quantidade no estoque
        $operator = $data['type'] === 'entrada' ? '+' : '-';

        $sql = "UPDATE adms_daman_material_stock 
                SET current_quantity = current_quantity {$operator} :quantity,
                    updated_at = :updated_at
                WHERE id = :stock_id";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':quantity',  (float) $data['quantity']);
        $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
        $stmt->bindValue(':stock_id',  $data['stock_id'], PDO::PARAM_INT);
        $stmt->execute();

        $this->getConnection()->commit();
        return true;

    } catch (Exception $e) {
        $this->getConnection()->rollBack();
        GenerateLog::generateLog("error", "Movimentação não registrada.", [
            'error' => $e->getMessage(), "id" => $data['project_id']
        ]);
        return false;
    }
}
}