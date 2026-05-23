<?php

namespace App\admsDaman\Controllers\purchasing;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationPurchasingItemnsService;
use App\admsDaman\Controllers\Services\Validation\ValidationPurchasingService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\DiscountCalculator;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\OrderCommentsRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\PaymentMethodsRepository;
use App\admsDaman\Models\Repository\PurchasingQuoteRepository;
use App\admsDaman\Models\Repository\SuppliersRepository;
use App\admsDaman\Views\Services\LoadViewService;

class CreatePurchasingQuote
{
    /** @var array|string|null $data Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    public function index()
    {
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessar o IF se existir o CSRF e for valido o CSRF
        if (!isset($this->data['form']['csrf_token']) or !CSRFHelper::validateCSRFToken('form_generate_purchasing_quote', $this->data['form']['csrf_token'])) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Aprovação não enviada", []);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Aprovação não enviada!\n Dados não conferem";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}generate-purchasing/{$this->data['form']['adms_daman_order_id']}");

            return;
        }

        // Validar dados gerais
        $validationPurchasing = new ValidationPurchasingService();
        $this->data['errors'] = $validationPurchasing->validate($this->data['form']);

        if (!empty($this->data['errors'])) {

            // Redirecionar o usuário para a página listar
            $this->viewQuote();
            return;
        }

        // Validar itens
        $validationPurchasingItems = new ValidationPurchasingItemnsService();
        $this->data['errors'] = $validationPurchasingItems->validate($this->data['form']);

        if (!empty($this->data['errors'])) {
            // Redirecionar o usuário para a página listar
            $this->viewQuote();
            return;
        }

        // Calcular desconto
        $calculateDiscount = new DiscountCalculator();
        $discount = $calculateDiscount->calculateDiscount($this->data['form']);

        if ($discount !== false) {
            $this->data['form']['discount_value'] = $discount;
        } else {
            $this->data['errors'][] = "Para aplicar o desconto, é necessário informar o valor e escolher uma modalidade de desconto!";
            $this->viewQuote();
            return;
        }

        // Salvar cotação
        $createQuote = new PurchasingQuoteRepository();
        $result = $createQuote->createQuote($this->data['form']);

        if ($result) {
            // Mudar status do pedido para "Aguardando Aprovação"
            // $changeStatus = new OrdersRepository();
            // $changeStatus->updateOrderStatusToAwaitingApproval($this->data['form']['adms_daman_order_id']);

            // Comentário automático no pedido
            // $createComment = new OrderCommentsRepository();
            // $createComment->createAutomaticOrderAwaitingApproval($this->data['form']['adms_daman_order_id']);

            $_SESSION['success'] = "Cotação enviada para aprovação com sucesso!";

            header("Location: {$_ENV['URL_ADM']}view-purchasing-quote/{$result}");
            return;
        } else {
            $this->data['errors'][] = "Erro ao enviar cotação. Tente novamente.";
            $this->viewQuote();
        }
    }

    private function viewQuote(): void
    {
        // Instanciar o repositório para preencher os selects.
        $getOrder = new OrdersRepository();
        $this->data['getOrder'] = $getOrder->getOrder($this->data['form']['adms_daman_order_id']);

        // Instanciar o repositório para preencher os selects.
        $getPaymentsSelect = new PaymentMethodsRepository();
        $this->data['getPaymentsSelect'] = $getPaymentsSelect->getAllPaymentSelect();

        // Instanciar o repositório para preencher os selects.
        $getItems = new OrdersRepository();
        $this->data['getItems'] = $getItems->getItems($this->data['form']['adms_daman_order_id']);

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
