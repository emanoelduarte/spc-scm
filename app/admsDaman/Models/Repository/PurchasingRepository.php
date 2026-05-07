<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Helpers\NormalizeDecimal;
use App\admsDaman\Models\Services\DbConnection;
use DateTime;
use Exception;
use PDO;

class PurchasingRepository extends DbConnection
{
    /**
     * Recuperar todos as compras com paginação.
     *
     * Este método retorna uma lista de compras da tabela `adms_daman_purchasing`, com suporte à paginação.
     *
     * @param int $page Número da página para recuperação de compras (começa do 1).
     * @param int $limitResult Número máximo de resultados por página.
     * @return array Lista de compras recuperados do banco de dados.
     */
    public function getAllPurchasings(int $page = 1, int $limitResult = 10, ?array $filters = []): array|false
    {

        // Calcular o registro inicial de cada página exemplo:
        // 2(caso pagina 2) - 1 = 1 * $limite por página = 10
        $offset = max(0, ($page - 1) * $limitResult);

        $conditions = [];
        $params = [];

        // Mapeamento campo form → coluna banco
        $map = [
            'purchasing_number' => 'adpu.id',
            'order_number' => 'adpu.adms_daman_order_id',
            'adms_daman_project_id' => 'adpu.adms_daman_project_id',
            'adms_daman_acquisition_purchasing_status_id' => 'adpu.adms_daman_acquisition_purchasing_status_id',
        ];

        foreach ($map as $field => $column) {
            if (!empty($filters[$field])) {
                $conditions[] = "{$column} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        // Filtro por intervalo de datas
        if (!empty($filters['data_inicio'])) {
            $conditions[] = "adpu.created_at >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'] . ' 00:00:00';
        }

        if (!empty($filters['data_fim'])) {
            $conditions[] = "adpu.created_at <= :data_fim";
            $params['data_fim'] = $filters['data_fim'] . ' 23:59:59';
        }

        // Pesquisar por Fornecedor
        if (!empty($filters['legal_name'])) {
            $conditions[] = "EXISTS (
                SELECT 1 
                FROM adms_daman_suppliers ads
                WHERE ads.id = adpu.adms_daman_supplier_id
                AND ads.legal_name LIKE :legal_name
            )";

            $params['legal_name'] = '%' . $filters['legal_name'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT adpu.id, adpu.adms_daman_order_id,
                adp.name AS project_name,
                ads.trade_name,
                adu.name AS buyer_name,
                adaps.id AS purchasing_status_id,
                adaps.name AS purchasing_status
                FROM adms_daman_purchasings AS adpu
                INNER JOIN adms_daman_suppliers AS ads ON ads.id = adpu.adms_daman_supplier_id
                INNER JOIN adms_daman_projects AS adp ON adp.id = adpu.adms_daman_project_id
                INNER JOIN adms_daman_users AS adu ON adu.id = adpu.adms_daman_user_id
                INNER JOIN adms_daman_acquisition_purchasing_status AS adaps ON adaps.id = adpu.adms_daman_acquisition_purchasing_status_id 

                {$where}
                ORDER BY adpu.id DESC
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
     * Recuperar a quantidade de compras para paginação
     * @return int|bool Quantidade de compras encontrados no banco de dados
     */

    public function getAmountPurchasings(?string $purchasing_number = null): int
    {
        $conditions = [];
        $params = [];

        if (!empty($purchasing_number)) {
            $conditions[] = "id = :purchasing_number";
            $params['purchasing_number'] = $purchasing_number;
        }

        if (!empty($orderNumber)) {
            $conditions[] = "id = :order_number";
            $params['order_number'] = $orderNumber;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Criar Query para recuperar todos os registros no banco de dados
        $sql = "SELECT COUNT(id) AS amount_records
         FROM adms_daman_purchasings
         {$where}";

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value, PDO::PARAM_INT);
        }

        // Executar a query
        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    /**
     * Recuperar uma compra específica com seus respectivos itens
     * 
     * @return array|bool compra recuperado do banco de dados
     */

    public function getPurchasing(int $idPurchasing): array|bool
    {

        try {
            $sql = 'SELECT adpu.id, adpu.expected_receipt_date, adpu.service, adpu.delivery_address, adpu.delivery_value, adpu.discount, adpu.created_at, adpu.updated_at,
                adp.name AS project_name,
                ads.legal_name,
                ado.id AS order_number,
                adu.name AS buyer_name,
                adpm.name AS payment_method,
                adat.name AS acquisition_type,
                adaps.id AS purchasing_status_id,
                adaps.name AS purchasing_status
        FROM adms_daman_purchasings AS adpu
        INNER JOIN adms_daman_suppliers AS ads ON ads.id = adpu.adms_daman_supplier_id
        INNER JOIN adms_daman_projects AS adp ON adp.id = adpu.adms_daman_project_id
        INNER JOIN adms_daman_orders AS ado ON ado.id = adpu.adms_daman_order_id
        INNER JOIN adms_daman_users AS adu ON adu.id = adpu.adms_daman_user_id 
        INNER JOIN adms_daman_payment_methods AS adpm ON adpm.id = adpu.adms_daman_user_id 
        INNER JOIN adms_daman_acquisition_types AS adat ON adat.id = adpu.adms_daman_acquisition_types_id 
        INNER JOIN adms_daman_acquisition_purchasing_status AS adaps ON adaps.id = adpu.adms_daman_acquisition_purchasing_status_id 
        WHERE adpu.id = :id';

            // Preparar a query
            $stmt = $this->getConnection()->prepare($sql);

            //Substituir Links
            $stmt->bindValue(':id', $idPurchasing, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            GenerateLog::generateLog("error", "Compra não encontrada", ['id' => (int) $idPurchasing]);
            die("Compra não encontrada!" . $err->getMessage());
        }
        return false;
    }

    /**
     * Método para buscar os itens da Compra
     */
    public function getItems(int $idPurchasing): array|bool
    {

        try {

            $sql = "SELECT adpi.adms_daman_purchasing_id, adpi.description, adpi.purchased_quantity, adpi.unit_price, adpi.created_at, adpi.updated_at,
                admu.name AS measurement_unit
                FROM adms_daman_purchasing_items AS adpi
                INNER JOIN adms_daman_measurement_units AS admu ON admu.id=adpi.adms_daman_measurement_units_id
                WHERE adms_daman_purchasing_id = :idPurchasing";

            $stmt = $this->getConnection()->prepare($sql);

            $stmt->bindValue(':idPurchasing', $idPurchasing, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "O pedido não possui itens.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return false;
        }

        return false;
    }

    public function generatePurchasing(array $data): int|bool
    {
        try {
            $FormatedDate = DateTime::createFromFormat('d/m/Y', $data['expected_receipt_date'])->format('Y-m-d');


            $sql = 'INSERT INTO adms_daman_purchasings 
                    (adms_daman_supplier_id, adms_daman_user_id, adms_daman_acquisition_types_id, adms_daman_acquisition_purchasing_status_id, expected_receipt_date, adms_daman_order_id, adms_daman_project_id, service, delivery_address, adms_daman_payment_methods_id, created_at';

            if (!$data['delivery_value'] == '') {
                $sql .= ', delivery_value';
            }

            if (!$data['discount_value'] == '') {
                $sql .= ', discount';
            }
            $sql .= ') VALUES (:adms_daman_supplier_id, :adms_daman_user_id, :adms_daman_acquisition_types_id, :adms_daman_acquisition_purchasing_status_id, :expected_receipt_date, :adms_daman_order_id, :adms_daman_project_id, :service, :delivery_address, :adms_daman_payment_methods_id, :created_at';

            if (!$data['delivery_value'] == '') {
                $sql .= ', :delivery_value';
            }

            if (!$data['discount_value'] == '') {
                $sql .= ', :discount';
            }

            $sql .= ')';

            $stmt = $this->getConnection()->prepare($sql);

            $stmt->bindValue(':adms_daman_supplier_id', $data['adms_daman_supplier_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_user_id', $data['adms_daman_user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_acquisition_types_id', $data['adms_daman_acquisition_types_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_acquisition_purchasing_status_id', 1, PDO::PARAM_INT);
            $stmt->bindValue(':expected_receipt_date', $FormatedDate);
            $stmt->bindValue(':adms_daman_order_id', $data['adms_daman_order_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_project_id', $data['adms_daman_project_id'], PDO::PARAM_INT);
            $stmt->bindValue(':service', $data['service'], PDO::PARAM_STR);
            $stmt->bindValue(':delivery_address', $data['delivery_address'], PDO::PARAM_STR);
            $stmt->bindValue(':adms_daman_payment_methods_id', $data['adms_daman_payment_methods_id'], PDO::PARAM_INT);
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));


            if (!$data['delivery_value'] == '') {
                $stmt->bindValue(':delivery_value', NormalizeDecimal::normalizeDecimal($data['delivery_value']));
            }
            if (!$data['discount_value'] == '') {
                $stmt->bindValue(':discount', NormalizeDecimal::normalizeDecimal($data['discount_value']));
            }

            // Executar a QUERY
            $result = $stmt->execute();


            if ($result) {
                // 2. PEGA O ID AQUI (imediatamente após o insert do pedido)
                $purchasingId = $this->getConnection()->lastInsertId();

                // Chama o método para cadastar os itens
                $this->generateItemsPurchasing($data, $purchasingId);
            }

            // retorna o ultimo id inserido de pedido
            return $purchasingId;
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra não cadastrada.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function generateItemsPurchasing(array $dataForm, $purchasingId): bool
    {

        try {
            $selectedItems = [];

            foreach ($dataForm['items'] as $item) {
                if (!empty($item['selected_item'])) {
                    $selectedItems[] = $item;
                }
            }

            foreach ($selectedItems as $item) {
                $data = [
                    'description' => $item['description'],
                    'adms_daman_measurement_units_id' => $item['adms_daman_measurement_units_id'],
                    'purchased_quantity' => $item['purchased_quantity'],
                    'unit_price' => $item['unit_price'],
                ];

                // salvar no banco

                // QUERY cadastrar pedido
                $sql = 'INSERT INTO adms_daman_purchasing_items 
                    (adms_daman_purchasing_id, description, adms_daman_measurement_units_id, purchased_quantity, unit_price, created_at) 
                    VALUES (:adms_daman_purchasing_id, :description, :adms_daman_measurement_units_id, :purchased_quantity, :unit_price, :created_at)';

                // Preparar a QUERY
                $stmt = $this->getConnection()->prepare($sql);

                // Substituir os links da QUERY pelo valor
                $stmt->bindValue(':adms_daman_purchasing_id', $purchasingId, PDO::PARAM_INT);
                $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
                $stmt->bindValue(':adms_daman_measurement_units_id', $data['adms_daman_measurement_units_id'], PDO::PARAM_INT);
                $stmt->bindValue(':purchased_quantity', (float)$data['purchased_quantity']);
                $stmt->bindValue(':unit_price', (float)$data['unit_price']);
                $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

                $stmt->execute();
            }
            return true;
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Itens não cadastrados.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return false;
        }

        return true;
    }

    /**
     * Metodo para Cancelar a Compra
     */
    public function updateCancelPurchasing(int $idPurchasing): bool
    {
        try {

            $sql = "UPDATE adms_daman_purchasings SET adms_daman_acquisition_purchasing_status_id = :adms_daman_acquisition_purchasing_status_id, updated_at = :updated_at
        WHERE id = :id";

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':adms_daman_acquisition_purchasing_status_id', 2, PDO::PARAM_INT);
            $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
            $stmt->bindValue(':id', $idPurchasing, PDO::PARAM_INT);

            // Executar a QUERY
            $stmt->execute();

            // Verificar o número de linhas afetadas
            if ($stmt->rowCount() > 0) {
                return true;
            } else {

                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Compra não cancelada.", ['id_compra' => $idPurchasing]);

                return false;
            }
        } catch (Exception $e) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra não cancelada.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Método para deletar compras
     */
    public function deletePurchasing(int $idPurchasing): bool
    {
        // Usar o try e catch para gerenciar exceção/erro
        try {

            // Query para deletar o compra
            $sql = 'DELETE FROM adms_daman_purchasings  WHERE id = :id LIMIT 1';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substiruir os links pelo valor
            $stmt->bindParam(':id', $idPurchasing, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();

            // Verificar o número de linhas afetadas
            $affectedRows = $stmt->rowCount();

            if ($affectedRows > 0) {
                return true;
            } else {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Pedido não apagado.", ['id' => $idPurchasing]);
                return false;
            }
        } catch (Exception $e) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra não apagada.", ['id' => $idPurchasing, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Recuperar uma obra específica
     * 
     * @return array|bool Obra recuperada do banco de dados
     */
    public function getAllPurchasingStatusSelect(): array|bool
    {
        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name 
                FROM adms_daman_acquisition_purchasing_status
                ORDER BY id ASC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
