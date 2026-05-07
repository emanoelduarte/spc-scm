<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class OrderCommentsRepository extends DbConnection
{
    public function getComment(int $orderId): string|array
    {
        $sql = "SELECT adoc.id, adoc.adms_daman_order_id, adoc.adms_daman_user_id, adoc.type, adoc.action, adoc.field, adoc.old_value, adoc.new_value, adoc.adms_daman_order_item_id, adoc.comment, adoc.created_at,
        
        adu.name AS user_name
        FROM adms_daman_order_comments AS adoc
        INNER JOIN adms_daman_users AS adu ON adu.id=adoc.adms_daman_user_id 
        WHERE adoc.adms_daman_order_id = :adms_daman_order_id
        ORDER BY id DESC";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->bindValue(':adms_daman_order_id', $orderId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adicionar comentários ao pedido
     */
    public function insertMultipleComments(array $changes, $itemNewId = null): void
    {
        if (empty($changes)) return;

        $sql = "INSERT INTO adms_daman_order_comments
        (adms_daman_order_id, adms_daman_user_id, type, action, field, old_value, new_value, adms_daman_order_item_id, comment, created_at)
        VALUES ";

        $values = [];
        $params = [];

        foreach ($changes as $index => $change) {

            // 🔥 se for item novo, usa o ID gerado
            $itemId = $change['item_id'];

            if ($change['action'] === 'add_item' && $itemNewId) {
                $itemId = $itemNewId;
            }

            $values[] = "(
            :order_id_$index,
            :user_id_$index,
            :type_$index,
            :action_$index,
            :field_$index,
            :old_value_$index,
            :new_value_$index,
            :item_id_$index,
            :comment_$index,
            :created_at
        )";

            $params[":order_id_$index"] = $change['order_id'];
            $params[":user_id_$index"] = $change['user_id'];
            $params[":type_$index"] = $change['type'];
            $params[":action_$index"] = $change['action'];
            $params[":field_$index"] = $change['field'];
            $params[":old_value_$index"] = $change['old_value'];
            $params[":new_value_$index"] = $change['new_value'];
            $params[":item_id_$index"] = $change['item_id'];
            $params[":comment_$index"] = $change['comment'];
            $params[":created_at"] = date("Y-m-d H:i:s");
        }

        $sql .= implode(',', $values);

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Metodo para mudar status automático do pedido a gerar uma compra.
     */
    public function createAutomaticOrderPurchased(int $idOrder): bool
    {
        try {
            $sql = "INSERT INTO adms_daman_order_comments  (adms_daman_order_id, adms_daman_user_id, type, action, created_at)
        VALUES (:adms_daman_order_id, :adms_daman_user_id, :type, :action, :created_at)";

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':adms_daman_order_id', $idOrder, PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':type', 'auto', PDO::PARAM_STR);
            $stmt->bindValue(':action', 'purchased_in', PDO::PARAM_STR);
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

            // Executar a QUERY
            $stmt->execute();

            // Verificar o número de linhas afetadas
            if ($stmt->rowCount() > 0) {
                return true;
            } else {

                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Comentário não inserido.", ['id_pedido' => $idOrder]);

                return false;
            }
        } catch (Exception $e) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Comentário não inserido.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return false;
        }
    }
}
