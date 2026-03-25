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
    public function getOrder(int $id_pedido): array|bool
    {
        try {
            $sql = 'SELECT ado.adms_daman_order_types_id
            FROM adms_daman_orders AS ado
            INNER JOIN adms_daman_order_types AS adot ON adot.id=ado.adms_daman_order_types_id
            WHERE ado.id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substiruir os links pelos valores 
            $stmt->bindValue(':id', $id_pedido, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if($result['adms_daman_order_types_id'] == 1) {

                // Consultar pedido e itens de compra
                $order = 'SELECT ado.id, ado.adms_daman_order_types_id, ado.adms_daman_category_id, ado.adms_daman_user_id, ado.adms_daman_project_id, 
                        ado.service, ado.observation, ado.adms_daman_order_status_id, ado.status_date, ado.created_at, ado.updated_at,

                        adot.name AS order_name_type,
                        adc.name AS category_name,
                        adu.name AS usr_name,
                        adp.name AS project_name,
                        ados.name AS order_status

                        FROM adms_daman_orders AS ado
                        INNER JOIN adms_daman_order_types AS adot ON adot.id=ado.adms_daman_order_types_id
                        INNER JOIN adms_daman_categories AS adc ON adc.id=ado.adms_daman_category_id
                        INNER JOIN adms_daman_users AS adu ON adu.id=ado.adms_daman_user_id
                        INNER JOIN adms_daman_projects AS adp ON adp.id=ado.adms_daman_project_id
                        INNER JOIN adms_daman_order_status AS ados ON ados.id=ado.adms_daman_order_status_id
                        WHERE ado.id = :id';
                
                $stmt_order = $this->getConnection()->prepare($order);
                $stmt_order->bindValue(':id', $id_pedido, PDO::PARAM_INT);
                
                $items = 'SELECT adoi.description, adoi.unit, adoi.quantity, adoi.purchased_quantity, adoi.unit_price, adoi.adms_daman_order_status_id
                        
                        FROM adms_daman_order_items AS adoi
                        INNER JOIN adms_daman_order_status AS ados ON ados.id=adoi.adms_daman_order_status_id
                        WHERE adoi.adms_daman_order_id = :id';

                $stmt_items = $this->getConnection()->prepare($items);
                $stmt_items->bindValue(':id', $id_pedido, PDO::PARAM_INT);

                $stmt_order->execute();
                $stmt_items->execute();

                $orderData = $stmt_order->fetch(PDO::FETCH_ASSOC);
                $itemsData = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                $resultado = [
                    'order' => $orderData,
                    'items' => $itemsData
                ];

                return $resultado;
            }

        }catch(Exception $err) {
            GenerateLog::generateLog("error", "Pedido não encontrado", ['id' => (int) $id_pedido]);
            die("Pedido não encontrado " . $err->getMessage());
        }
        return false;
    }
}
?>