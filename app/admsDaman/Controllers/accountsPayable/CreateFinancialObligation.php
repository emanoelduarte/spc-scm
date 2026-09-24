<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationManualPurchaseDocumentService;
use App\admsDaman\Controllers\Services\Validation\ValidationManualPurchaseInstallmentsService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\FinancialPaymentMethodsRepository;
use App\admsDaman\Models\Repository\PaymentMethodsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\SuppliersRepository;
use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;
use App\admsDaman\Models\Services\PurchaseDocumentService;
use App\admsDaman\Views\Services\LoadViewService;
use Throwable;

class CreateFinancialObligation
{
    /**
     * Dados enviados para a View.
     */
    private array|string|null $data = null;

    /**
     * Exibir/processar o cadastro de uma obrigação financeira.
     */
    public function index(): void
    {
        $this->data['form'] =
            filter_input_array(
                INPUT_POST,
                FILTER_UNSAFE_RAW
            ) ?? [];

        if (!empty($this->data['form'])) {
            if (
                isset($this->data['form']['csrf_token'])
                &&
                CSRFHelper::validateCSRFToken(
                    'form_create_financial_obligation',
                    $this->data['form']['csrf_token']
                )
            ) {
                $this->addFinancialObligation();
                return;
            }

            $this->data['errors'][] =
                'Token de segurança inválido ou expirado.';
        }

        $this->viewFinancialObligation();
    }

    /**
     * Carregar dados auxiliares e a View.
     */
    private function viewFinancialObligation(): void
    {
        /*
         * =====================================================
         * CREDORES / FORNECEDORES
         * =====================================================
         *
         * Nesta tela somente fornecedores ativos classificados
         * como "Obrigação Financeira" podem ser selecionados.
         */
        $suppliersRepository =
            new SuppliersRepository();

        $this->data['getFinancialObligationSuppliersSelect'] =
            $suppliersRepository
                ->getAllSuppliersSelectByType(
                    SuppliersRepository::TYPE_FINANCIAL_OBLIGATION,
                    true
                );

        /* Obras */
        $projectsRepository =
            new ProjectsRepository();

        $this->data['getAllProjectsSelectActive'] =
            $projectsRepository
                ->getAllProjectsSelectActive();

        /* Compradores / responsáveis */
        $usersAccessLevelsRepository =
            new UsersAccessLevelsRepository();

        $this->data['getPurchaseUsersSelect'] =
            $usersAccessLevelsRepository
                ->getPurchaseUsersSelect();

        /* Condições de pagamento */
        $paymentMethodsRepository =
            new PaymentMethodsRepository();

        $this->data['getAllPaymentSelect'] =
            $paymentMethodsRepository
                ->getAllPaymentSelect();

        /* Formas efetivas utilizadas na baixa */
        $financialPaymentMethodsRepository =
            new FinancialPaymentMethodsRepository();

        $this->data['getAllFinancialPaymentMethodsSelect'] =
            $financialPaymentMethodsRepository
                ->getAllActiveSelect();

        $pageElements = [
            'title_head' => 'Nova Obrigação Financeira',
            'menu' => 'list-purchase-documents',
            'buttonPermissions' => [],
        ];

        $pageLayoutService =
            new PageLayoutService();

        $this->data = array_merge(
            $this->data,
            $pageLayoutService
                ->configurePageElements(
                    $pageElements
                )
        );

        $loadView =
            new LoadViewService(
                'admsDaman/Views/accountsPayable/createFinancialObligation',
                $this->data
            );

        $loadView->loadView();
    }

    /**
     * Cadastrar a obrigação utilizando o mesmo motor financeiro
     * das compras manuais: documento, parcelas e baixas.
     */
    private function addFinancialObligation(): void
    {
        try {
            /*
             * Estes dados não devem depender de campos manipuláveis
             * enviados pelo navegador.
             */
            $this->data['form']['created_by'] =
                (int) ($_SESSION['user_id'] ?? 0);

            $this->data['form']['financial_entry_type'] =
                'financial_obligation';

            /*
             * Na obrigação financeira, "Data de Geração" utiliza
             * o campo purchase_date já existente no motor atual.
             * Também a usamos como data do documento/referência.
             */
            $this->data['form']['document_date'] =
                $this->data['form']['purchase_date']
                ?? null;

            /*
             * Não há necessidade de expor um tipo de documento
             * para o usuário nesta tela.
             */
            $this->data['form']['document_type'] = null;

            /* Validação comum do lançamento financeiro manual. */
            $validationPurchase =
                new ValidationManualPurchaseDocumentService();

            $this->data['errors'] =
                $validationPurchase->validate(
                    $this->data['form']
                );

            /*
             * Número / Referência é obrigatório especificamente
             * para obrigações financeiras.
             */
            $documentNumber =
                trim(
                    (string) (
                        $this->data['form']['document_number']
                        ?? ''
                    )
                );

            if ($documentNumber === '') {
                $this->data['errors']['document_number'] =
                    'Informe o número ou a referência da obrigação.';
            }

            /*
             * Mesmo que o select mostre apenas tipo 4, validamos
             * novamente no backend para impedir POST adulterado.
             */
            $supplierId =
                (int) (
                    $this->data['form']['adms_daman_supplier_id']
                    ?? 0
                );

            if ($supplierId > 0) {
                $suppliersRepository =
                    new SuppliersRepository();

                $supplier =
                    $suppliersRepository
                        ->getSupplier($supplierId);

                if (
                    !$supplier
                    ||
                    (int) (
                        $supplier['adms_daman_suppliers_types_id']
                        ?? 0
                    ) !== SuppliersRepository::TYPE_FINANCIAL_OBLIGATION
                    ||
                    (int) (
                        $supplier['supplier_status']
                        ?? 0
                    ) !== 1
                ) {
                    $this->data['errors']['adms_daman_supplier_id'] =
                        'Selecione um fornecedor ativo do tipo Obrigação Financeira.';
                }
            }

            if (!empty($this->data['errors'])) {
                $this->viewFinancialObligation();
                return;
            }

            /*
             * Quando estiver como "Falta boleto", as parcelas ainda
             * não são definitivas e não devem ser exigidas.
             */
            $paymentSchedulePending =
                !empty(
                    $this->data['form']['payment_schedule_pending']
                );

            if (!$paymentSchedulePending) {
                $validationInstallments =
                    new ValidationManualPurchaseInstallmentsService();

                $this->data['errors'] =
                    $validationInstallments->validate(
                        $this->data['form']
                    );

                if (!empty($this->data['errors'])) {
                    $this->viewFinancialObligation();
                    return;
                }
            }

            /*
             * Reutilizar integralmente o motor financeiro existente.
             */
            $service =
                new PurchaseDocumentService();

            $purchaseDocumentId =
                $service->createManual(
                    $this->data['form']
                );

            $_SESSION['success'] =
                'Obrigação financeira cadastrada com sucesso.';

            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'view-purchase-document/'
                    . $purchaseDocumentId
            );

            exit;
        } catch (Throwable $err) {
            $this->data['errors'][] =
                $err->getMessage();

            $this->viewFinancialObligation();
        }
    }
}
