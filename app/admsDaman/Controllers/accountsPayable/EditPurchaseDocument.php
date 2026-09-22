<?php

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\PurchaseDocumentAllocationsRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;
use App\admsDaman\Models\Repository\PurchaseInstallmentPaymentsRepository;
use App\admsDaman\Models\Repository\PurchaseInstallmentsRepository;
use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;
use App\admsDaman\Models\Services\PurchaseDocumentService;
use App\admsDaman\Views\Services\LoadViewService;
use Throwable;

/**
 * Controller responsável por editar
 * um lançamento financeiro de compra.
 */
class EditPurchaseDocument
{
    /**
     * Dados enviados para a View.
     */
    private array|string|null $data = null;


    /**
     * Abrir / processar a edição do lançamento.
     */
    public function index(string|int $id): void
    {
        $purchaseDocumentId =
            (int) $id;


        if ($purchaseDocumentId <= 0) {

            $_SESSION['error'] =
                'Lançamento financeiro inválido.';

            $this->redirectToList();
        }


        /*
         * =====================================================
         * PROCESSAR POST
         * =====================================================
         */
        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET')
            === 'POST'
        ) {

            $form =
                filter_input_array(
                    INPUT_POST,
                    FILTER_UNSAFE_RAW
                )
                ?? [];


            $this->data['form'] =
                $form;


            try {

                if (
                    !CSRFHelper::validateCSRFToken(
                        'form_edit_purchase_document',
                        $form['csrf_token']
                            ?? ''
                    )
                ) {

                    throw new \InvalidArgumentException(
                        'Token de segurança inválido ou expirado.'
                    );
                }


                /*
                 * O ID do lançamento vem da rota.
                 * Não confiar em um ID enviado pelo navegador.
                 */
                $form['adms_daman_purchase_document_id'] =
                    $purchaseDocumentId;


                /*
                 * O responsável pela alteração sempre vem
                 * da sessão autenticada.
                 */
                $form['updated_by'] =
                    (int) (
                        $_SESSION['user_id']
                        ?? 0
                    );


                $service =
                    new PurchaseDocumentService();


                $updatedDocumentId =
                    $service
                        ->updatePurchaseDocument(
                            $form
                        );


                $_SESSION['success'] =
                    'Lançamento financeiro atualizado com sucesso.';


                header(
                    'Location: '
                    . $_ENV['URL_ADM']
                    . 'view-purchase-document/'
                    . $updatedDocumentId
                );

                exit;

            } catch (Throwable $err) {

                $this->data['errors'][] =
                    $err->getMessage();
            }
        }


        /*
         * Em GET ou após falha de validação,
         * recarregar os dados oficiais do banco.
         */
        $this->loadEditData(
            $purchaseDocumentId
        );


