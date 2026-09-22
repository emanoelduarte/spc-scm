<?php

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;
use App\admsDaman\Models\Repository\PurchaseInstallmentPaymentsRepository;
use App\admsDaman\Models\Repository\PurchaseInstallmentsRepository;
use App\admsDaman\Views\Services\LoadViewService;
use App\admsDaman\Models\Repository\PurchaseDocumentAllocationsRepository;

class ListPurchaseDocuments
{
    /**
     * Dados enviados para a View.
     */
    private array|string|null $data = null;

    /**
     * Quantidade de lançamentos exibidos por página.
     */
    private int $limitResult = 10;

    /**
     * Exibir a listagem de compras / contas a pagar.
     *
     * O método principal funciona apenas como orquestrador da tela:
     * prepara filtros, indicadores, lançamentos, parcelas, pagamentos,
     * opções auxiliares e, por fim, carrega a View.
     */
    public function index(string|int $page = 1): void
    {
        $page = max(1, (int) $page);

        $filters = $this->getFilters();

        $this->data['filters'] = $filters;

        $purchaseDocumentsRepository =
            new PurchaseDocumentsRepository();

        /*
         * =====================================================
         * ALERTAS RÁPIDOS DO FINANCEIRO
         * =====================================================
         *
         * Uma única consulta recupera:
         *
         * - FB: lançamentos aguardando confirmação das parcelas;
         * - AT: quantidade de parcelas vencendo em até 7 dias;
         * - ON: quantidade de parcelas vencidas.
         *
         * Esses contadores são globais e funcionam como atalhos
         * para as principais pendências do Contas a Pagar.
         */
        $financialAlertCounts =
            $purchaseDocumentsRepository
                ->getFinancialAlertCounts();


        $this->data['pendingPaymentScheduleCount'] =
            (int) (
                $financialAlertCounts['pending_payment_schedule_count']
                ?? 0
            );


        $this->data['attentionInstallmentsCount'] =
            (int) (
                $financialAlertCounts['attention_installments_count']
                ?? 0
            );


        $this->data['overdueInstallmentsCount'] =
            (int) (
                $financialAlertCounts['overdue_installments_count']
                ?? 0
            );

        /*
         * Dados auxiliares utilizados pelos filtros da tela.
         */
        $this->loadFilterOptions(
            $purchaseDocumentsRepository
        );

        /*
         * Indicadores financeiros exibidos nos cards superiores.
         */
        $this->loadFinancialSummary(
            $purchaseDocumentsRepository,
            $filters
        );

        /*
         * Recuperar somente os lançamentos da página atual.
         */
        $purchaseDocuments =
            $purchaseDocumentsRepository
            ->getAllPurchaseDocuments(
                $page,
                $this->limitResult,
                $filters
            );

        /*
         * Paginação da listagem principal.
         */
        $this->data['pagination'] =
            PaginationService::generatePagination(
                $purchaseDocumentsRepository
                    ->getAmountPurchaseDocuments(
                        $filters
                    ),
                $this->limitResult,
                $page,
                'list-purchase-documents'
            );

        /*
         * Carregar os rateios das obras em lote.
         *
         * Cada lançamento receberá a chave "allocations"
         * contendo uma ou várias obras vinculadas ao documento.
         */
        $purchaseDocuments =
            $this->loadAllocations(
                $purchaseDocuments
            );

        /*
         * Anexar as parcelas aos respectivos lançamentos e carregar,
         * em lote, o histórico de pagamentos dessas parcelas.
         */
        $purchaseDocuments =
            $this->loadInstallmentsAndPayments(
                $purchaseDocuments
            );

        /*
         * Mantemos os dois índices por compatibilidade temporária
         * com trechos antigos da View.
         */
        $this->data['purchase_documents'] =
            $purchaseDocuments;

        $this->data['purchaseDocuments'] =
            $purchaseDocuments;

        $this->configurePage();
        $this->loadView();
    }

