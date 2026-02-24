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

    /**
     * Cadastrar novo Usuário
     * @param array $data Dados do usuário
     * @return bool Sucesso ou falha
     */
    public function createUser(array $data): bool
    {
        try {
            // Criar a Query para cadastrar os dados
            $sql = 'INSERT INTO adms_daman_users (name, email, username, password, created_at) 
            VALUES (:name, :email, :username, :password, :created_at)';

            // Preparar a query para inserir os dados no banco de dados
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links pelos valores passados no array
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindValue(':username', $data['email'], PDO::PARAM_STR);
            $stmt->bindValue(':password', password_hash($data['password'], PASSWORD_DEFAULT));
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

            // Executar a querry para cadastrar no banco de dados
            return $stmt->execute();
        }catch(Exception $e) {
             // Chamar método para salvar o log
            GenerateLog::generateLog("error", "Usuário tentou cadastrar usuário existente", ['email' => $data['email']]);
            return false;
        }
    }
    
}