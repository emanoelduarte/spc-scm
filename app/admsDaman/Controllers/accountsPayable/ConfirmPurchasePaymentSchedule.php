<?php

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Services\PurchaseDocumentService;
use Throwable;

class ConfirmPurchasePaymentSchedule
{
    /**
     * Confirmar as parcelas definitivas de um lançamento FB.
     */
    public function index(): void
    {
        /*
         * Recuperar dados enviados pelo modal.
         */
        $form =
            filter_input_array(
                INPUT_POST,
                FILTER_UNSAFE_RAW
            ) ?? [];

        /*
        * Usuário responsável pela confirmação
        * e pelas eventuais baixas automáticas.
        *
        * Nunca confiar em created_by vindo do formulário.
        */
        $form['created_by'] =
            (int) (
                $_SESSION['user_id']
                ?? 0
            );


        $purchaseDocumentId =
            (int) (
                $form['adms_daman_purchase_document_id']
                ?? 0
            );


        /*
         * URL utilizada tanto no sucesso quanto no erro.
         */
        $redirectUrl =
            $purchaseDocumentId > 0
            ? $_ENV['URL_ADM']
            . 'view-purchase-document/'
            . $purchaseDocumentId

            : $_ENV['URL_ADM']
            . 'list-purchase-documents';


        /*
         * =====================================================
         * ACEITAR SOMENTE POST
         * =====================================================
         */
        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET')
            !== 'POST'
        ) {

            $_SESSION['error'] =
                'Método de requisição inválido.';

            header(
                'Location: ' . $redirectUrl
            );

            exit;
        }


        /*
         * =====================================================
         * VALIDAR LANÇAMENTO
         * =====================================================
         */
        if ($purchaseDocumentId <= 0) {

            $_SESSION['error'] =
                'Lançamento financeiro inválido.';

            header(
                'Location: ' . $redirectUrl
            );

            exit;
        }


        /*
         * =====================================================
         * VALIDAR CSRF
         * =====================================================
         */
        $csrfToken =
            (string) (
                $form['csrf_token']
                ?? ''
            );


        if (
            empty($csrfToken)
            ||
            !CSRFHelper::validateCSRFToken(
                'form_confirm_purchase_payment_schedule',
                $csrfToken
            )
        ) {

            $_SESSION['error'] =
                'Token de segurança inválido ou expirado.';

            header(
                'Location: ' . $redirectUrl
            );

            exit;
        }


        /*
         * =====================================================
         * CONFIRMAR PARCELAMENTO
         * =====================================================
         */
        try {

            $service =
                new PurchaseDocumentService();


            $confirmedDocumentId =
                $service->confirmPaymentSchedule(
                    $form
                );


            $_SESSION['success'] =
                'Parcelas confirmadas com sucesso.';


            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'view-purchase-document/'
                    . $confirmedDocumentId
            );

            exit;
        } catch (Throwable $err) {

            /*
             * O Service já registra o erro e executa rollback.
             * Aqui apenas apresentamos a mensagem ao usuário.
             */
            $_SESSION['error'] =
                $err->getMessage();


            header(
                'Location: ' . $redirectUrl
            );

            exit;
        }
    }
}
