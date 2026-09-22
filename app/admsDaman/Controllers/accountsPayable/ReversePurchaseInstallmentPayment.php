<?php

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\PurchaseInstallmentPaymentService;
use Throwable;


/**
 * Controller responsável por realizar o estorno
 * de pagamentos de parcelas de compras.
 */
class ReversePurchaseInstallmentPayment
{
    /**
     * Processar solicitação de estorno.
     */
    public function index(): void
    {
        /*
         * =====================================================
         * ACEITAR SOMENTE POST
         * =====================================================
         */
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            $_SESSION['error'] =
                'Método de requisição inválido.';


            /*
             * Neste ponto ainda não existem dados do formulário.
             */
            $this->redirectAfterOperation();

            return;
        }


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


        try {

            /*
             * =================================================
             * VALIDAR CSRF
             * =================================================
             */
            if (
                !CSRFHelper::validateCSRFToken(
                    'form_reverse_purchase_installment_payment',
                    $data['csrf_token']
                        ?? ''
                )
            ) {

                GenerateLog::generateLog(
                    'error',
                    'Token de segurança inválido.',
                    []
                );


                throw new \InvalidArgumentException(
                    'Token de segurança inválido.'
                );
            }


            /*
             * =================================================
             * PAGAMENTO QUE SERÁ ESTORNADO
             * =================================================
             */
            $paymentId =
                (int) (
                    $data['payment_id']
                    ?? 0
                );


            /*
             * =================================================
             * USUÁRIO RESPONSÁVEL PELO ESTORNO
             * =================================================
             *
             * Sempre utilizar o usuário da sessão.
             */
            $reversedBy =
                (int) (
                    $_SESSION['user_id']
                    ?? 0
                );


            /*
             * =================================================
             * MOTIVO DO ESTORNO
             * =================================================
             */
            $reason =
                trim(
                    $data['reversal_reason']
                        ?? ''
                );


            /*
             * =================================================
             * REALIZAR ESTORNO
             * =================================================
             */
            $service =
                new PurchaseInstallmentPaymentService();


            $service->reversePayment(
                $paymentId,
                $reversedBy,
                $reason
            );


            /*
             * =================================================
             * SUCESSO
             * =================================================
             */
            $_SESSION['success'] =
                'Pagamento estornado com sucesso!';
        } catch (Throwable $e) {

            /*
             * =================================================
             * ERRO
             * =================================================
             */
            GenerateLog::generateLog(
                'error',
                'Erro na operação de estorno',
                [
                    'error' =>
                    $e->getMessage(),
                ]
            );


            $_SESSION['error'] =
                $e->getMessage();
        }


        /*
         * =====================================================
         * REDIRECIONAR PARA A ORIGEM
         * =====================================================
         */
        $this->redirectAfterOperation(
            $data
        );
    }


    /**
     * Redirecionar após o estorno.
     *
     * Se a operação veio da tela de detalhes,
     * retornar para o próprio lançamento.
     *
     * Caso contrário, retornar para a listagem.
     */
    private function redirectAfterOperation(
        array $data = []
    ): void {

        $purchaseDocumentId =
            (int) (
                $data['redirect_purchase_document_id']
                ?? 0
            );


        /*
         * Operação realizada pela view do lançamento.
         */
        if ($purchaseDocumentId > 0) {

            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'view-purchase-document/'
                    . $purchaseDocumentId
            );

            exit;
        }


        /*
         * Operação realizada pela listagem.
         */
        header(
            'Location: '
                . $_ENV['URL_ADM']
                . 'list-purchase-documents'
        );

        exit;
    }
}
