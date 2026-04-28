<?php

namespace App\admsDaman\Controllers\Services;

use App\admsDaman\Models\Repository\OrdersRepository;

class OrderCommentService
{
    public function logBatch(array $data): array
    {
        $orderRepo = new OrdersRepository();

        $orderOld = $orderRepo->getOrder($data['id']);
        $orderOldItems = $orderRepo->getItems($data['id']);

        $userId = $_SESSION['user_id'] ?? null;

        $changes = [];

        // indexar itens antigos
        $oldItemsIndexed = [];
        foreach ($orderOldItems as $oi) {
            $oldItemsIndexed[$oi['item_id']] = $oi;
        }

        // STATUS
        if ($orderOld['order_status_id'] != $data['adms_daman_acquisition_status_id']) {

            $changes[] = [
                'order_id' => $data['id'],
                'user_id' => $userId,
                'type' => 'auto',
                'action' => 'update_status',
                'field' => 'status',
                'old_value' => $orderOld['order_status_id'],
                'new_value' => $data['adms_daman_acquisition_status_id'],
                'item_id' => null
            ];
        }

        // CAMPOS A VERIFICAR
        $fieldsToCheck = [
            'quantity' => 'quantity',
            'purchased_quantity' => 'purchased_quantity',
            'unit_price' => 'unit_price',
            'description' => 'description',
        ];

        // CONTROLE de itens novos (para detectar removidos depois)
        foreach ($data['items'] as $item) {

            // ITEM NOVO
            if (!empty($item['is_new']) && (int) $item['is_new'] === 1) {

                $changes[] = [
                    'order_id' => $data['id'],
                    'user_id' => $userId,
                    'type' => 'auto',
                    'action' => 'add_item',
                    'field' => null,
                    'old_value' => null,
                    'new_value' => json_encode([
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'] ?? null,
                        'unit_price' => $item['unit_price'] ?? null,
                    ]),
                    'item_id' => null
                ];

                continue;
            }

            // marca como existente
            if (!empty($item['item_id'])) {
                $newItemsIds[] = (int) $item['item_id'];
            }

            $oldItem = $oldItemsIndexed[$item['item_id']] ?? null;

            if (!$oldItem) continue;

            // 🔥 UPDATE
            foreach ($fieldsToCheck as $field => $label) {

                $oldValue = $oldItem[$field] ?? null;
                $newValue = $item[$field] ?? null;

                if ((string)$oldValue !== (string)$newValue) {

                    $changes[] = [
                        'order_id' => $data['id'],
                        'user_id' => $userId,
                        'type' => 'auto',
                        'action' => 'update_item',
                        'field' => $label,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'item_id' => $item['item_id']
                    ];
                }
            }
        }

        // // ITENS REMOVIDOS
        // $newItemsIds = [];

        // foreach ($orderOldItems as $oldItem) {

        //     if (!in_array((int)$oldItem['item_id'], $newItemsIds, true)) {

        //         $changes[] = [
        //             'order_id' => $data['id'],
        //             'user_id' => $userId,
        //             'type' => 'auto',
        //             'action' => 'delete_item',
        //             'field' => null,
        //             'old_value' => json_encode($oldItem),
        //             'new_value' => null,
        //             'item_id' => $oldItem['item_id']
        //         ];
        //     }
        // }

        return $changes;
    }
}
