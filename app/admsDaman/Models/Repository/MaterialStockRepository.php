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
    public function getAllMaterialStock(int $page = 1, int $limitResult = 10, ?array $filters = []): array
    {
        // Calcular o registro inicial de cada página exemplo:
        $offset = max(0, ($page - 1) * $limitResult);

        $conditions = [];
        $params = [];

        // Mapeamento campo form → coluna banco
        $map = [
            'id_number' => 'ams.id',
            'adms_daman_project_id' => 'ams.adms_daman_project_id',
        ];

        foreach ($map as $field => $column) {
            if (!empty($filters[$field])) {
                $conditions[] = "{$column} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        // Filtro por intervalo de datas
        if (!empty($filters['data_inicio'])) {
            $conditions[] = "ado.created_at >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'] . ' 00:00:00';
        }

        if (!empty($filters['data_fim'])) {
            $conditions[] = "ado.created_at <= :data_fim";
            $params['data_fim'] = $filters['data_fim'] . ' 23:59:59';
        }

        if (!empty($filters['name'])) {
            $conditions[] = "ams.name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT ams.id, ams.adms_daman_project_id, ams.name, ams.current_quantity, ams.min_quantity, ams.created_at,
        admu.name AS measurement_unit,
        adp.name AS project_name
        FROM adms_daman_material_stock AS ams
        INNER JOIN adms_daman_measurement_units AS admu ON admu.id = ams.adms_daman_measurement_units_id
        INNER JOIN adms_daman_projects AS adp ON adp.id = ams.adms_daman_project_id
        {$where}
        ORDER BY name ASC
        LIMIT :limit OFFSET :offset";

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        // Substituir o link da QUERY pelo valor
        $stmt->bindValue(':limit', $limitResult, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Executar a Query
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar a quantidade de materiais para paginação
     * @return int|bool Quantidade de materiais encontrados no banco de dados
     */

    public function getAmountMaterials(?array $filters = []): int|bool
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
        FROM adms_daman_material_stock
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

    public function getUniqueMaterial(int $materialId)
    {
        $sql = "SELECT ams.id, ams.adms_daman_measurement_units_id, ams.adms_daman_project_id, ams.name, ams.current_quantity, ams.min_quantity, ams.created_at,
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
            $sql = 'INSERT INTO adms_daman_material_stock (name, adms_daman_measurement_units_id, adms_daman_project_id, current_quantity, min_quantity, created_at) VALUES (:name, :adms_daman_measurement_units_id, :adms_daman_project_id, :current_quantity, :min_quantity, :created_at)';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':adms_daman_measurement_units_id', $data['adms_daman_measurement_units_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_project_id', $data['adms_daman_project_id'], PDO::PARAM_INT);
            $stmt->bindValue(':current_quantity', (float) $data['quantity']);
            $stmt->bindValue(':min_quantity', (float) $data['min_quantity']);
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
            SET name = :name, adms_daman_measurement_units_id = :adms_daman_measurement_units_id, min_quantity = :min_quantity, updated_at = :updated_at";

            // Condição para indicar qual registo editar
            $sql .= ' WHERE id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            //Substitui os links pelos valores
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':adms_daman_measurement_units_id', $data['adms_daman_measurement_units_id'], PDO::PARAM_INT);
            $stmt->bindValue(':min_quantity', (float) $data['min_quantity']);
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