    /**
     * Recuperar e normalizar os filtros informados via GET.
     */
    private function getFilters(): array
    {
        $getFilters =
            filter_input_array(
                INPUT_GET,
                FILTER_UNSAFE_RAW
            ) ?? [];

        $installmentStatus =
            strtoupper(
                trim(
                    (string) (
                        $getFilters['installment_status']
                        ?? ''
                    )
                )
            );

        /*
        * =====================================================
        * SITUAÇÃO DO PARCELAMENTO
        * =====================================================
        *
        * pending:
        * falta boleto / parcelas ainda não confirmadas.
        *
        * confirmed:
        * parcelamento já definido.
        */
        $paymentScheduleStatus =
            strtolower(
                trim(
                    (string) (
                        $getFilters['payment_schedule_status']
                        ?? ''
                    )
                )
            );

        $allowedInstallmentStatuses = [
            'AV',
            'AT',
            'ON',
            'OK',
            'AP',
        ];

        $allowedPaymentScheduleStatuses = [
            'pending',
            'confirmed',
        ];

        return [
            'project_id' =>
            !empty($getFilters['project_id'])
                ? (int) $getFilters['project_id']
                : null,

            'supplier_key' =>
            !empty($getFilters['supplier_key'])
                ? trim(
                    (string) $getFilters['supplier_key']
                )
                : null,

            'document_number' =>
            !empty($getFilters['document_number'])
                ? trim(
                    (string) $getFilters['document_number']
                )
                : null,

            'payment_schedule_status' =>
            in_array(
                $paymentScheduleStatus,
                $allowedPaymentScheduleStatuses,
                true
            )
                ? $paymentScheduleStatus
                : null,

            'installment_status' =>
            in_array(
                $installmentStatus,
                $allowedInstallmentStatuses,
                true
            )
                ? $installmentStatus
                : null,

            'due_date_start' =>
            !empty($getFilters['due_date_start'])
                ? $getFilters['due_date_start']
                : null,

            'due_date_end' =>
            !empty($getFilters['due_date_end'])
                ? $getFilters['due_date_end']
                : null,
        ];
    }

    /**
     * Carregar as opções utilizadas nos filtros da listagem.
     *
     * Fornecedores e obras são recuperados separadamente porque
     * pertencem a repositórios distintos.
     */
    private function loadFilterOptions(
        PurchaseDocumentsRepository $purchaseDocumentsRepository
    ): void {
        $this->data['suppliers'] =
            $purchaseDocumentsRepository
            ->getPurchaseSuppliersSelect();

        $projectsRepository =
            new ProjectsRepository();

        $this->data['projects'] =
            $projectsRepository
            ->getAllProjectsSelect();
    }

    /**
     * Carregar os indicadores financeiros apresentados nos cards.
     *
     * Os cards de vencimento próximo e vencido forçam, respectivamente,
     * os status AT e ON, mas continuam respeitando os demais filtros.
     */
    private function loadFinancialSummary(
        PurchaseDocumentsRepository $purchaseDocumentsRepository,
        array $filters
    ): void {
        $this->data['totalOpenAmount'] =
            $purchaseDocumentsRepository
            ->getTotalOpenAmount(
                $filters
            );

        $this->data['dueSoonAmount'] =
            $purchaseDocumentsRepository
            ->getTotalOpenAmount(
                $filters,
                'AT'
            );

        $this->data['overdueAmount'] =
            $purchaseDocumentsRepository
            ->getTotalOpenAmount(
                $filters,
                'ON'
            );

        $this->data['totalPurchaseDocumentsAmount'] =
            $purchaseDocumentsRepository
            ->getTotalPurchaseDocumentsAmount(
                $filters
            );
    }

