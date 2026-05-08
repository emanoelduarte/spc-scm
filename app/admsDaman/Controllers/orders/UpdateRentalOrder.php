<?php

namespace App\admsDaman\Controllers\orders;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationOrderItemnsService;
use App\admsDaman\Controllers\Services\Validation\ValidationOrderService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\CategoriesRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\StatusRepository;
use App\admsDaman\Views\Services\LoadViewService;

class UpdateRentalOrder
{
    /** @var array|string|null $data Dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Editar o pedido Locação.
     *
     * Este método gerencia o processo de edição de um pedido de locação. Recebe os dados do formulário, valida o CSRF token e
     * a existência do pedido, e chama o método adequado para editar o pedido ou carregar a visualização de edição.
     *
     * @param int|string $id ID da pedido a ser editada.
     * 
     * @return void
     */
    public function index(int|string $id): void
    {
        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Validar o CSRF token e a existência do ID do pedido
        if (
            isset($this->data['form']['csrf_token']) &&
            CSRFHelper::validateCSRFToken('form_update_order', $this->data['form']['csrf_token'])
        ) {
            // Editar a pedido
            $this->editOrder();
            // var_dump($this->data['form']);
        } else {
            // Recuperar o registro da pedido
            $viewOrder = new OrdersRepository();
            $this->data['form'] = $viewOrder->getOrder((int) $id);
            $this->data['items'] = $viewOrder->getItems((int) $id);

            // Verificar se a pedido foi encontrada
            if (!$this->data['form']) {
                // Registrar o erro e redirecionar
                GenerateLog::generateLog("error", "Pedido não encontrado", ['id' => (int) $id]);
                $_SESSION['error'] = "Pedido não encontrado!";
                header("Location: {$_ENV['URL_ADM']}list-orders");
                return;
            }


            // exit;
            // Carregar a visualização para edição da Pedido
            $this->viewUpdateOrder();
        }
    }

    /**
     * Carregar a visualização para edição do pedido.
     *
     * Este método define o título do pedido e carrega a visualização de edição do pedido com os dados necessários.
     * 
     * @return void
     */
    private function viewUpdateOrder(): void
    {
        // Instanciar o repositório para preencher os selects.
        $getProjectSelect = new ProjectsRepository();
        $this->data['getAllProjectsSelect'] = $getProjectSelect->getAllProjectsSelect();

        // Instanciar o repositório para preencher os selects.
        $getAllStatusSelect = new StatusRepository();
        $this->data['getAllStatusSelect'] = $getAllStatusSelect->getAllStatusSelect();

        // Instanciar o repositório para preencher os selects.
        $getProjectSelect = new CategoriesRepository();
        $this->data['getAllCategoriesSelect'] = $getProjectSelect->getAllCategoriesSelect();

        // Instanciar o repositório para preencher os selects.
        $getMeasurementUnits = new OrdersRepository();
        $this->data['getAllMeasurementUnitsSelect'] = $getMeasurementUnits->getAllMeasurementUnitsSelect();

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Editar Pedido",
            'menu' => "list-orders",
            'buttonPermissions' => ["ListOrders", "ViewOrder"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/orders/updateRental", $this->data);
        $loadView->loadView();
    }

    /**
     * Editar pedido
     * 
     * Este método realiza a edição de um pedido existente no sistema. Ele valida os dados do formulário usando a
     * Classe `ValidationOrderService e ValidationOrderItemnsService`, exibe a view com os erros caso existam campos compdados incorretos,
     * Chama o repositório para atualizar o pedido e, depedendo do resultado, redireciona o pedido ou exibe
     * uma mensagem de erro
     * 
     * @return void
     */
    private function editOrder(): void
    {
        // Manter os itens do POST se houver erro de preenchimento
        $this->data['items'] = $this->data['form']['items'] ?? [];

        // Instanciar a classe validar os dados do formulário com Rakit
        $validationOrder = new ValidationOrderService();
        $this->data['errors'] = $validationOrder->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados incorretos
        if (!empty($this->data['errors'])) {

            // Chama o método carregar a view
            $this->viewUpdateOrder();

            return;
        }

        // Instaciar a classe que valida os dados do formulário de itens do pedido com Rakit
        $validationItemns = new ValidationOrderItemnsService();
        $this->data['errors'] = $validationItemns->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewUpdateOrder();

            return;
        }

        // Instanciar o OrdersRepository para chamar o método que faz a edição do pedido
        $orderUpdate = new OrdersRepository();
        $result = $orderUpdate->updateOrder($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar
            $_SESSION['success'] = "Pedido editado com sucesso!";

            // Redirecionar o usuário para a página de visualizar Pedido
            header("Location: {$_ENV['URL_ADM']}view-order/{$this->data['form']['id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar
            $this->data['errors'][] = "Pedido não editado!";

            // Chamar o método carregar a view
            $this->viewUpdateOrder();
        }
    }
}
