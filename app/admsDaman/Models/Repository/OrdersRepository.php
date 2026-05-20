<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use DateTime;
use Exception;
use PDO;

class OrdersRepository extends DbConnection
{
    /**
     * Recuperar todos os pedidos com paginação.
     *
     * Este método retorna uma lista de pedidos da tabela `adms_daman_orders`, com suporte à paginação.
     *
     * @param int $page Número da página para recuperação de pedidos (começa do 1).
     * @param int $limitResult Número máximo de resultados por página.
     * @return array Lista de pedidos recuperados do banco de dados.
     */
    public function getAllOrders(int $page = 1, int $limitResult = 10, ?array $filters = [])
    {
        $offset = max(0, ($page - 1) * $limitResult);

        $conditions = [];
        $params = [];

        // Verificar se o usuário é Admin, Super Admin ou Comprador
        $sqlCheckLevel = "SELECT COUNT(*) FROM adms_daman_users_access_levels 
            WHERE adms_daman_user_id = :check_user_id 
            AND adms_daman_access_level_id IN (1, 2, 5)";

        $stmtCheck = $this->getConnection()->prepare($sqlCheckLevel);
        $stmtCheck->bindValue(':check_user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtCheck->execute();
        $isPrivileged = $stmtCheck->fetchColumn() > 0;

        if (!$isPrivileged) {
            $conditions[] = "ado.adms_daman_user_id = :logged_user_id";
            $params['logged_user_id'] = $_SESSION['user_id'];
        }

        // Mapeamento campo form → coluna banco
        $map = [
            'order_number' => 'ado.id',
            'adms_daman_project_id' => 'ado.adms_daman_project_id',
            'adms_daman_acquisition_status_id' => 'ado.adms_daman_acquisition_status_id',
            'adms_daman_category_id' => 'ado.adms_daman_category_id',
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

        // Pesquisar por item
        if (!empty($filters['description'])) {
            $conditions[] = "EXISTS (
        SELECT 1 
        FROM adms_daman_order_items aoi
        WHERE aoi.adms_daman_order_id = ado.id
        AND aoi.description LIKE :description
    )";

            $params['description'] = '%' . $filters['description'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT 
                ado.id AS pedido_id, 
                ado.adms_daman_acquisition_types_id, 
                ado.adms_daman_project_id, 
                ado.adms_daman_acquisition_status_id, 
                ado.created_at,
                adp.name AS project_name, 
                ados.id AS status_id,
                ados.name AS status_name,
                adot.name AS name_tape,
                adc.name AS category_name, adc.id AS categoria_id
            FROM adms_daman_orders AS ado
            INNER JOIN adms_daman_projects AS adp ON adp.id = ado.adms_daman_project_id
            INNER JOIN adms_daman_acquisition_status AS ados ON ados.id = ado.adms_daman_acquisition_status_id
            INNER JOIN adms_daman_acquisition_types AS adot ON adot.id=ado.adms_daman_acquisition_types_id
            INNER JOIN adms_daman_categories AS adc ON adc.id=ado.adms_daman_category_id
            {$where}
            ORDER BY pedido_id DESC
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

    /**
     * Recuperar a quantidade total de pedidos para paginação.
     *
     * Este método retorna a quantidade total de pedidos na tabela `adms_daman_orders`, útil para a paginação.
     *
     * @return int Quantidade total de pedidos encontrados no banco de dados.
     */
    public function getAmountOrders(?string $orderNumber = null): int
    {
        $conditions = [];
        $params = [];

        // Verificar se o usuário é Admin, Super Admin ou Comprador
        $sqlCheckLevel = "SELECT COUNT(*) FROM adms_daman_users_access_levels 
            WHERE adms_daman_user_id = :check_user_id 
            AND adms_daman_access_level_id IN (1, 2, 5)";

        $stmtCheck = $this->getConnection()->prepare($sqlCheckLevel);
        $stmtCheck->bindValue(':check_user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtCheck->execute();
        $isPrivileged = $stmtCheck->fetchColumn() > 0;

        if (!$isPrivileged) {
            $conditions[] = "adms_daman_user_id = :logged_user_id";
            $params['logged_user_id'] = $_SESSION['user_id'];
        }

        if (!empty($orderNumber)) {
            $conditions[] = "id = :order_number";
            $params['order_number'] = $orderNumber;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT COUNT(id) AS amount_records
            FROM adms_daman_orders
            {$where}";

        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value, PDO::PARAM_INT);
        }

        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    /**
     * Recuperar um pedido específico com seus respectivos itens
     * 
     * @return array|bool Pedido recuperado do banco de dados
     */
    public function getOrder(int $orderId): array|bool
    {
        try {
            // Consultar pedido e itens de compra
            $order = 'SELECT ado.id, ado.adms_daman_supplier_id, ado.adms_daman_acquisition_types_id, ado.adms_daman_category_id, ado.adms_daman_user_id, 
                ado.adms_daman_project_id, ado.service, ado.expected_receipt_date, ado.observation, ado.adms_daman_acquisition_status_id, ado.status_date, ado.created_at, ado.updated_at, ado.rental_contract, ado.rental_period,

                adot.id AS oder_type_id, adot.name AS order_name_type,
                adc.name AS category_name,
                adu.name AS usr_name,
                adp.name AS project_name, adp.address AS project_adrress, adp.id AS order_project_id,
                ados.id AS order_status_id,
                ads.trade_name AS supplier_name,
                ados.name AS order_status

                FROM adms_daman_orders AS ado
                INNER JOIN adms_daman_acquisition_types AS adot ON adot.id=ado.adms_daman_acquisition_types_id
                INNER JOIN adms_daman_categories AS adc ON adc.id=ado.adms_daman_category_id
                INNER JOIN adms_daman_users AS adu ON adu.id=ado.adms_daman_user_id
                INNER JOIN adms_daman_projects AS adp ON adp.id=ado.adms_daman_project_id
                LEFT JOIN adms_daman_suppliers AS ads ON ads.id = ado.adms_daman_supplier_id
                INNER JOIN adms_daman_acquisition_status AS ados ON ados.id=ado.adms_daman_acquisition_status_id
                WHERE ado.id = :id';

            $stmt_order = $this->getConnection()->prepare($order);
            $stmt_order->bindValue(':id', $orderId, PDO::PARAM_INT);

            $stmt_order->execute();

            return $stmt_order->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            GenerateLog::generateLog("error", "Pedido não encontrado", ['id' => (int) $orderId, 'error' => $err->getMessage()]);
            die("Pedido não encontrado" . $err->getMessage());
        }
        return false;
    }

    /**
     * Método para buscar os itens do pedido
     */
    public function getItems(int $orderId): array|bool
    {

        // Consultar Itens de Compra
        $items = 'SELECT adoi.id AS item_id, adoi.description, adoi.adms_daman_measurement_units_id, adoi.quantity, adoi.purchased_quantity, adoi.unit_price, adoi.rented_quantity, adoi.returned_quantity, adoi.adms_daman_acquisition_status_id, ados.name AS item_status_name, admu.name AS measurement_units
                
            FROM adms_daman_order_items AS adoi
            INNER JOIN adms_daman_acquisition_status AS ados ON ados.id=adoi.adms_daman_acquisition_status_id
            INNER JOIN adms_daman_measurement_units AS admu ON admu.id=adoi.adms_daman_measurement_units_id
            WHERE adoi.adms_daman_order_id = :id';

        $stmt_items = $this->getConnection()->prepare($items);

        $stmt_items->bindValue(':id', $orderId, PDO::PARAM_INT);

        $stmt_items->execute();

        $itemsData = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        return $itemsData;
    }

    /** Método para criar pedido 
     * 
     */
    public function createOrder(array $data): bool|int
    {
        // Usar try e catch para gerenciar exceção/erro
        try { // Permanece no try se não houver nenhum erro


            if ($data['adms_daman_acquisition_types_id']) {
                // QUERY cadastrar pedido Compra
                $sql = 'INSERT INTO adms_daman_orders 
                    (adms_daman_acquisition_types_id, adms_daman_category_id, adms_daman_user_id, adms_daman_project_id, service, expected_receipt_date, observation, adms_daman_acquisition_status_id, created_at';

                // Incluir campo de periodo de locação caso seja do tipo locação
                if ($data['adms_daman_acquisition_types_id'] == 2) {
                    $sql .= ', rental_period';
                }

                $sql .= ') VALUES (:adms_daman_acquisition_types_id, :adms_daman_category_id, :adms_daman_user_id, :adms_daman_project_id, :service, :expected_receipt_date, 
                    :observation, :adms_daman_acquisition_status_id, :created_at';

                // Incluir campo de periodo de locação caso seja do tipo locação
                if ($data['adms_daman_acquisition_types_id'] == 2) {
                    $sql .= ', :rental_period';
                }
                $sql .= ')';

                // Preparar a QUERY
                $stmt = $this->getConnection()->prepare($sql);

                // Transforma a data d/m/Y para Y/m/d, formato aceito no MySql
                $FormatedDate = DateTime::createFromFormat('d/m/Y', $data['expected_receipt_date'])->format('Y-m-d');

                // Substituir os links da QUERY pelo valor
                $stmt->bindValue(':adms_daman_acquisition_types_id', $data['adms_daman_acquisition_types_id'], PDO::PARAM_INT);
                $stmt->bindValue(':adms_daman_category_id', $data['adms_daman_category_id'], PDO::PARAM_INT);
                $stmt->bindValue(':adms_daman_user_id', $data['adms_daman_user_id'], PDO::PARAM_INT);
                $stmt->bindValue(':adms_daman_project_id', $data['adms_daman_project_id'], PDO::PARAM_INT);
                $stmt->bindValue(':service', $data['service'], PDO::PARAM_STR);
                $stmt->bindValue(':expected_receipt_date', $FormatedDate);
                $stmt->bindValue(':observation', $data['observation'], PDO::PARAM_STR);
                $stmt->bindValue(':adms_daman_acquisition_status_id', 1, PDO::PARAM_INT);
                $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

                // Substituir link campo de periodo de locação caso seja do tipo locação
                if ($data['adms_daman_acquisition_types_id'] == 2) {
                    $stmt->bindValue(':rental_period', (int) $data['rental_period'], PDO::PARAM_INT);
                }
            }

            // Executar a QUERY
            $result = $stmt->execute();

            if ($result) {
                // 2. PEGA O ID AQUI (imediatamente após o insert do pedido)
                $orderId = $this->getConnection()->lastInsertId();

                // Chama o método para cadastar os itens
                $this->createItems($data, $orderId);
            }

            // retorna o ultimo id inserido de pedido
            return $orderId;
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pedido não cadastrado.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Método eclusivo para criação de itens.
     */
    private function createItems(array $data, int $orderId): array|bool
    {
        $newItemsMap = [];
        // Usar try e catch para gerenciar exceção/erro
        try { // Permanece no try se não houver nenhum erro

            $items = $data['items'] ?? [];

            foreach ($items as $item) {
                if (empty($item['item_id'])) { // Se não vier Id ele cadastra um novo item
                    $description = $item['description'] ?? null;
                    $quantity    = $item['quantity'] ?? null;
                    $adms_daman_measurement_units_id        = $item['adms_daman_measurement_units_id'] ?? null;

                    // salvar no banco

                    // QUERY cadastrar pedido
                    $sql = 'INSERT INTO adms_daman_order_items 
                    (adms_daman_order_id, description, adms_daman_measurement_units_id, quantity, adms_daman_acquisition_status_id, created_at) 
                    VALUES (:adms_daman_order_id, :description, :adms_daman_measurement_units_id, :quantity, :adms_daman_acquisition_status_id, :created_at)';

                    // Preparar a QUERY
                    $stmt = $this->getConnection()->prepare($sql);

                    $descriptionUpper = mb_convert_case($description, MB_CASE_TITLE, 'UTF-8');

                    // Substituir os links da QUERY pelo valor
                    $stmt->bindValue(':adms_daman_order_id', $orderId, PDO::PARAM_INT);
                    $stmt->bindValue(':description', $descriptionUpper, PDO::PARAM_STR);
                    $stmt->bindValue(':adms_daman_measurement_units_id', $adms_daman_measurement_units_id, PDO::PARAM_INT);
                    $stmt->bindValue(':quantity', $quantity);
                    $stmt->bindValue(':adms_daman_acquisition_status_id', 1, PDO::PARAM_INT);
                    $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

                    $stmt->execute();

                    // pegar temp_id
                    $tempId = $item['temp_id'] ?? null;

                    // pegar ID real
                    $newId = $this->getConnection()->lastInsertId();

                    // mapear
                    if ($tempId) {
                        $newItemsMap[$tempId] = $newId;
                    }
                }
            }
            return $newItemsMap;
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Itens não cadastrados.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Metodo para atualizar o pedido
     */
    public function updateOrder(array $data): array|bool
    {

        try {

            // QUERY para atualizar PEDIDO
            $sql = 'UPDATE adms_daman_orders SET adms_daman_acquisition_status_id = :adms_daman_acquisition_status_id, adms_daman_project_id = :adms_daman_project_id, adms_daman_category_id = :adms_daman_category_id, service = :service, observation = :observation, updated_at = :updated_at';

            // Incluir campo de periodo de locação e id do fornecedor caso seja do tipo locação
            if ($data['adms_daman_acquisition_types_id'] == 2) {
                $sql .= ', adms_daman_supplier_id = :adms_daman_supplier_id, rental_period = :rental_period, rental_contract = :rental_contract, adms_daman_acquisition_types_id = :adms_daman_acquisition_types_id';
            } else {
                $sql .= ', adms_daman_acquisition_types_id = :adms_daman_acquisition_types_id';
            }

            $sql .= ' WHERE id = :id';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':adms_daman_acquisition_status_id', $data['adms_daman_acquisition_status_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_project_id', $data['adms_daman_project_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_category_id', $data['adms_daman_category_id'], PDO::PARAM_INT);
            $stmt->bindValue(':service', $data['service'], PDO::PARAM_STR);
            $stmt->bindValue(':observation', $data['observation'], PDO::PARAM_STR);
            $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            // Substituir link campo de periodo de locação e id do fornecedor caso seja do tipo locação
            if ($data['adms_daman_acquisition_types_id'] == 2) {
                $stmt->bindValue(':adms_daman_supplier_id', $data['adms_daman_supplier_id'], PDO::PARAM_INT);
                $stmt->bindValue(':rental_period', $data['rental_period'], PDO::PARAM_INT);
                $stmt->bindValue(':rental_contract', $data['rental_contract']);
                $stmt->bindValue(':adms_daman_acquisition_types_id', $data['adms_daman_acquisition_types_id'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':adms_daman_acquisition_types_id', $data['adms_daman_acquisition_types_id'], PDO::PARAM_INT);
            }

            // Executar a QUERY
            $stmt->execute();

            $newItemsMap = $this->updateItems($data);

            return [
                'success' => true,
                'newItemsMap' => $newItemsMap
            ];

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pedido não editado.", ['id' => $data['id']]);
        } catch (Exception $e) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pedido não editado.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return [
                'fail' => false
            ];
        }
    }

    /**
     * Função específica para edição de Itens
     * 
     * Esta função recebe um array de dados que usa para identificar qual o tipo de ordem, para editar os campos específicos de cada ordem, ou seja se for do tipo locação vai editar na tabela itens o que é usado para locação.
     * @return bool
     */
    public function updateItems(array $data): array|bool
    {
        try {

            $items = $data['items'] ?? [];

            foreach ($items as $item) {

                // Se tem ID significa que precisa fazer o update UPDATE
                if (!empty($item['item_id'])) {

                    // QUERY para atualizar pedido
                    $sql = 'UPDATE adms_daman_order_items SET description = :description, quantity = :quantity, adms_daman_measurement_units_id = :adms_daman_measurement_units_id, unit_price = :unit_price, adms_daman_acquisition_status_id = :adms_daman_acquisition_status_id, updated_at = :updated_at';

                    // Incluir campo de periodo de locação caso seja do tipo Compra ou locação
                    if ($data['adms_daman_acquisition_types_id'] == 1) {
                        $sql .= ', purchased_quantity = :purchased_quantity';
                    } elseif ($data['adms_daman_acquisition_types_id'] == 2) {
                        $sql .= ', rented_quantity = :rented_quantity, returned_quantity = :returned_quantity, rental_start_date = :rental_start_date';
                    }

                    $sql .= ' WHERE id = :item_id';

                    // Preparar a QUERY
                    $stmt = $this->getConnection()->prepare($sql);

                    $descriptionUpper = mb_convert_case($item['description'], MB_CASE_TITLE, 'UTF-8');

                    // Substituir os links da QUERY pelo valor
                    $stmt->bindValue(':description', $descriptionUpper, PDO::PARAM_STR);
                    $stmt->bindValue(':adms_daman_measurement_units_id', $item['adms_daman_measurement_units_id'], PDO::PARAM_INT);
                    $stmt->bindValue(':quantity', (float)$item['quantity']);
                    $stmt->bindValue(':unit_price', (float)$item['unit_price']);
                    $stmt->bindValue(':adms_daman_acquisition_status_id', $item['adms_daman_acquisition_status_id'], PDO::PARAM_INT);
                    $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
                    $stmt->bindValue(':item_id', $item['item_id'], PDO::PARAM_INT);

                    // Substituir link campo de periodo de locação caso seja do tipo Compra ou Locação
                    if ($data['adms_daman_acquisition_types_id'] == 1) {
                        $purchased_quantity = ($item['purchased_quantity'] ?? NULL);
                        $stmt->bindValue(':purchased_quantity', (float) $purchased_quantity);
                    } elseif ($data['adms_daman_acquisition_types_id'] == 2) {
                        $stmt->bindValue(':rented_quantity', (float) $item['rented_quantity']);
                        $stmt->bindValue(':returned_quantity', (float) $item['returned_quantity']);
                        $stmt->bindValue(':rental_start_date', $item['rental_start_date'] ?? NULL);
                    }

                    // Executar a QUERY
                    $stmt->execute();
                }
            }

            $newItemsMap = $this->createItems($data, $data['id']);
            return $newItemsMap;
        } catch (Exception $e) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Itens não editados.", ['id_status' => (int) $data['adms_daman_acquisition_status_id'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Metodo para mudar status automático do pedido a gerar uma compra.
     */
    public function updateAutomaticOrderStatus(int $idOrder): bool
    {
        try {

            $sql = "UPDATE adms_daman_orders SET adms_daman_acquisition_status_id = :adms_daman_acquisition_status_id, status_date = :status_date
                    WHERE id = :id";

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':adms_daman_acquisition_status_id', 3, PDO::PARAM_INT);
            $stmt->bindValue(':status_date', date("Y-m-d H:i:s"));
            $stmt->bindValue(':id', $idOrder, PDO::PARAM_INT);

            // Executar a QUERY
            $stmt->execute();

            // Verificar o número de linhas afetadas
            if ($stmt->rowCount() > 0) {
                return true;
            } else {

                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Status de pedido não editado.", ['id_compra' => $idOrder]);

                return false;
            }
        } catch (Exception $e) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Status de pedido não editado.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Deletar um pedido pelo ID.
     *
     * Este método remove um pedido específico da tabela `adms_daman_orders' caso de erro, um log é gerado.
     *
     * @param int $id ID do pedido a ser deletado.
     * @return bool `true` se o pedido foi deletado com sucesso ou `false` em caso de erro.
     */
    public function deleteOrder(int $id): bool
    {
        // Usar o try e catch para gerenciar exceção/erro
        try {

            // Query para deletar o pedido
            $sql = 'DELETE FROM adms_daman_orders  WHERE id = :id LIMIT 1';

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
                GenerateLog::generateLog("error", "Pedido não apagado.", ['id' => $id]);
                return false;
            }
        } catch (Exception $e) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pedido não apagado.", ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Deletar um item do pedido pelo ID.
     *
     * Este método remove um item do pedido específico da tabela `adms_daman_orders' caso de erro, um log é gerado.
     *
     * @param int $id ID do item do pedido a ser deletado.
     * @return bool `true` se o pedido foi deletado com sucesso ou `false` em caso de erro.
     */
    public function deleteItem(int $id): bool
    {
        // Usar o try e catch para gerenciar exceção/erro
        try {

            // Query para deletar o Item
            $sql = 'DELETE FROM adms_daman_order_items  WHERE id = :id LIMIT 1';

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
                GenerateLog::generateLog("error", "Item não apagado.", ['id' => $id]);
                return false;
            }
        } catch (Exception $e) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Item não apagado.", ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Recuperar uma Unidade de medida específica
     * 
     * @return array|bool Unidade de medida recuperada do banco de dados
     */
    public function getAllMeasurementUnitsSelect(): array|bool
    {
        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name 
                FROM adms_daman_measurement_units
                ORDER BY name ASC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Método exlcusivo para buscar quais obras tem pedido em análise e rankear a obra com mais pedidos em análise para para a que tem menos pedidos nesse status.
     */
    public function rankProjectsStatusAnalisys(): array
    {
        $sql = "SELECT 
            adp.id,
            adp.name AS project_name,
            COUNT(ado.id) AS total
        FROM adms_daman_orders AS ado
        INNER JOIN adms_daman_projects AS adp ON adp.id = ado.adms_daman_project_id
        WHERE ado.adms_daman_acquisition_status_id = 1
        GROUP BY adp.id, adp.name
        ORDER BY total DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        $projectsRanked = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Aqui você pode usar a variável $projectsRanked para exibir ou processar os dados conforme necessário
        return $projectsRanked;
    }
}
