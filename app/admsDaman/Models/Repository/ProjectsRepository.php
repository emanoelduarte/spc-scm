<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

/**
 * Repositório responsável por recuperar as obras
 */
class ProjectsRepository extends DbConnection
{
    public function getAllProjects(int $page = 1, int $limitResult = 10): array|false
    {
        // Calcular o registro inicial de cada página exemplo:
        // 2(caso pagina 2) - 1 = 1 * $limite por página = 10
        $offset = max(0, ($page - 1) * $limitResult);

        $sql = 'SELECT id, name, status
        FROM adms_daman_projects
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
     * Recuperar a quantidade de obras para paginação
     * @return int|bool Quantidade de obras encontrados no banco de dados
     */

    public function getAmountProjects(): int|bool
    {
        // Criar Query para recuperar todos os registros no banco de dados
        $sql = 'SELECT COUNT(id) AS amount_records
        FROM adms_daman_projects';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a query
        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    /**
     * Recuperar uma obra específica
     * 
     * @return array|bool Obra recuperada do banco de dados
     */
    public function getProject(int $id): array|bool
    {
        try {
            $sql = 'SELECT id, name, address, description, status, created_at, updated_at
            FROM adms_daman_projects
            WHERE id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substiruir os links pelos valores 
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);

        }catch(Exception $err) {
            GenerateLog::generateLog("error", "Obra não encontrada", ['id' => (int) $id]);
            die("obra não encontrada " . $err->getMessage());
        }
        return false;
    }

    /**
     * Cadastrar nova Obra
     * @param array $data Dados da Obra
     * @return bool Sucesso ou falha
     */
    public function createProject(array $data): bool|int
    {
        try {
            // Criar a Query para cadastrar os dados
            $sql = 'INSERT INTO adms_daman_projects (name, address, description, status, created_at) 
            VALUES (:name, :address, :description, :status, :created_at)';

            // Preparar a query para inserir os dados no banco de dados
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links pelos valores passados no array
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':address', $data['address'], PDO::PARAM_STR);
            $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindValue(':status', 1, PDO::PARAM_INT);
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

            // Executar a querry para cadastrar no banco de dados
             $stmt->execute();

            // Retornar o ID da Obra recém cadastrado
            return $this->getConnection()->lastInsertId();
        }catch(Exception $e) {
             // Chamar método para salvar o log
            GenerateLog::generateLog("error", "Usuário tentou cadastrar obra existente", ['name' => $data['name']]);
            return false;
        }
    }

    /**
     * Editar os dados da Obra
     * @param array $data Dados atualizados da Obra
     * @return bool Sucesso ou Falha | true|false
     */
    public function updateProject(array $data): bool
    {
        // Usar try e catch para gerencia exceção/erro
        try { // Permanece no try se não houver nenhum erro

            // Query para atualizar a Obra
            $sql = "UPDATE adms_daman_projects 
            SET name = :name, address = :address, description = :description, status = :status, updated_at = :updated_at";

            // Condição para indicar qual registo editar
            $sql .= ' WHERE id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            //Substitui os links pelos valores
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':address', $data['address'], PDO::PARAM_STR);
            $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindValue(':status', $data['status'], PDO::PARAM_BOOL);
            $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
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
                GenerateLog::generateLog("error", "Obra não editada.", ['id' => $data['id']]);

                return false;
            }
        } catch (Exception $e) { // Acessa o catch quando houver erro no try
            // Chamar método para salvar o log
            GenerateLog::generateLog("error", "Obra não editada.", ['id' => $data['id'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Deletar uma Obra pelo ID.
     *
     * Este método remove uma Obra específico da tabela `adms_daman_projects`. Em caso de erro, um log é gerado.
     *
     * @param int $id ID da Obra a ser deletada.
     * @return bool `true` se a Obra foi deletada com sucesso ou `false` em caso de erro.
     */
    public function deleteProject(int $id): bool
    {
        // Usar o try e catch para gerenciar exceção/erro
        try {

            // Query para deletar a Obra
            $sql = 'DELETE FROM adms_daman_projects WHERE id = :id LIMIT 1';

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
                GenerateLog::generateLog("error", "Obra não apagada.", ['id' => $id]);
                return false;
            }
        } catch (Exception $e) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Obra não apagada.", ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Recuperar uma obra específica
     * 
     * @return array|bool Obra recuperada do banco de dados
     */
    public function getAllProjectsSelect(): array|bool
    {
        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name 
                FROM adms_daman_projects
                ORDER BY id ASC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar uma obra específica Ativa
     * 
     * @return array|bool Obra recuperada do banco de dados
     */
    public function getAllProjectsSelectActive(): array|bool
    {
        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name, status 
                FROM adms_daman_projects
                WHERE status = :status
                ORDER BY id ASC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir Links por valor
        $stmt->bindValue(':status', 1, PDO::PARAM_INT);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}