<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class MaterialStockRepository extends DbConnection
{
    /**
     * Método para listar Itens
     * 
     * @return array
     */
    public function getAllMaterialStock(int $page = 1, int $limitResult = 10, ?array $filters = []): array|bool
    {
        $offset = max(0, ($page - 1) * $limitResult);

        $conditions = [];
        $params = [];

        // Verificar se o usuário tem nível privilegiado
        $sqlCheckLevel = "SELECT COUNT(*) FROM adms_daman_users_access_levels AS adual
                      INNER JOIN adms_daman_access_levels AS adal ON adal.id = adual.adms_daman_access_level_id
                      WHERE adual.adms_daman_user_id = :check_user_id 
                      AND adal.id IN (1, 2, 5, 6)"; // Ajustar os ids conforme seus níveis

        $stmtCheck = $this->getConnection()->prepare($sqlCheckLevel);
        $stmtCheck->bindValue(':check_user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtCheck->execute();
        $isPrivileged = $stmtCheck->fetchColumn() > 0;

        if (!$isPrivileged) {
            // Verificar se tem obra vinculada
            $sqlCheckProject = "SELECT COUNT(*) FROM adms_daman_user_projects
                            WHERE adms_daman_user_id = :user_id";
            $stmtProject = $this->getConnection()->prepare($sqlCheckProject);
            $stmtProject->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmtProject->execute();
            $hasProject = $stmtProject->fetchColumn() > 0;

            if (!$hasProject) {
                return ['no_project' => true];
            }

            // Filtra só pelas obras do usuário
            $conditions[] = "ams.adms_daman_project_id IN (
            SELECT adms_daman_project_id 
            FROM adms_daman_user_projects 
            WHERE adms_daman_user_id = :logged_user_id
        )";
            $params['logged_user_id'] = $_SESSION['user_id'];
        }

        // Mapeamento campo form → coluna banco
        $map = [
            'id_number'                => 'ams.id',
            'adms_daman_project_id'    => 'ams.adms_daman_project_id',
            'adms_daman_category_id'   => 'ams.adms_daman_category_id',
        ];

        foreach ($map as $field => $column) {
            if (!empty($filters[$field])) {
                $conditions[] = "{$column} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        if (!empty($filters['data_inicio'])) {
            $conditions[] = "ams.created_at >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'] . ' 00:00:00';
        }

        if (!empty($filters['data_fim'])) {
            $conditions[] = "ams.created_at <= :data_fim";
            $params['data_fim'] = $filters['data_fim'] . ' 23:59:59';
        }

        if (!empty($filters['name'])) {
            $conditions[] = "ams.name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT ams.id, ams.adms_daman_project_id, adms_daman_category_id, ams.name, 
                   ams.current_quantity, ams.min_quantity, ams.created_at,
                   admu.name AS measurement_unit,
                   adp.name AS project_name
            FROM adms_daman_material_stock AS ams
            INNER JOIN adms_daman_measurement_units AS admu ON admu.id = ams.adms_daman_measurement_units_id
            INNER JOIN adms_daman_projects AS adp ON adp.id = ams.adms_daman_project_id
            {$where}
            ORDER BY name ASC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->bindValue(':limit', $limitResult, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAmountMaterials(?array $filters = []): int|bool
    {
        $conditions = [];
        $params = [];

        // Verificar se o usuário tem nível privilegiado
        $sqlCheckLevel = "SELECT COUNT(*) FROM adms_daman_users_access_levels AS adual
                      INNER JOIN adms_daman_access_levels AS adal ON adal.id = adual.adms_daman_access_level_id
                      WHERE adual.adms_daman_user_id = :check_user_id 
                      AND adal.id IN (1, 2, 5, 6)";

        $stmtCheck = $this->getConnection()->prepare($sqlCheckLevel);
        $stmtCheck->bindValue(':check_user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtCheck->execute();
        $isPrivileged = $stmtCheck->fetchColumn() > 0;

        if (!$isPrivileged) {
            $sqlCheckProject = "SELECT COUNT(*) FROM adms_daman_user_projects
                            WHERE adms_daman_user_id = :user_id";
            $stmtProject = $this->getConnection()->prepare($sqlCheckProject);
            $stmtProject->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmtProject->execute();
            $hasProject = $stmtProject->fetchColumn() > 0;

            if (!$hasProject) {
                return 0;
            }

            $conditions[] = "adms_daman_project_id IN (
            SELECT adms_daman_project_id 
            FROM adms_daman_user_projects 
            WHERE adms_daman_user_id = :logged_user_id
        )";
            $params['logged_user_id'] = $_SESSION['user_id'];
        }

        if (!empty($filters['name'])) {
            $conditions[] = "name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT COUNT(id) AS amount_records
            FROM adms_daman_material_stock
            {$where}";

        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    public function getUniqueMaterial(int $materialId)
    {
        $sql = "SELECT ams.id, ams.adms_daman_measurement_units_id, ams.adms_daman_project_id, adms_daman_category_id, ams.name, ams.current_quantity, ams.min_quantity, ams.obs, ams.created_at,
        admu.name AS measurement_unit,
        adp.name AS project_name
        FROM adms_daman_material_stock AS ams
        INNER JOIN adms_daman_measurement_units AS admu ON admu.id = ams.adms_daman_measurement_units_id
        INNER JOIN adms_daman_projects AS adp ON adp.id = ams.adms_daman_project_id
        WHERE ams.id = :id";

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir Link
        $stmt->bindValue(':id', $materialId, PDO::PARAM_INT);

        // Executar a Query
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createMaterial(array $data): int|bool
    {
        // Usar try e catch para gerenciar exceção/erro
        try { // Permanece no try se não houver nenhum erro

            // QUERY cadastrar material
            $sql = 'INSERT INTO adms_daman_material_stock (name, adms_daman_measurement_units_id, adms_daman_project_id, adms_daman_category_id, current_quantity, min_quantity, obs, created_at) VALUES (:name, :adms_daman_measurement_units_id, :adms_daman_project_id, :adms_daman_category_id, :current_quantity, :min_quantity, :obs, :created_at)';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            $descriptionUpper = mb_convert_case($data['name'], MB_CASE_TITLE, 'UTF-8');

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':name', $descriptionUpper, PDO::PARAM_STR);
            $stmt->bindValue(':adms_daman_measurement_units_id', $data['adms_daman_measurement_units_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_project_id', $data['adms_daman_project_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_category_id', $data['adms_daman_category_id'], PDO::PARAM_INT);
            $stmt->bindValue(':current_quantity', (float) $data['quantity']);
            $stmt->bindValue(':min_quantity', (float) $data['min_quantity']);
            $stmt->bindValue(':obs', $data['obs'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

            // Executar a QUERY
            $stmt->execute();

            // Retornar o ID do material recém cadastrado
            return $this->getConnection()->lastInsertId();
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Material não cadastrado.", ['material' => $data['name'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Editar os dados do material
     * @param array $data Dados atualizados do material
     * @return bool Sucesso ou Falha | true|false
     */
    public function updateMaterial(array $data): bool
    {
        // Usar try e catch para gerencia exceção/erro
        try { // Permanece no try se não houver nenhum erro

            // Query para atualizar o material
            $sql = "UPDATE adms_daman_material_stock
            SET name = :name, adms_daman_measurement_units_id = :adms_daman_measurement_units_id, adms_daman_category_id = :adms_daman_category_id, min_quantity = :min_quantity, obs = :obs, updated_at = :updated_at";

            // Condição para indicar qual registo editar
            $sql .= ' WHERE id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            $descriptionUpper = mb_convert_case($data['name'], MB_CASE_TITLE, 'UTF-8');

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':name', $descriptionUpper, PDO::PARAM_STR);
            $stmt->bindValue(':adms_daman_measurement_units_id', $data['adms_daman_measurement_units_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_category_id', $data['adms_daman_category_id'], PDO::PARAM_INT);
            $stmt->bindValue(':min_quantity', (float) $data['min_quantity']);
            $stmt->bindValue(':obs', $data['obs'] ?? null, PDO::PARAM_STR);
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
                GenerateLog::generateLog("error", "Material não editado.", ['id' => $data['id']]);

                return false;
            }
        } catch (Exception $e) { // Acessa o catch quando houver erro no try
            // Chamar método para salvar o log
            GenerateLog::generateLog("error", "Material não editado.", ['id' => $data['id'], 'error' => $e->getMessage()]);

            return false;
        }
    }
}