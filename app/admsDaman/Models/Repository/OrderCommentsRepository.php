<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;

class OrderCommentsRepository extends DbConnection
{
    public function insertMultipleComments(array $changes, $itemNewId = null): void
    {
        if (empty($changes)) return;

        $sql = "INSERT INTO adms_daman_order_comments
        (adms_daman_order_id, adms_daman_user_id, type, action, field, old_value, new_value, adms_daman_order_item_id, created_at)
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
            $params[":created_at"] = date("Y-m-d H:i:s");
        }

        $sql .= implode(',', $values);

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
    }
}
