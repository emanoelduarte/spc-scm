<?php

namespace App\admsDaman\Controllers\orders;

use App\admsDaman\Controllers\Services\OrderCommentService;
use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\OrderCommentsRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Controllers\Services\ProjectAccessService;
use App\admsDaman\Views\Services\LoadViewService;

class ViewOrder
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes do pedido.
     *
     * Este método gerencia a recuperação e exibição dos detalhes de um pedido específico. Ele valida o ID fornecido,
     * recupera os dados do pedido do repositório e carrega a visualização. Se o pedido não for encontrado, registra
     * um erro, exibe uma mensagem e redireciona para a página de lista de pedidos.
     *
     * @param int|string $id ID do pedido a ser visualizado.
     * 
     * @return void
     */
    public function index(int|string $id): void
{
    $orderId = (int) $id;

    // Receber os dados do formulário
    $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

    // Verificar se o ID do pedido é válido
    if (!$orderId) {
        GenerateLog::generateLog("error", "Pedido não encontrado", ['id' => $orderId]);
        $_SESSION['error'] = "Pedido não encontrado!";
        header("Location: {$_ENV['URL_ADM']}list-orders");
        return;
    }

    // Recuperar o pedido
    $viewOrder = new OrdersRepository();
    $this->data['order'] = $viewOrder->getOrder($orderId);

    // Verificar se encontrou o pedido
    if (!$this->data['order']) {
        GenerateLog::generateLog("error", "Pedido não encontrado", ['id' => $orderId]);
        $_SESSION['error'] = "Pedido não encontrado!";
        header("Location: {$_ENV['URL_ADM']}list-orders");
        return;
    }

    // Verificar se o usuário possui acesso à obra do pedido
    $projectAccessService = new ProjectAccessService();
    $userId = (int) $_SESSION['user_id'];
    $projectId = (int) $this->data['order']['adms_daman_project_id'];

    if (!$projectAccessService->canAccessProject($userId, $projectId)) {
        GenerateLog::generateLog("error", "Acesso negado ao pedido", [
            'user_id' => $userId,
            'order_id' => $orderId,
            'project_id' => $projectId
        ]);

        $_SESSION['error'] = "Você não possui acesso a este pedido!";
        header("Location: {$_ENV['URL_ADM']}list-orders");
        return;
    }

    // Recuperar os itens somente após validar o acesso
    $this->data['items'] = $viewOrder->getItems($orderId);

    // Garantir que eventual comentário seja associado ao pedido da rota
    if (is_array($this->data['form'])) {
        $this->data['form']['id'] = $orderId;
    }

    // Chamar serviço de comentários adicionar comentário
    $commentService = new OrderCommentService();
    $arrayAddUserComment = $this->data['form'] ?? [];
    $changesArray = $commentService->logBatchUserComment($arrayAddUserComment);

    // Verificar se o retorno teve dados ou foi array vazio
    if (!empty($changesArray)) {
        $orderComments = new OrderCommentsRepository();
        $orderComments->insertMultipleComments($changesArray);
        $this->data['form']['new_user_comment'] = '';
    }

    // Recuperar os comentários
    $getComments = new OrderCommentsRepository();
    $this->data['comments'] = $getComments->getComment($orderId);

    $sendComments = new OrderCommentService();
    $this->data['formatedComments'] = $sendComments->commentPresenter($this->data['comments']);

    // Configurar os elementos da página
    $pageElements = [
        'title_head' => "Visualizar Pedido",
        'menu' => "list-orders",
        'buttonPermissions' => [
            "ListOrders",
            "UpdateOrder",
            "UpdateRentalOrder",
            "DeleteItem",
            "GeneratePurchasing",
            "DeleteOrder",
        ],
    ];

    $pageLayoutService = new PageLayoutService();
    $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

    // Carregar a VIEW
    $loadView = new LoadViewService("admsDaman/Views/orders/view", $this->data);
    $loadView->loadView();
}
}
