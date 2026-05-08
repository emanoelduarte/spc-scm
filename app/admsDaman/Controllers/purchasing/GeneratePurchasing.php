<?php

namespace App\admsDaman\Controllers\purchasing;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationPurchasingItemnsService;
use App\admsDaman\Controllers\Services\Validation\ValidationPurchasingService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\DiscountCalculator;
use App\admsDaman\Helpers\NormalizeDecimal;
use App\admsDaman\Models\Repository\OrderCommentsRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\PaymentMethodsRepository;
use App\admsDaman\Models\Repository\PurchasingRepository;
use App\admsDaman\Models\Repository\SuppliersRepository;
use App\admsDaman\Views\Services\LoadViewService;
use Normalizer;

class GeneratePurchasing
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $id = null;

    /**
     * Método principal que gerencia a geração de compras.
     *
     * Este método é chamado para processar a geração de uma nova compra. Ele verifica a validade do token CSRF,
     * valida os dados do formulário e, se tudo estiver correto, gera a compra. Caso contrário, carrega a
     * visualização de geração de compra com mensagens de erro.
     * 
     * @return void
     */
    public function index(int $orderId)
    {
        $this->id = $orderId;

        // Receber os dados do formulário de cadastro de pedido
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);


        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_generate_purchasing', $this->data['form']['csrf_token'])) {

            // Chamar método cadastrar passando pelas validações necessárias
            $this->addPurchasing();
            // var_dump($this->data['form']);
            // exit;
        } else {
            // Chamar o método carregar a view
            $this->viewPurchasing();
        }
    }

    /**
     * Carregar a visualização de geração de compra.
     * 
     * Este método configura os dados necessários e carrega a view para a geração de uma nova compra.
     * 
     * @return void
     */
    private function viewPurchasing(): void
    {
        // Instanciar o repositório para preencher os selects.
        $getOrder = new OrdersRepository();
        $this->data['getOrder'] = $getOrder->getOrder($this->id);

        // Instanciar o repositório para preencher os selects.
        $getPaymentsSelect = new PaymentMethodsRepository();
        $this->data['getPaymentsSelect'] = $getPaymentsSelect->getAllPaymentSelect();

        // Instanciar o repositório para preencher os selects.
        $getItems = new OrdersRepository();
        $this->data['getItems'] = $getItems->getItems($this->id);

        // Instanciar o repositório para preencher os selects.
        $getAllSuppliersSelectActive = new SuppliersRepository();
        $this->data['getAllSuppliersSelectActive'] = $getAllSuppliersSelectActive->getAllSuppliersSelectActive();

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Gerar Compra",
            'menu' => "list-purchasings",
            'buttonPermissions' => ["ListPurchasings"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/purchasing/generate", $this->data);
        $loadView->loadView();
    }

    private function addPurchasing()
    {
        // Instaciar a classe que valida os dados do formulário de dados gerais do pedido com Rakit
        $validationPurchasing = new ValidationPurchasingService();
        $this->data['errors'] = $validationPurchasing->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewPurchasing();

            return;
        }

        // Instaciar a classe que valida os dados do formulário de dados gerais do pedido com Rakit
        $validationPurchasingItems = new ValidationPurchasingItemnsService();
        $this->data['errors'] = $validationPurchasingItems->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewPurchasing();

            return;
        }

        // Calcular Desconto e atribuir novo valor ao array ou retornar mensagem de erro para o usuário
        $calculateDiscount = new DiscountCalculator();
        $discount = $calculateDiscount->calculateDiscount($this->data['form']);

        if ($discount !== false) {
            $this->data['form']['discount_value'] = $discount;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            $this->data['errors'][] = "Para aplicar o desconto, é necessário informar o valor e escolher uma modalidade de desconto!";

            // Chamar o método carregar a view
            $this->viewPurchasing();

            return;
        }

        // Instanciar o Repository para cadastrar o Compra
        $generatePurchasing = new PurchasingRepository();
        $result = $generatePurchasing->generatePurchasing($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {
            // Atualizar Status do pedido
            $changeStatus = new OrdersRepository();
            $changeStatus->updateAutomaticOrderStatus($this->data['form']['adms_daman_order_id']);

            // Criar comentário com a data da compra relacionada ao pedido
            $createComment = new OrderCommentsRepository();
            $createComment->createAutomaticOrderPurchased($this->data['form']['adms_daman_order_id']);

            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Compra cadastrada com sucesso!";

            // Redirecionar o usuário para a página de visualizar a compra recem criada
            header("Location: {$_ENV['URL_ADM']}view-purchasing/$result");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            $this->data['errors'][] = "Compra não cadastrada!";

            // Chamar o método carregar a view
            $this->viewPurchasing();
        }
    }
}
