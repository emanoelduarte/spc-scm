<?php

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;
use App\admsDaman\Models\Repository\PurchaseInstallmentsRepository;
use App\admsDaman\Views\Services\LoadViewService;
use App\admsDaman\Models\Repository\PurchaseDocumentAllocationsRepository;
use App\admsDaman\Models\Repository\PaymentMethodsRepository;
use App\admsDaman\Models\Repository\PurchaseInstallmentPaymentsRepository;

class ViewPurchaseDocument
{
    /**
     * Dados enviados para a View.
     */
    private array|string|null $data = null;


    /**
     * Visualizar um lançamento financeiro.
     *
     * Recupera os dados principais do lançamento,
     * suas informações relacionadas e todas as parcelas
     * vinculadas ao lançamento.
     *
     * @param string|int $id
     * ID do lançamento financeiro recebido pela rota.
     *
     * @return void
     */
    public function index(string|int $id): void
    {
        /*
         * Converter o ID recebido pela rota
         * para inteiro.
         */
        $purchaseDocumentId = (int) $id;


        /*
         * Impedir consultas com ID inválido.
         */
        if ($purchaseDocumentId <= 0) {

            $_SESSION['error'] =
                'Lançamento financeiro inválido.';

            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'list-nfes'
            );

            exit;
        }


        /*
         * Recuperar os dados principais
         * do lançamento financeiro.
         */
        $purchaseDocumentsRepository =
            new PurchaseDocumentsRepository();

        $purchaseDocument =
            $purchaseDocumentsRepository->getById(
                $purchaseDocumentId
            );


        /*
         * Caso o lançamento não exista,
         * retornar para a listagem de NF-e.
         */
        if (!$purchaseDocument) {

            $_SESSION['error'] =
                'Lançamento financeiro não encontrado.';

            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'list-purchase-documents'
            );

            exit;
        }


        /*
         * Recuperar todas as parcelas
         * pertencentes ao lançamento.
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
        * RECUPERAR HISTÓRICO DE PAGAMENTOS
        * =====================================================
        *
        * A tela de detalhes também precisa conhecer:
        *
        * - pagamentos ativos;
        * - pagamentos estornados;
        * - histórico completo de cada parcela.
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
        * RECUPERAR RATEIO ENTRE OBRAS
        * =====================================================
        *
        * Todo lançamento novo possui pelo menos uma
        * alocação:
        *
        * - uma obra: 100% do lançamento;
        * - duas ou mais obras: lançamento rateado.
        */
        $purchaseDocumentAllocationsRepository =
            new PurchaseDocumentAllocationsRepository();

        $allocations =
            $purchaseDocumentAllocationsRepository
            ->getByPurchaseDocumentId(
                $purchaseDocumentId
            );

        /*
        * =====================================================
        * CONDIÇÕES DE PAGAMENTO PARA FB
        * =====================================================
        *
        * Carregar as condições somente quando o lançamento
        * ainda estiver aguardando confirmação das parcelas.
        */
        $this->data['getAllPaymentSelect'] = [];

        if (
            (
                $purchaseDocument['payment_schedule_status']
                ?? 'confirmed'
            ) === 'pending'
        ) {

            $paymentMethodsRepository =
                new PaymentMethodsRepository();

            $this->data['getAllPaymentSelect'] =
                $paymentMethodsRepository
                ->getAllPaymentSelect();
        }


        /*
         * Disponibilizar os dados para a View.
         */
        $this->data['purchaseDocument'] =
            $purchaseDocument;

        $this->data['installments'] =
            $installments;

        $this->data['allocations'] =
            $allocations;

        $this->data['paymentsByInstallment'] =
            $paymentsByInstallment;


        /*
         * =====================================================
         * PERMISSÃO FUNCIONAL PARA EXCLUIR O LANÇAMENTO
         * =====================================================
         *
         * O botão só será exibido quando não existir qualquer
         * movimentação financeira.
         *
         * O Service repetirá toda a validação no backend.
         */
        $canDeletePurchaseDocument =
            (
                ($purchaseDocument['status'] ?? 'open')
                !== 'closed'
            );


        if ($canDeletePurchaseDocument) {

            foreach ($installments as $installment) {

                $installmentId =
                    (int) (
                        $installment['id']
                        ?? 0
                    );


                /*
                 * Proteger registros OK mesmo em algum cenário
                 * legado sem histórico de pagamento associado.
                 */
                if (
                    strtoupper(
                        (string) (
                            $installment['status']
                            ?? ''
                        )
                    ) === 'OK'
                ) {

                    $canDeletePurchaseDocument =
                        false;

                    break;
                }


                /*
                 * Qualquer pagamento, ativo ou estornado,
                 * impede a exclusão do lançamento.
                 */
                if (
                    !empty(
                        $paymentsByInstallment[$installmentId]
                        ?? []
                    )
                ) {

                    $canDeletePurchaseDocument =
                        false;

                    break;
                }
            }
        }


        $this->data['canDeletePurchaseDocument'] =
            $canDeletePurchaseDocument;


        /*
         * Configurar título, menu ativo
         * e permissões da página.
         */
        $pageElements = [
            'title_head' => 'Visualizar Lançamento',
            'menu' => 'list-purchase-documents',
            'buttonPermissions' => [],
        ];

        $pageLayoutService =
            new PageLayoutService();

        $this->data = array_merge(
            $this->data,
            $pageLayoutService->configurePageElements(
                $pageElements
            )
        );


        /*
         * Carregar a View responsável
         * pela apresentação do lançamento.
         */
        $loadView =
            new LoadViewService(
                'admsDaman/Views/accountsPayable/view',
                $this->data
            );

        $loadView->loadView();
    }
}
