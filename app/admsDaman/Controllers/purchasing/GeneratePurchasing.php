<?php

namespace App\admsDaman\Controllers\purchasing;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationPurchasingItemnsService;
use App\admsDaman\Controllers\Services\Validation\ValidationPurchasingService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\DiscountCalculator;
use App\admsDaman\Models\Repository\OrderCommentsRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\PaymentMethodsRepository;
use App\admsDaman\Models\Repository\PurchasingRepository;
use App\admsDaman\Models\Repository\SuppliersRepository;
use App\admsDaman\Views\Services\LoadViewService;

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
}
