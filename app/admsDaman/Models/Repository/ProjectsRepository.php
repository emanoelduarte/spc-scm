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
    public function getAllProjects(int $page = 1, int $limitResult = 10, ?array $filters = []): array|false
    {
        // Calcular o registro inicial de cada página exemplo:
        // 2(caso pagina 2) - 1 = 1 * $limite por página = 10
        $offset = max(0, ($page - 1) * $limitResult);

        $conditions = [];
        $params = [];

        if (!empty($filters['name'])) {
            $conditions[] = "name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT id, name, status
        FROM adms_daman_projects
        {$where}
        ORDER BY id DESC
        LIMIT :limit OFFSET :offset";

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

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

    public function getAmountProjects(?array $filters = []): int|bool
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['name'])) {
            $conditions[] = "name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Criar Query para recuperar todos os registros no banco de dados
        $sql = "SELECT COUNT(id) AS amount_records
        FROM adms_daman_projects
        {$where}";

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

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
        } catch (Exception $err) {
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
        } catch (Exception $e) {
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
                ORDER BY name ASC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar obras disponíveis conforme uma lista de IDs permitidos.
     *
     * null:
     *      sem restrição por obra.
     *
     * []:
     *      nenhuma obra disponível.
     *
     * [1, 5, 8]:
     *      retornar somente essas obras.
     */
    public function getProjectsSelectByIds(?array $projectIds = null, bool $onlyActive = false): array
{
    $conditions = [];
    $params = [];

    // Aplicar restrição por obras quando o usuário não possuir acesso global
    if ($projectIds !== null) {
        $projectIds = array_values(array_unique(array_filter(array_map('intval', $projectIds))));

        if (empty($projectIds)) {
            return [];
        }

        $placeholders = [];

        foreach ($projectIds as $index => $projectId) {
            $paramName = "project_id_{$index}";
            $placeholders[] = ":{$paramName}";
            $params[$paramName] = $projectId;
        }

        $conditions[] = 'p.id IN (' . implode(', ', $placeholders) . ')';
    }

    // Recuperar somente obras ativas quando solicitado
    if ($onlyActive) {
        $conditions[] = 'p.status = :status';
        $params['status'] = 1;
    }

    $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql = "SELECT p.id, p.name, p.status
            FROM adms_daman_projects AS p
            {$where}
            ORDER BY p.name ASC";

    $stmt = $this->getConnection()->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue(":{$key}", $value, PDO::PARAM_INT);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Recuperar todas as obras que o usuário tem vínculo ou que tem autorização
     * 
     * @return array|bool Obra recuperada do banco de dados
     */
    public function getAllProjectsSelectActive(): array|bool
    {
        $conditions = [];
        $params = [];

        // Verificar se o usuário é Super Admin, Admin, Comprador ou Almoxarife
        $sqlCheckLevel = "SELECT COUNT(*) FROM adms_daman_users_access_levels 
        WHERE adms_daman_user_id = :check_user_id 
        AND adms_daman_access_level_id IN (1, 2, 5, 6)";

        $stmtCheck = $this->getConnection()->prepare($sqlCheckLevel);
        $stmtCheck->bindValue(':check_user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtCheck->execute();

        $isPrivileged = $stmtCheck->fetchColumn() > 0;

        // Apenas obras ativas
        $conditions[] = "status = :status";
        $params['status'] = 1;

        // JOIN apenas se não for privilegiado
        $join = '';

        // Se não for privilegiado
        if (!$isPrivileged) {
            $join = "INNER JOIN adms_daman_user_projects up 
                    ON up.adms_daman_project_id = p.id";

            $conditions[] = "up.adms_daman_user_id = :logged_user_id";

            $params['logged_user_id'] = $_SESSION['user_id'];
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // QUERY para recuperar os registros do banco de dados
        $sql = "SELECT p.id, p.name, p.status 
                FROM adms_daman_projects AS p
                {$join}
                {$where}
                ORDER BY p.id ASC";

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value, PDO::PARAM_INT);
        }

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
