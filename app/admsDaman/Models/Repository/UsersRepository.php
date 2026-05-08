<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class UsersRepository extends DbConnection
{
    public function getAllUsers(int $page = 1, int $limitResult = 10): array|false
    {

        // Calcular o registro inicial de cada página exemplo:
        // 2(caso pagina 2) - 1 = 1 * $limite por página = 10
        $offset = max(0, ($page - 1) * $limitResult);

        $sql = 'SELECT id, name, email, username
        FROM adms_daman_users
        ORDER BY id DESC
        LIMIT :limit OFFSET :offset';

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir o link da QUERY pelo valor
        $stmt->bindValue(':limit', $limitResult, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Executar a Query
        $stmt->execute();

        return  $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar a quantidade de usuários para paginação
     * @return int|bool Quantidade de usuários encontrados no banco de dados
     */

    public function getAmountUsers(): int|bool
    {
        // Criar Query para recuperar todos os registros no banco de dados
        $sql = 'SELECT COUNT(id) AS amount_records
        FROM adms_daman_users';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a query
        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
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
        } catch (Exception $err) {
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
    public function createUser(array $data): bool|int
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
            $stmt->execute();

            // Retornar o ID do usuário recém cadastrado
            return $this->getConnection()->lastInsertId();
        } catch (Exception $e) {
            // Chamar método para salvar o log
            GenerateLog::generateLog("error", "Usuário tentou cadastrar usuário existente", ['email' => $data['email']]);
            return false;
        }
    }

    /**
     * Editar os dados do usuário
     * @param array $data Dados atualizados do usuário
     * @return bool Sucesso ou Falha | true|false
     */
    public function updateUser(array $data): bool
    {
        // Usar try e catch para gerencia exceção/erro
        try { // Permanece no try se não houver nenhum erro

            // Query para atualizar o usuário
            $sql = "UPDATE adms_daman_users 
            SET name = :name, email = :email, username = :username, updated_at = :updated_at";

            // Condição para indicar qual registo editar
            $sql .= ' WHERE id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            //Substitui os links pelos valores
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindValue(':username', $data['username'], PDO::PARAM_STR);
            $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();

            // Receber a quantidade de linhas que foram afetadas
            $affectedRowns = $stmt->rowCount();

            //Verificar a quantidade de linhas afetadas
            if ($affectedRowns > 0) {
                return true;
            } else {
                // Chamar método para salvar o log
                GenerateLog::generateLog("error", "Usuário não editado.", ['id' => $data['id']]);

                return false;
            }
        } catch (Exception $e) { // Acessa o catch quando houver erro no try
            // Chamar método para salvar o log
            GenerateLog::generateLog("error", "Usuário não editado.", ['id' => $data['id'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Editar senha do usuário
     * 
     * @return bool Sucesso ou falha
     */
    public function updatePasswordUser(array $data) : bool
    {
        //Usar try catch tratar exceção e erro
        try {
            // Query para editar a senha do usuário
            // QUERY para atualizar usuário
            $sql = 'UPDATE adms_daman_users SET password = :password WHERE id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir Links por valor
            $stmt->bindValue(':password', password_hash($data['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();

            // Recebe a quantidade de linhas afetadas
            $affectedRows = $stmt->rowCount();

            // Verifica a quantidade de linhas afetadas
            if ($affectedRows > 0) {
                return true;
            } else {
                
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Usuário não apagado.", ['id' => $data['id']]);
                
                return false;
            }
        } catch (Exception $e) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Usuário não apagado.", ['id' => $data['id'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Deletar Usuário pelo ID
     * @param int $id ID do usuário a ser deletado
     * @return bool Sucesso ou falha
     */
    public function deleteUser(int $id): bool
    {
        // Usar o try e catch para gerenciar exceção/erro
        try {

            // Query para deletar o usuário
            $sql = 'DELETE FROM adms_daman_users WHERE id = :id LIMIT 1';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substiruir os links pelo valor
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();

            // Verificar o número de linhas afetadas
            $affectedRows = $stmt->rowCount();

            if ($affectedRows > 0) {
                return true;
            } else {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Usuário não apagado.", ['id' => $id]);
                return false;
            }
        } catch (Exception $e) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Usuário não apagado.", ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }
}
