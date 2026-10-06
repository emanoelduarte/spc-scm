<?php

namespace App\admsDaman\Controllers\orders;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Controllers\Services\ProjectAccessService;

/**
 * Controller para exclusão de item de Pedidos
 *
 * Esta classe gerencia o processo de exclusão de item de pedidos no sistema. Ela lida com a validação dos dados
 * do formulário, a exclusão do pedido do banco de dados e o registro de logs para operações bem-sucedidas ou
 * falhas. Além disso, redireciona o usuário para a página do pedido com mensagens de sucesso ou erro.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * 
 * @package App\adms\Controllers\orders
 */
class DeleteItem
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes do pedido e processar a exclusão.
     *
     * Este método verifica a validade do token CSRF e a existência do ID do pedido. Se válido, recupera os
     * detalhes do pedido do banco de dados e tenta excluir o pedido. Redireciona o pedido para a página de 
     * listagem de pedidos com mensagens apropriadas baseadas no sucesso ou falha da operação.
     * 
     * @return void
     */
    public function index(): void
    {
        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?? [];

        $itemId = (int) ($this->data['form']['item_id'] ?? 0);
        $orderId = (int) ($this->data['form']['order_id'] ?? 0);

        // Validar CSRF e os IDs recebidos
        if (
            empty($this->data['form']['csrf_token']) ||
            !CSRFHelper::validateCSRFToken('form_delete_item', $this->data['form']['csrf_token']) ||
            !$itemId ||
            !$orderId
        ) {
            GenerateLog::generateLog("error", "Item não encontrado", [
                'item_id' => $itemId,
                'order_id' => $orderId
            ]);

            $_SESSION['error'] = "Item não encontrado!";
            header("Location: {$_ENV['URL_ADM']}list-orders");
            return;
        }

        // Recuperar o pedido ao qual o item deve pertencer
        $ordersRepository = new OrdersRepository();
        $order = $ordersRepository->getOrder($orderId);

        // Verificar se o pedido existe
        if (!$order) {
            GenerateLog::generateLog("error", "Pedido não encontrado ao apagar item", [
                'item_id' => $itemId,
                'order_id' => $orderId
            ]);

            $_SESSION['error'] = "Pedido não encontrado!";
            header("Location: {$_ENV['URL_ADM']}list-orders");
            return;
        }

        // Verificar se o usuário possui acesso à obra do pedido
        $projectAccessService = new ProjectAccessService();

        $userId = (int) $_SESSION['user_id'];
        $projectId = (int) $order['adms_daman_project_id'];

        if (!$projectAccessService->canAccessProject($userId, $projectId)) {
            GenerateLog::generateLog("error", "Tentativa de apagar item de pedido sem acesso", [
                'user_id' => $userId,
                'item_id' => $itemId,
                'order_id' => $orderId,
                'project_id' => $projectId
            ]);

            $_SESSION['error'] = "Você não possui acesso a este pedido!";
            header("Location: {$_ENV['URL_ADM']}list-orders");
            return;
        }

        // Recuperar os itens pertencentes ao pedido informado
        $items = $ordersRepository->getItems($orderId);

        // Verificar se o item realmente pertence ao pedido
        $itemIds = array_map('intval', array_column($items ?: [], 'item_id'));

        if (!in_array($itemId, $itemIds, true)) {
            GenerateLog::generateLog("error", "Tentativa de apagar item que não pertence ao pedido", [
                'user_id' => $userId,
                'item_id' => $itemId,
                'order_id' => $orderId
            ]);

            $_SESSION['error'] = "Item não pertence a este pedido!";
            header("Location: {$_ENV['URL_ADM']}view-order/{$orderId}");
            return;
        }

        // Apagar o item
        $result = $ordersRepository->deleteItem($itemId);

        if ($result) {
            $_SESSION['success'] = "Item apagado com sucesso!";
            header("Location: {$_ENV['URL_ADM']}view-order/{$orderId}");
            return;
        }

        $_SESSION['error'] = "Item não apagado!";
        header("Location: {$_ENV['URL_ADM']}view-order/{$orderId}");
    }
}
