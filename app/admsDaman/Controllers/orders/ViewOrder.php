<?php

namespace App\admsDaman\Controllers\orders;

use App\admsDaman\Controllers\Services\OrderCommentService;
use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\OrderCommentsRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
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
    public function index(int|string $id)
    {

        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pedido não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Pedido não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-orders");

            return;
        }

        $viewOrder = new OrdersRepository();
        $this->data['order'] = $viewOrder->getOrder((int) $id);
        $this->data['items'] = $viewOrder->getItems((int) $id);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['order']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pedido não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Pedido não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-orders");

            return;
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

        $getComments = new OrderCommentsRepository();
        $this->data['comments'] = $getComments->getComment((int) $id);

        $sendComments = new OrderCommentService();
        $this->data['formatedComments'] = $sendComments->commentPresenter($this->data['comments']);

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Visualizar Pedido",
            'menu' => "list-orders",
            'buttonPermissions' => ["ListOrders", "UpdateOrder", "UpdateRentalOrder", "GeneratePurchasing", "DeleteOrder",],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Criar o título da página
        $this->data['title_head'] = "Visualizar Pedido";

        // Ativar o item de Menu
        $this->data['menu'] = "list-orders";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/orders/view", $this->data);
        $loadView->loadView();
    }
}
