<?php

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Services\PurchaseDocumentService;
use Throwable;

/**
 * Controller responsável por excluir um lançamento financeiro
 * que ainda não possui qualquer histórico de pagamento.
 */
class DeletePurchaseDocument
{
    /**
     * Processar exclusão.
     */
    public function index(): void
    {
        /*
         * Exclusão somente via POST.
         */
        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET')
            !== 'POST'
        ) {

            $_SESSION['error'] =
                'Método de requisição inválido.';

            $this->redirectToList();
        }


        $form =
            filter_input_array(
                INPUT_POST,
                FILTER_UNSAFE_RAW
            )
            ?? [];


        $purchaseDocumentId =
            (int) (
                $form['adms_daman_purchase_document_id']
                ?? 0
            );


        try {

            /*
             * =================================================
             * CSRF
             * =================================================
             */
            if (
                !CSRFHelper::validateCSRFToken(
                    'form_delete_purchase_document',
                    $form['csrf_token']
                        ?? ''
                )
            ) {

                throw new \InvalidArgumentException(
                    'Token de segurança inválido ou expirado.'
                );
            }


            /*
             * Confirmação explícita do usuário.
             *
             * Não dependemos apenas do checkbox required do HTML.
             */
            if (
                (
                    $form['confirm_delete']
                    ?? ''
                ) !== '1'
            ) {

                throw new \InvalidArgumentException(
                    'Confirme que está ciente da exclusão do lançamento.'
                );
            }


            /*
             * O usuário responsável sempre vem da sessão.
             */
            $form['deleted_by'] =
                (int) (
                    $_SESSION['user_id']
                    ?? 0
                );


            $service =
                new PurchaseDocumentService();


            $deletedDocument =
                $service
                    ->deletePurchaseDocument(
                        $form
                    );


            $_SESSION['success'] =
                'Lançamento financeiro excluído com sucesso.';


            /*
             * NF-e:
             * permanece conferida e volta a ficar disponível
             * para um novo lançamento.
             *
             * Compra manual:
             * retornar à listagem financeira.
             */
            if (
                (
                    $deletedDocument['document_origin']
                    ?? 'MANUAL'
                ) === 'NFE'
            ) {

                header(
                    'Location: '
                    . $_ENV['URL_ADM']
                    . 'list-nfes'
                );

                exit;
            }


            $this->redirectToList();

        } catch (Throwable $err) {

            $_SESSION['error'] =
                $err->getMessage();


            if ($purchaseDocumentId > 0) {

                header(
                    'Location: '
                    . $_ENV['URL_ADM']
                    . 'view-purchase-document/'
                    . $purchaseDocumentId
                );

                exit;
            }


            $this->redirectToList();
        }
    }


    /**
     * Retornar para Contas a Pagar.
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
