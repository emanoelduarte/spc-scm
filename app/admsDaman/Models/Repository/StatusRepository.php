<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class StatusRepository extends DbConnection
{
    /**
     * Recuperar uma Status específico
     * 
     * @return array|bool Status recuperado do banco de dados
     */
    public function getStatus(int|string $id): array|bool
    {
        try {
            $sql = 'SELECT id, name, created_at, updated_at
            FROM adms_daman_acquisition_status
            WHERE id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substiruir os links pelos valores 
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            GenerateLog::generateLog("error", "Status não encontrado", ['id' => (int) $id]);
            die("Status não encontrado " . $err->getMessage());
        }
        return false;
    }

    /**
     * Recuperar todos os Status para preencher selects
     * 
     * @return array|bool Status recuperado do banco de dados
     */
    public function getAllStatusSelect(): array|bool
    {
        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name 
                FROM adms_daman_acquisition_status
                ORDER BY id ASC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Método para recuperar e contar a quantidade de pedido com um status especifico a fim de exibir a quantidade em um card no dasboard
     * Se por acaso não for Super Admi, Admim ou comprador, exibe apenas os números do usuário solicitante
     */
    public function countStatus(): array|bool
    {
        try {
            $params = [];

            // Verificar se o usuário é Admin, Super Admin ou Comprador para exibir se todos ou apenas o status dos pedidos que o usuário fez
            $sqlCheckLevel = "SELECT COUNT(*) FROM adms_daman_users_access_levels 
            WHERE adms_daman_user_id = :check_user_id 
            AND adms_daman_access_level_id IN (1, 2, 5)";

            $stmtCheck = $this->getConnection()->prepare($sqlCheckLevel);
            $stmtCheck->bindValue(':check_user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmtCheck->execute();
            $isPrivileged = $stmtCheck->fetchColumn() > 0;

            $joinCondition = "";

            if (!$isPrivileged) {
                $joinCondition = "AND ado.adms_daman_user_id = :logged_user_id";
                $params['logged_user_id'] = $_SESSION['user_id'];
            }

            $sql = "SELECT 
                        adas.id,
                        adas.name,
                        adas.color,
                        adas.icon,
                        COUNT(ado.id) AS total
                    FROM adms_daman_acquisition_status AS adas
                    LEFT JOIN adms_daman_orders AS ado ON ado.adms_daman_acquisition_status_id = adas.id 
                    {$joinCondition}
                    GROUP BY adas.id, adas.name
                    ORDER BY adas.id ASC";

            $stmt = $this->getConnection()->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue(":{$key}", $value, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            GenerateLog::generateLog("error", "Erro ao contar status.", ['error' => $e->getMessage()]);
            return false;
        }
    }
}