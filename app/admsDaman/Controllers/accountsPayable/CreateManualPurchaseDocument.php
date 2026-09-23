<?php

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationManualPurchaseDocumentService;
use App\admsDaman\Controllers\Services\Validation\ValidationManualPurchaseInstallmentsService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\PaymentMethodsRepository;
use App\admsDaman\Models\Repository\FinancialPaymentMethodsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\SuppliersRepository;
use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;
use App\admsDaman\Models\Services\PurchaseDocumentService;
use App\admsDaman\Views\Services\LoadViewService;
use Throwable;

class CreateManualPurchaseDocument
{
    /**
     * Dados enviados para a View.
     */
    private array|string|null $data = null;


    /**
     * Abrir formulário para cadastro de uma compra avulsa.
     *
     * Diferente do CreatePurchaseDocument, este lançamento
     * não depende de uma NF-e importada pela SEFAZ.
     *
     * A compra poderá possuir outro documento de origem,
     * como cupom fiscal, recibo, nota manual ou até mesmo
     * um lançamento sem documento fiscal.
     *
     * @return void
     */
    public function index(): void
    {
        /*
     * Recuperar os dados enviados pelo formulário.
     *
     * Mantemos os dados em $this->data['form']
     * para conseguir restaurar os campos caso
     * alguma validação retorne erro.
     */
        $this->data['form'] =
            filter_input_array(
                INPUT_POST,
                FILTER_UNSAFE_RAW
            ) ?? [];


        /*
     * =====================================================
     * PROCESSAR POST
     * =====================================================
     */
        if (!empty($this->data['form'])) {

            /*
         * Validar o token CSRF específico
         * do lançamento avulso.
         */
            if (
                isset(
                    $this->data['form']['csrf_token']
                )
                &&
                CSRFHelper::validateCSRFToken(
                    'form_create_manual_purchase_document',
                    $this->data['form']['csrf_token']
                )
            ) {

                /*
             * Enviar os dados para o Service.
             */
                $this->addManualPurchaseDocument();

                return;
            }


            /*
         * POST realizado com token inválido.
         */
            $this->data['errors'][] =
                'Token de segurança inválido ou expirado.';
        }


        /*
     * GET normal ou POST com erro:
     * carregar novamente o formulário.
     */
        $this->viewManualPurchaseDocument();
    }


    /**
     * Carregar a tela de criação do lançamento avulso.
     *
     * Recupera os dados que já são compartilhados com
     * o lançamento originado de NF-e:
     *
     * - Obras;
     * - Compradores;
     * - Condições de pagamento.
     *
     * O fornecedor será adicionado depois que confirmarmos
     * o Repository e a estrutura da tabela de fornecedores.
     *
     * @return void
     */
    private function viewManualPurchaseDocument(): void
    {
        /*
        * =====================================================
        * FORNECEDORES
        * =====================================================
        *
        * Recuperar somente fornecedores ativos
        * aptos para compras.
        */
        $suppliersRepository =
            new SuppliersRepository();

        $this->data['getAllSuppliersSelectActive'] =
            $suppliersRepository
            ->getAllSuppliersSelectActive();

        /*
         * =====================================================
         * OBRAS
         * =====================================================
         */
        $projectsRepository =
            new ProjectsRepository();

        $this->data['getAllProjectsSelectActive'] =
            $projectsRepository
            ->getAllProjectsSelectActive();


        /*
         * =====================================================
         * COMPRADORES
         * =====================================================
         */
        $usersAccessLevelsRepository =
            new UsersAccessLevelsRepository();

        $this->data['getPurchaseUsersSelect'] =
            $usersAccessLevelsRepository
            ->getPurchaseUsersSelect();


        /*
         * =====================================================
         * CONDIÇÕES DE PAGAMENTO
         * =====================================================
         */
        $paymentMethodsRepository =
            new PaymentMethodsRepository();

        $this->data['getAllPaymentSelect'] =
            $paymentMethodsRepository
            ->getAllPaymentSelect();


        /*
         * =====================================================
         * FORMAS DE PAGAMENTO DA BAIXA
         * =====================================================
         *
         * Esta lista representa como o pagamento efetivamente
         * saiu do caixa: PIX, boleto, transferência etc.
         */
        $financialPaymentMethodsRepository =
            new FinancialPaymentMethodsRepository();

        $this->data['getAllFinancialPaymentMethodsSelect'] =
            $financialPaymentMethodsRepository
            ->getAllActiveSelect();


        /*
         * =====================================================
         * CONFIGURAÇÃO DA PÁGINA
         * =====================================================
         */
        $pageElements = [
            'title_head' => 'Novo Lançamento',
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


        /*
         * =====================================================
         * CARREGAR VIEW
         * =====================================================
         *
         * Usaremos uma View própria para o lançamento avulso
         * para não arriscar alterações na tela de NF-e que
         * já está funcionando.
         */
        $loadView =
            new LoadViewService(
                'admsDaman/Views/accountsPayable/createManual',
                $this->data
            );

        $loadView->loadView();
    }

    /**
     * Cadastrar o lançamento financeiro avulso.
     *
     * O Controller apenas prepara os dados relacionados
     * à sessão e entrega o restante para o Service.
     *
     * As validações, transação e gravação são responsabilidade
     * do PurchaseDocumentService.
     *
     * @return void
     */
    private function addManualPurchaseDocument(): void
    {
        try {

            /*
            * Usuário que está realizando
            * o lançamento no sistema.
            *
            * Não recebemos esse valor pelo formulário
            * para evitar manipulação pelo navegador.
            */
            $this->data['form']['created_by'] =
                (int) $_SESSION['user_id'];

            /*
            * Validar os dados gerais do lançamento manual.
            */
            $validationPurchase =
                new ValidationManualPurchaseDocumentService();

            $this->data['errors'] =
                $validationPurchase->validate(
                    $this->data['form']
                );

            if (!empty($this->data['errors'])) {

                $this->viewManualPurchaseDocument();

                return;
            }


            /*
            * =====================================================
            * VALIDAR PARCELAS
            * =====================================================
            *
            * Quando estiver marcado como "Falta boleto",
            * o lançamento ainda não possui parcelamento
            * definitivo.
            *
            * Nesse cenário não devemos exigir parcelas.
            */
            $paymentSchedulePending =
                !empty($this->data['form']['payment_schedule_pending']);


            if (!$paymentSchedulePending) {

                /*
                * Fluxo normal:
                * validar vencimentos, valores e soma das parcelas.
                */
                $validationInstallments =
                    new ValidationManualPurchaseInstallmentsService();


                $this->data['errors'] =
                    $validationInstallments->validate(
                        $this->data['form']
                    );


                if (!empty($this->data['errors'])) {

                    $this->viewManualPurchaseDocument();

                    return;
                }
            }

            /*
            * Executar o fluxo transacional
            * do lançamento avulso.
            */
            $service =
                new PurchaseDocumentService();


            $purchaseDocumentId =
                $service->createManual(
                    $this->data['form']
                );


            /*
         * Informar sucesso para a próxima página.
         */
            $_SESSION['success'] =
                'Lançamento financeiro cadastrado com sucesso.';


            /*
         * Após cadastrar, abrir o lançamento criado
         * para o usuário conferir os dados gravados.
         */
            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'view-purchase-document/'
                    . $purchaseDocumentId
            );

            exit;
        } catch (Throwable $err) {

            /*
         * Manter os dados preenchidos no formulário.
         */
            $this->data['errors'][] =
                $err->getMessage();


            /*
         * Recarregar a mesma tela mostrando o erro.
         */
            $this->viewManualPurchaseDocument();
        }
    }
}
