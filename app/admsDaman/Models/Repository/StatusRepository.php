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

        }catch(Exception $err) {
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
     */
    public function countStatus(): array|bool
    {
        try {
            $sql = "SELECT 
                        adas.id,
                        adas.name,
                        adas.color,
                        adas.icon,
                        COUNT(ado.id) AS total
                    FROM adms_daman_acquisition_status AS adas
                    LEFT JOIN adms_daman_orders AS ado ON ado.adms_daman_acquisition_status_id = adas.id
                    GROUP BY adas.id, adas.name
                    ORDER BY adas.id ASC";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            GenerateLog::generateLog("error", "Erro ao contar status.", ['error' => $e->getMessage()]);
            return false;
        }
    }
}