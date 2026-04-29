<?php

namespace App\admsDaman\Controllers\Services;

use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\StatusRepository;

class OrderCommentService
{
    /** @var array|null $data recebe os dados enviados para a classe */
    private array|null $data = null;

    public function logBatch(array $data): array
    {
        $this->data = $data;

        $orderRepo = new OrdersRepository();

        $orderOld = $orderRepo->getOrder($this->data['id']);
        $orderOldItems = $orderRepo->getItems($this->data['id']);

        $userId = $_SESSION['user_id'] ?? null;

        $changes = [];

        // indexar itens antigos
        $oldItemsIndexed = [];
        foreach ($orderOldItems as $oi) {
            $oldItemsIndexed[$oi['item_id']] = $oi;
        }

        // STATUS
        if ($orderOld['order_status_id'] != $this->data['adms_daman_acquisition_status_id']) {

            $statusRepo = new StatusRepository();

            $oldStatus = $statusRepo->getStatus($orderOld['order_status_id']);
            $newStatus = $statusRepo->getStatus($data['adms_daman_acquisition_status_id']);

            $changes[] = [
                'order_id' => $this->data['id'],
                'user_id' => $userId,
                'type' => 'auto',
                'action' => 'update_status',
                'field' => 'status',
                'old_value' => $oldStatus['name'],
                'new_value' =>  $newStatus['name'],
                'comment' => null,
                'item_id' => null
            ];
        }

        // OBRAS
        if ($orderOld['order_project_id'] != $this->data['adms_daman_project_id']) {

            $projectsRepo = new ProjectsRepository();

            $oldProject = $projectsRepo->getProject($orderOld['order_project_id']);
            $newProject = $projectsRepo->getProject($data['adms_daman_project_id']);

            $changes[] = [
                'order_id' => $data['id'],
                'user_id' => $userId,
                'type' => 'auto',
                'action' => 'update_project',
                'field' => 'project',
                'old_value' => $oldProject['name'] ?? null,
                'new_value' => $newProject['name'] ?? null,
                'comment' => null,
                'item_id' => null
            ];
        }

        // DESCRIÇÃO DO SERVIÇO
        if ($orderOld['service'] != $this->data['service']) {

            $changes[] = [
                'order_id' => $this->data['id'],
                'user_id' => $userId,
                'type' => 'auto',
                'action' => 'update_service',
                'field' => 'service',
                'old_value' => $orderOld['service'],
                'new_value' => $this->data['service'],
                'comment' => null,
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
        foreach ($this->data['items'] as $item) {

            // ITEM NOVO
            if (!empty($item['is_new']) && (int) $item['is_new'] === 1) {

                $changes[] = [
                    'order_id' => $this->data['id'],
                    'user_id' => $userId,
                    'type' => 'auto',
                    'action' => 'add_item',
                    'field' => null,
                    'old_value' => null,
                    'new_value' => json_encode([
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'] ?? null,
                        'purchased_quantity' => $item['purchased_quantity'] ?? null,
                        'unit_price' => $item['unit_price'] ?? null,
                    ]),
                    'comment' => null,
                    'item_id' => null,

                    'temp_id' => $item['temp_id'] ?? null
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
                        'order_id' => $this->data['id'],
                        'user_id' => $userId,
                        'type' => 'auto',
                        'action' => 'update_item',
                        'field' => $label,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'comment' => null,
                        'item_id' => $item['item_id']
                    ];
                }
            }
        }

        return $changes;
    }

    public function logBatchUserComment(array $data): array
    {
        $this->data = $data;

        $userId = $_SESSION['user_id'] ?? null;

        $changes = [];

        // USER COMMENTS
        if (isset($this->data['new_user_comment'])) {

            if (empty($this->data['new_user_comment'])) {

                $_SESSION['error'] = 'O comentário não pode ser vazio.';
                return [];
            }

            $changes[] = [
                'order_id' => $this->data['id'],
                'user_id' => $userId,
                'type' => 'manual',
                'action' => 'comment_user',
                'field' => 'user',
                'old_value' => null,
                'new_value' => null,
                'comment' =>  $this->data['new_user_comment'],
                'item_id' => null
            ];
        }

        return $changes;
    }

    public function commentPresenter(array $arrayComments): array
    {

        $result = [];

        foreach ($arrayComments as $comment) {

            $item = [
                'title' => '',
                'message' => '',
                'icon' => 'bi-chat',
                'color' => 'secondary',
                'created_at' => $comment['created_at'],
                'user' => $comment['user_name'] ?? 'Sistema'
            ];

            switch ($comment['action']) {

                case 'comment_user':
                    $item['title'] = 'Comentário';
                    $item['message'] = $this->formatCommentUser($comment);
                    $item['icon'] = 'bi-plus-circle';
                    $item['color'] = 'success';
                    break;

                case 'update_item':
                    $item['title'] = 'Item atualizado';
                    $item['message'] = $this->formatUpdateItem($comment);
                    $item['icon'] = 'bi-pencil';
                    $item['color'] = 'warning';
                    break;

                case 'update_status':
                    $item['title'] = 'Status do pedido atualizado';
                    $item['message'] = $this->formatUpdateStatus($comment);
                    $item['icon'] = 'bi-arrow-repeat';
                    $item['color'] = 'primary';
                    break;

                case 'update_service':
                    $item['title'] = 'Descrição atualizada';
                    $item['message'] = $this->formatUpdateService($comment);
                    $item['icon'] = 'bi-file-text';
                    $item['color'] = 'info';
                    break;

                case 'update_project':
                    $item['title'] = 'Obra atualizada';
                    $item['message'] = $this->formatUpdateProject($comment);
                    $item['icon'] = 'bi-building';
                    $item['color'] = 'primary';
                    break;

                case 'add_item':
                    $item['title'] = 'Novo item adicionado';
                    $item['message'] = $this->formatAddItem($comment);
                    $item['icon'] = 'bi-plus-circle';
                    $item['color'] = 'success';
                    break;
            }

            $result[] = $item;
        }

        return $result;
    }

    private function formatUpdateItem(array $c): string
    {
        $fieldNames = [
            'quantity' => 'quantidade',
            'purchased_quantity' => 'quantidade comprada',
            'unit_price' => 'valor unitário',
            'description' => 'descrição',
        ];

        $field = $fieldNames[$c['field']] ?? $c['field'];

        if ($field == 'valor unitário') {
            $oldValue = number_format((float)$c['old_value'] ?? 0, 2, ',', '.');
            $newValue = number_format((float)$c['new_value'] ?? 0, 2, ',', '.');

            return "{$c['user_name']} alterou {$field} de  {$oldValue} para {$newValue}";
        } else {
            return "{$c['user_name']} alterou {$field} de {$c['old_value']} para {$c['new_value']}";
        }
    }

    private function formatUpdateStatus(array $c): string
    {
        return "{$c['user_name']} alterou o status de {$c['old_value']} para {$c['new_value']}";
    }

    private function formatUpdateService(array $c): string
    {
        return "{$c['user_name']} alterou a descrição do serviço de {$c['old_value']} para {$c['new_value']}";
    }

    private function formatUpdateProject(array $c): string
    {
        return "{$c['user_name']} alterou a obra de destino do pedido de {$c['old_value']} para {$c['new_value']}";
    }

    private function formatCommentUser(array $c): string
    {
        return "{$c['comment']}";
    }

    private function formatAddItem(array $c): string
    {
        $data = json_decode($c['new_value'], true);

        $desc = $data['description'] ?? '—';
        $qty  = $data['quantity'] ?? '—';
        $price = $data['unit_price'] ?? '—';

        return "{$c['user_name']} adicionou um novo item: {$desc} (Qtd: {$qty})";
    }

    private function formatDeleteItem(array $c): string
    {
        $data = json_decode($c['old_value'], true);

        $desc = $data['description'] ?? 'Item removido';

        return "{$c['user_name']} removeu o item: {$desc}";
    }
}
