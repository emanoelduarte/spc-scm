<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class UsersRepository extends DbConnection
{
    public function getAllUsers(): array|false
    {
        $sql = 'SELECT id, name, email, username
        FROM adms_daman_users
        ORDER BY id DESC';

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a Query
        $stmt->execute();

        return  $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar o usuário
     * 
     * @return array|bool Usuário recuperado do banco de dados
     */
    public function getUser(int $id): array|bool
    {
        try {
            $sql = 'SELECT id, name, email, username, created_at, updated_at
            FROM adms_daman_users
            WHERE id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substiruir os links pelos valores 
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);

        }catch(Exception $err) {
            GenerateLog::generateLog("error", "Usuário não encontrado", ['id' => (int) $id]);
            die("Usuário não encontrado " . $err->getMessage());
        }
        return false;
    }
    
}