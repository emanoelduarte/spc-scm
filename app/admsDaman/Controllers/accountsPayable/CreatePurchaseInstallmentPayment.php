<?php

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Services\PurchaseInstallmentPaymentService;
use Throwable;

class CreatePurchaseInstallmentPayment
{
    /**
     * Registrar a baixa/pagamento de uma parcela.
     */
    public function index(): void
    {

        /*
         * =====================================================
         * RECUPERAR DADOS DO FORMULÁRIO
         * =====================================================
         */
        $data =
            filter_input_array(
                INPUT_POST,
                FILTER_UNSAFE_RAW
            ) ?? [];

        /*
         * =====================================================
         * ACEITAR SOMENTE POST
         * =====================================================
         */
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            $_SESSION['error'] =
                'Operação inválida.';

            $this->redirectToList($data);

            return;
        }


        /*
         * =====================================================
         * VALIDAR CSRF
         * =====================================================
         */
        if (
            empty($data['csrf_token'])
            ||
            !CSRFHelper::validateCSRFToken(
                'form_purchase_installment_payment',
                $data['csrf_token']
            )
        ) {

            $_SESSION['error'] =
                'Token de segurança inválido ou expirado.';

            $this->redirectToList($data);

            return;
        }


        /*
         * =====================================================
         * USUÁRIO RESPONSÁVEL PELA BAIXA
         * =====================================================
         *
         * Nunca confiar em created_by vindo
         * diretamente do formulário.
         */
        $data['created_by'] =
            (int) (
                $_SESSION['user_id']
                ?? 0
            );


        try {

            /*
             * =================================================
             * REGISTRAR PAGAMENTO
             * =================================================
             */
            $service =
                new PurchaseInstallmentPaymentService();


            $paymentId =
                $service->createPayment(
                    $data
                );


            /*
             * =================================================
             * SUCESSO
             * =================================================
             */
            $_SESSION['success'] =
                'Baixa da parcela registrada com sucesso. '
                . 'Pagamento nº '
                . $paymentId
                . '.';


            $this->redirectToList($data);
        } catch (Throwable $e) {

            /*
             * =================================================
             * ERRO
             * =================================================
             *
             * As mensagens das regras financeiras do Service
             * serão apresentadas ao usuário.
             *
             * Exemplos:
             *
             * - parcela já quitada;
             * - principal maior que o saldo;
             * - data inválida;
             * - valores inválidos.
             */
            $_SESSION['error'] =
                $e->getMessage();


            $this->redirectToList($data);
        }
    }


    /**
     * ====================================================
     * FAZER O REDIRECIONAMENTO CORRETO
     * ====================================================
     *
     * Se a baixa foi realizada pela tela de detalhes,
     * retornar para o lançamento.
     *
     * Caso contrário, retornar para a listagem.
     */
    private function redirectToList(
        array $data = []
    ): void {

        $redirectPurchaseDocumentId =
            (int) (
                $data['redirect_purchase_document_id']
                ?? 0
            );


        if ($redirectPurchaseDocumentId > 0) {

            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'view-purchase-document/'
                    . $redirectPurchaseDocumentId
            );

            exit;
        }


        header(
            'Location: '
                . $_ENV['URL_ADM']
                . 'list-purchase-documents'
        );

        exit;
    }
}