    /**
     * Carregar os rateios dos lançamentos exibidos na página atual.
     *
     * A consulta é realizada em lote para evitar o problema de N+1.
     * Cada lançamento recebe a chave "allocations" com suas obras
     * e respectivos valores apropriados.
     */
    private function loadAllocations(
        array $purchaseDocuments
    ): array {
        /*
         * Se não houver lançamentos na página, não executar consulta.
         */
        if (empty($purchaseDocuments)) {
            return $purchaseDocuments;
        }

        $purchaseDocumentIds =
            array_column(
                $purchaseDocuments,
                'id'
            );

        $allocationRepository =
            new PurchaseDocumentAllocationsRepository();

        $allocations =
            $allocationRepository
            ->getByPurchaseDocumentIds(
                $purchaseDocumentIds
            );

        /*
         * Agrupar as alocações pelo lançamento financeiro.
         *
         * Estrutura resultante:
         *
         * [
         *     10 => [allocation, allocation],
         *     11 => [allocation],
         * ]
         */
        $allocationsByDocument = [];

        foreach ($allocations as $allocation) {
            $documentId =
                (int) $allocation['adms_daman_purchase_document_id'];

            $allocationsByDocument[$documentId][] =
                $allocation;
        }

        /*
         * Anexar as alocações aos respectivos lançamentos.
         */
        foreach (
            $purchaseDocuments as &$purchaseDocument
        ) {
            $documentId =
                (int) ($purchaseDocument['id'] ?? 0);

            $purchaseDocument['allocations'] =
                $allocationsByDocument[$documentId]
                ?? [];
        }

        /*
         * Remover a referência criada pelo foreach.
         */
        unset($purchaseDocument);

        return $purchaseDocuments;
    }

    /**
     * Carregar as parcelas dos lançamentos exibidos na página atual
     * e o histórico de pagamentos associado a essas parcelas.
     *
     * As consultas são feitas em lote para evitar o problema de N+1.
     */
    private function loadInstallmentsAndPayments(
        array $purchaseDocuments
    ): array {
        $purchaseDocumentIds =
            array_column(
                $purchaseDocuments,
                'id'
            );

        $purchaseInstallmentsRepository =
            new PurchaseInstallmentsRepository();

        $installments =
            $purchaseInstallmentsRepository
            ->getByPurchaseDocumentIds(
                $purchaseDocumentIds
            );

        $installmentsByDocument =
            $this->groupInstallmentsByDocument(
                $installments
            );

        foreach (
            $purchaseDocuments as &$purchaseDocument
        ) {
            $documentId =
                (int) $purchaseDocument['id'];

            $purchaseDocument['installments'] =
                $installmentsByDocument[$documentId]
                ?? [];
        }

        /*
         * Remover a referência criada pelo foreach.
         */
        unset($purchaseDocument);

        $this->data['paymentsByInstallment'] =
            $this->getPaymentsByInstallment(
                $installments
            );

        return $purchaseDocuments;
    }

    /**
     * Agrupar as parcelas pelo ID do lançamento financeiro.
     *
     * Estrutura retornada:
     *
     * [
     *     1 => [parcela, parcela],
     *     3 => [parcela],
     * ]
     */
    private function groupInstallmentsByDocument(
        array $installments
    ): array {
        $installmentsByDocument = [];

        foreach ($installments as $installment) {
            $documentId =
                (int) $installment['adms_daman_purchase_document_id'];

            $installmentsByDocument[$documentId][] =
                $installment;
        }

        return $installmentsByDocument;
    }

    /**
     * Recuperar os pagamentos das parcelas exibidas na página atual
     * e agrupá-los pelo ID da parcela.
     *
     * Pagamentos ativos e estornados são mantidos porque este conjunto
     * representa o histórico financeiro, não apenas o saldo atual.
     */
    private function getPaymentsByInstallment(
        array $installments
    ): array {
        $installmentIds = [];

        foreach ($installments as $installment) {
            $installmentId =
                (int) ($installment['id'] ?? 0);

            if ($installmentId > 0) {
                $installmentIds[] = $installmentId;
            }
        }

        $paymentRepository =
            new PurchaseInstallmentPaymentsRepository();

        $payments =
            $paymentRepository
            ->getByInstallmentIds(
                $installmentIds
            );

        $paymentsByInstallment = [];

        foreach ($payments as $payment) {
            $installmentId =
                (int) $payment['adms_daman_purchase_installment_id'];

            $paymentsByInstallment[$installmentId][] =
                $payment;
        }

        return $paymentsByInstallment;
    }

    /**
     * Configurar título, menu ativo e permissões da página.
     */
    private function configurePage(): void
    {
        $pageElements = [
            'title_head' => 'Compras',
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
    }

    /**
     * Carregar a View da listagem de contas a pagar.
     */
    private function loadView(): void
    {
        $loadView =
            new LoadViewService(
                'admsDaman/Views/accountsPayable/list',
                $this->data
            );

        $loadView->loadView();
    }
}
