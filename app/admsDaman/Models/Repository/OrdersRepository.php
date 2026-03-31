<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
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
    public function getAllOrders(int $page = 1, int $limitResult = 10)
    {

        // Calcular o registro inicial de cada página exemplo:
        // 2(caso pagina 2) - 1 = 1 * $limite por página = 10
        $offset = max(0, ($page - 1) * $limitResult);

        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT  ado.id AS pedido_id, ado.adms_daman_order_types_id, ado.adms_daman_project_id, ado.adms_daman_order_status_id, ado.created_at,
                adp.name AS project_name, ados.name AS status_name
                FROM adms_daman_orders AS ado
                INNER JOIN adms_daman_projects AS adp ON adp.id=ado.adms_daman_project_id
                INNER JOIN adms_daman_order_status AS ados ON ados.id=adms_daman_order_status_id
                ORDER BY pedido_id DESC
                LIMIT :limit OFFSET :offset';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir o link da QUERY pelo valor
        $stmt->bindValue(':limit', $limitResult, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar a quantidade total de pedidos para paginação.
     *
     * Este método retorna a quantidade total de pedidos na tabela `adms_daman_orders`, útil para a paginação.
     *
     * @return int Quantidade total de pedidos encontrados no banco de dados.
     */
    public function getAmountOrders(): int|bool
    {
        // Criar Query para recuperar todos os registros no banco de dados
        $sql = 'SELECT COUNT(id) AS amount_records
        FROM adms_daman_orders';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a query
        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    /**
     * Recuperar um pedido específico com seus respectivos itens
     * 
     * @return array|bool Pedido recuperado do banco de dados
     */
    public function getOrder(int $idPedido): array|bool
    {
        try {
            // Consultar pedido e itens de compra
            $order = 'SELECT ado.id, ado.adms_daman_order_types_id, ado.adms_daman_category_id, ado.adms_daman_user_id, 
                ado.adms_daman_project_id, 
                ado.service, ado.expected_receipt_date, ado.observation, ado.adms_daman_order_status_id, ado.status_date, ado.created_at, ado.updated_at, ado.rental_contract, ado.rental_period,

                adot.name AS order_name_type,
                adc.name AS category_name,
                adu.name AS usr_name,
                adp.name AS project_name, adp.address AS project_adrress,
                ados.name AS order_status

                FROM adms_daman_orders AS ado
                INNER JOIN adms_daman_order_types AS adot ON adot.id=ado.adms_daman_order_types_id
                INNER JOIN adms_daman_categories AS adc ON adc.id=ado.adms_daman_category_id
                INNER JOIN adms_daman_users AS adu ON adu.id=ado.adms_daman_user_id
                INNER JOIN adms_daman_projects AS adp ON adp.id=ado.adms_daman_project_id
                INNER JOIN adms_daman_order_status AS ados ON ados.id=ado.adms_daman_order_status_id
                WHERE ado.id = :id';

            $stmt_order = $this->getConnection()->prepare($order);
            $stmt_order->bindValue(':id', $idPedido, PDO::PARAM_INT);

            $stmt_order->execute();

            return $stmt_order->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            GenerateLog::generateLog("error", "Pedido não encontrado", ['id' => (int) $idPedido]);
            die("Pedido não encontrado " . $err->getMessage());
        }
        return false;
    }

    /** Método para criar pedido 
     * 
     */
    public function createOrder(array $data): bool|int
    {
        // Usar try e catch para gerenciar exceção/erro
        try { // Permanece no try se não houver nenhum erro


            if ($data['adms_daman_order_types_id'] == 1) {
                // QUERY cadastrar pedido Compra
                $sql = 'INSERT INTO adms_daman_orders 
                    (adms_daman_order_types_id, adms_daman_category_id, adms_daman_user_id, adms_daman_project_id, service, expected_receipt_date, observation, adms_daman_order_status_id, created_at) 

                    VALUES (:adms_daman_order_types_id, :adms_daman_category_id, :adms_daman_user_id, :adms_daman_project_id, :service, :expected_receipt_date, 
                    :observation, :adms_daman_order_status_id, :created_at)';

                // Preparar a QUERY
                $stmt = $this->getConnection()->prepare($sql);

                // Substituir os links da QUERY pelo valor
                $stmt->bindValue(':adms_daman_order_types_id', $data['adms_daman_order_types_id'], PDO::PARAM_INT);
                $stmt->bindValue(':adms_daman_category_id', $data['adms_daman_category_id'], PDO::PARAM_INT);
                $stmt->bindValue(':adms_daman_user_id', $data['adms_daman_user_id'], PDO::PARAM_INT);
                $stmt->bindValue(':adms_daman_project_id', $data['adms_daman_project_id'], PDO::PARAM_INT);
                $stmt->bindValue(':service', $data['service'], PDO::PARAM_STR);
                $stmt->bindValue(':expected_receipt_date', $data['expected_receipt_date']);
                $stmt->bindValue(':observation', $data['observation'], PDO::PARAM_STR);
                $stmt->bindValue(':adms_daman_order_status_id', 1, PDO::PARAM_INT);
                $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));
            } else {
                // QUERY cadastrar pedido Locação
                $sql = 'INSERT INTO adms_daman_orders 
                    (adms_daman_order_types_id, adms_daman_category_id, adms_daman_user_id, adms_daman_project_id, service, expected_receipt_date, observation, adms_daman_order_status_id, created_at, rental_period) 

                    VALUES (:adms_daman_order_types_id, :adms_daman_category_id, :adms_daman_user_id, :adms_daman_project_id, :service, :expected_receipt_date, 
                    :observation, :adms_daman_order_status_id, :created_at, :rental_period)';

                // Preparar a QUERY
                $stmt = $this->getConnection()->prepare($sql);

                // Substituir os links da QUERY pelo valor
                $stmt->bindValue(':adms_daman_order_types_id', $data['adms_daman_order_types_id'], PDO::PARAM_INT);
                $stmt->bindValue(':adms_daman_category_id', $data['adms_daman_category_id'], PDO::PARAM_INT);
                $stmt->bindValue(':adms_daman_user_id', $data['adms_daman_user_id'], PDO::PARAM_INT);
                $stmt->bindValue(':adms_daman_project_id', $data['adms_daman_project_id'], PDO::PARAM_INT);
                $stmt->bindValue(':service', $data['service'], PDO::PARAM_STR);
                $stmt->bindValue(':expected_receipt_date', $data['expected_receipt_date']);
                $stmt->bindValue(':observation', $data['observation'], PDO::PARAM_STR);
                $stmt->bindValue(':adms_daman_order_status_id', 1, PDO::PARAM_INT);
                $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));
                $stmt->bindValue(':rental_period', $data['rental_period'], PDO::PARAM_INT);
            }

            // Executar a QUERY
            $result = $stmt->execute();

            // Retornar o ID do pedido recém cadastrado

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

    public function createItems(array $data, int $orderId): bool|int
    {
        // Usar try e catch para gerenciar exceção/erro
        try { // Permanece no try se não houver nenhum erro

            $descriptions = $data['description'];
            $quantities   = $data['quantity'];
            $units        = $data['unit'];

            foreach ($descriptions as $index => $desc) {
                $quantity = $quantities[$index];
                $unit     = $units[$index];
                // salvar no banco

                // QUERY cadastrar pedido Compra
                $sql = 'INSERT INTO adms_daman_order_items 
                    (adms_daman_order_id, description, unit, quantity, adms_daman_order_status_id, created_at) 
                    VALUES (:adms_daman_order_id, :description, :unit, :quantity, :adms_daman_order_status_id, :created_at)';

                // Preparar a QUERY
                $stmt = $this->getConnection()->prepare($sql);

                // Substituir os links da QUERY pelo valor
                $stmt->bindValue(':adms_daman_order_id', $orderId, PDO::PARAM_INT);
                $stmt->bindValue(':description', $desc, PDO::PARAM_STR);
                $stmt->bindValue(':unit', $unit, PDO::PARAM_STR);
                $stmt->bindValue(':quantity', $quantity);
                $stmt->bindValue(':adms_daman_order_status_id', 1, PDO::PARAM_INT);
                $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

                $stmt->execute();
            }
            // Executar a QUERY
            return true;
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Itens não cadastrados.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function getItems(int $idPedido): array|bool
    {

        // Consultar Itens de Compra
        $items = 'SELECT adoi.description, adoi.unit, adoi.quantity, adoi.purchased_quantity, adoi.unit_price, adoi.rented_quantity, adoi.returned_quantity, adoi.adms_daman_order_status_id
                
            FROM adms_daman_order_items AS adoi
            INNER JOIN adms_daman_order_status AS ados ON ados.id=adoi.adms_daman_order_status_id
            WHERE adoi.adms_daman_order_id = :id';

        $stmt_items = $this->getConnection()->prepare($items);

        $stmt_items->bindValue(':id', $idPedido, PDO::PARAM_INT);

        $stmt_items->execute();

        $itemsData = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        return $itemsData;
    }
}
