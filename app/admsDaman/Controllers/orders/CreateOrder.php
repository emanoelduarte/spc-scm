<?php

namespace App\admsDaman\Controllers\orders;

use App\admsDaman\Controllers\Services\Validation\ValidationOrderItemnsService;
use App\admsDaman\Controllers\Services\Validation\ValidationOrderService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\CategoriesRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para criação de pedido
 *
 * Esta classe é responsável pelo processo de criação de novos pedidos. Ela lida com a recepção dos dados do
 * formulário, validação dos mesmos, e criação do pedido no sistema. Além disso, é responsável por carregar
 * a visualização apropriada com mensagens de sucesso ou erro.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\orders
 */
class CreateOrder
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Método principal que gerencia a criação do pedido.
     *
     * Este método é chamado para processar a criação de um novo pedido. Ele verifica a validade do token CSRF,
     * valida os dados do formulário e, se tudo estiver correto, cria o pedido. Caso contrário, carrega a
     * visualização de criação de pedido com mensagens de erro.
     * 
     * @return void
     */
    public function index()
    {
        // Receber os dados do formulário de cadastro de pedido
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_create_order', $this->data['form']['csrf_token'])) {

            // Chamar método cadastrar passando pelas validações necessárias
            $this->addOrder();
            // var_dump($this->data['form']);
            // exit;

        } else {
            // Chamar o método carregar a view
            $this->viewOrder();
        }
    }

    /**
     * Carregar a visualização de criação de Pedido.
     * 
     * Este método configura os dados necessários e carrega a view para a criação de um novo pedido.
     * 
     * @return void
     */
    private function viewOrder(): void
    {
        // Instanciar o repositório para preencher os selects.
        $getProjectSelect = new ProjectsRepository();
        $this->data['getAllProjectsSelect'] = $getProjectSelect->getAllProjectsSelect();

        // Instanciar o repositório para preencher os selects.
        $getProjectSelect = new CategoriesRepository();
        $this->data['getAllCategoriesSelect'] = $getProjectSelect->getAllCategoriesSelect();

        // Criar o título da página
        $this->data['title_head'] = "Cadastrar Pedido";

        // Ativar o item de Menu
        $this->data['menu'] = "list-orders";

        // Garantir que exista pelo menos uma linha no array, para evitar erro em que o php esconda os campos iniciais
        if (empty($this->data['form']['description'])) {
            $this->data['form']['description'] = [''];
            $this->data['form']['quantity']    = [''];
            $this->data['form']['unit']        = [''];
        }

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/orders/create", $this->data);
        $loadView->loadView();
    }

    /**
     * Adicionar um novo pedido ao sistema.
     * 
     * Este método valida os dados do formulário usando a classe de validação `ValidationUserRakitService` e,
     * se não houver erros, cria o pedido no banco de dados usando o `OrdersRepository`. Caso contrário, ele
     * recarrega a visualização de criação com mensagens de erro.
     * 
     * @return void
     */
    private function addOrder(): void
    {
        // Instaciar a classe que valida os dados do formulário de dados gerais do pedido com Rakit
        $validationOrder = new ValidationOrderService();
        $this->data['errors'] = $validationOrder->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewOrder();

            return;
        }

        // Instaciar a classe que valida os dados do formulário de itens do pedido com Rakit
        $validationItemns = new ValidationOrderItemnsService();
        $this->data['errors'] = $validationItemns->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewOrder();

            return;
        }

        // Instanciar o Repository para cadastrar o Pedido
        $orderCreate = new OrdersRepository();
        $result = $orderCreate->createOrder($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Pedido cadastrado com sucesso!";

            // Redirecionar o usuário para a página de visualizar o pedido recem criado
            header("Location: {$_ENV['URL_ADM']}view-order/$result");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            $this->data['errors'][] = "Pedido não cadastrado!";

            // Chamar o método carregar a view
            $this->viewOrder();
        }
    }
}