        $this->configurePage();
        $this->loadView();
    }


    /**
     * Recuperar todos os dados necessários
     * para montar a tela de edição.
     */
    private function loadEditData(
        int $purchaseDocumentId
    ): void {

        /*
         * =====================================================
         * LANÇAMENTO
         * =====================================================
         */
        $purchaseDocumentsRepository =
            new PurchaseDocumentsRepository();


        $purchaseDocument =
            $purchaseDocumentsRepository
                ->getById(
                    $purchaseDocumentId
                );


        if (!$purchaseDocument) {

            $_SESSION['error'] =
                'Lançamento financeiro não encontrado.';

            $this->redirectToList();
        }


        /*
         * =====================================================
         * PARCELAS
         * =====================================================
         */
        $purchaseInstallmentsRepository =
            new PurchaseInstallmentsRepository();


        $installments =
            $purchaseInstallmentsRepository
                ->getByPurchaseDocumentIds(
                    [
                        $purchaseDocumentId
                    ]
                );


        /*
         * =====================================================
         * HISTÓRICO FINANCEIRO
         * =====================================================
         */
        $installmentIds = [];


        foreach ($installments as $installment) {

            $installmentId =
                (int) (
                    $installment['id']
                    ?? 0
                );


            if ($installmentId > 0) {

                $installmentIds[] =
                    $installmentId;
            }
        }


        $paymentsByInstallment = [];


        if (!empty($installmentIds)) {

            $paymentsRepository =
                new PurchaseInstallmentPaymentsRepository();


            $payments =
                $paymentsRepository
                    ->getByInstallmentIds(
                        $installmentIds
                    );


            foreach ($payments as $payment) {

                $installmentId =
                    (int) (
                        $payment['adms_daman_purchase_installment_id']
                        ?? 0
                    );


                if ($installmentId <= 0) {
                    continue;
                }


                $paymentsByInstallment[$installmentId][] =
                    $payment;
            }
        }


        /*
         * =====================================================
         * CLASSIFICAR PARCELAS
         * =====================================================
         *
         * Bloqueada quando:
         *
         * - existe qualquer pagamento ativo ou estornado;
         * - ou o registro está armazenado como OK.
         */
        $hasFinancialMovement =
            false;


        foreach ($installments as &$installment) {

            $installmentId =
                (int) (
                    $installment['id']
                    ?? 0
                );


            $paymentHistory =
                $paymentsByInstallment[$installmentId]
                ?? [];


            $storedStatus =
                strtoupper(
                    (string) (
                        $installment['status']
                        ?? 'AV'
                    )
                );


            $isLocked =
                !empty($paymentHistory)
                ||
                $storedStatus === 'OK';


            $installment['edit_locked'] =
                $isLocked;


            $installment['has_financial_history'] =
                !empty($paymentHistory);


            $installment['payment_history_count'] =
                count($paymentHistory);


            if ($isLocked) {

                $hasFinancialMovement =
                    true;
            }
        }


        unset($installment);


        /*
         * =====================================================
         * RATEIO
         * =====================================================
         */
        $allocationsRepository =
            new PurchaseDocumentAllocationsRepository();


        $allocations =
            $allocationsRepository
                ->getByPurchaseDocumentId(
                    $purchaseDocumentId
                );


        /*
         * =====================================================
         * COMPRADORES
         * =====================================================
         */
        $usersAccessLevelsRepository =
            new UsersAccessLevelsRepository();


        $buyers =
            $usersAccessLevelsRepository
                ->getPurchaseUsersSelect();


        /*
         * =====================================================
         * ENVIAR PARA A VIEW
         * =====================================================
         */
        $this->data['purchaseDocument'] =
            $purchaseDocument;


        $this->data['installments'] =
            $installments;


        $this->data['allocations'] =
            $allocations;


        $this->data['paymentsByInstallment'] =
            $paymentsByInstallment;


        $this->data['hasFinancialMovement'] =
            $hasFinancialMovement;


        $this->data['getPurchaseUsersSelect'] =
            is_array($buyers)
                ? $buyers
                : [];
    }


    /**
     * Configurar título e menu ativo.
     */
    private function configurePage(): void
    {
        $pageElements = [
            'title_head' =>
                'Editar Lançamento',

            'menu' =>
                'list-purchase-documents',

            'buttonPermissions' =>
                [],
        ];


        $pageLayoutService =
            new PageLayoutService();


        $this->data =
            array_merge(
                $this->data,
                $pageLayoutService
                    ->configurePageElements(
                        $pageElements
                    )
            );
    }


    /**
     * Carregar a View de edição.
     */
    private function loadView(): void
    {
        $loadView =
            new LoadViewService(
                'admsDaman/Views/accountsPayable/edit',
                $this->data
            );


        $loadView->loadView();
    }


    /**
     * Voltar para a listagem financeira.
     */
    private function redirectToList(): never
    {
        header(
            'Location: '
            . $_ENV['URL_ADM']
            . 'list-purchase-documents'
        );


        exit;
    }
}
