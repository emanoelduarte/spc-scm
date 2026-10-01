<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Models\Repository\AccountsPayableReportsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;
use App\admsDaman\Views\Services\LoadViewService;
use DateTimeImmutable;

class PaymentProvision
{
    /**
     * Dados enviados para a View.
     */
    private array|string|null $data = null;


    /**
     * ============================================================
     * PROVISÃO DE PAGAMENTOS
     * ============================================================
     */
    public function index(): void
    {
        /*
         * =====================================================
         * FILTROS
         * =====================================================
         */
        $filters =
            $this->getFilters();


        /*
         * A data selecionada pelo usuário é normalizada
         * para a terça-feira da semana financeira.
         */
        $startDate =
            $this->normalizeToFinancialWeekTuesday(
                $filters['reference_date']
            );


        $filters['start_date'] =
            $startDate;


        $this->data['filters'] =
            $filters;


        /*
         * =====================================================
         * GERAR PROVISÃO
         * =====================================================
         */
        $reportsRepository =
            new AccountsPayableReportsRepository();


        $provision =
            $reportsRepository
                ->getPaymentProvisionByWeeks(
                    $startDate,
                    $filters['weeks'],
                    [
                        'project_id' =>
                            $filters['project_id'],

                        'excluded_supplier_keys' =>
                            $filters[
                                'excluded_supplier_keys'
                            ],
                    ]
                );


        $this->data['provision'] =
            $provision;


        /*
         * =====================================================
         * DADOS AUXILIARES
         * =====================================================
         */
        $this->loadFilterOptions();


        /*
         * =====================================================
         * INFORMAÇÕES DA SEMANA FINANCEIRA
         * =====================================================
         */
        $this->data['financialWeekLabel'] =
            'Terça-feira a Segunda-feira';


        $this->data['projectionPeriodLabel'] =
            $this->formatDateBr(
                $provision['start_date']
            )
            .
            ' a '
            .
            $this->formatDateBr(
                $provision['end_date']
            );


        /*
         * =====================================================
         * PÁGINA
         * =====================================================
         */
        $this->configurePage();

        $this->loadView();
    }


    /**
     * ============================================================
     * FILTROS
     * ============================================================
     */
    private function getFilters(): array
    {
        $getFilters =
            filter_input_array(
                INPUT_GET,
                FILTER_UNSAFE_RAW
            ) ?? [];


        /*
         * =====================================================
         * DATA DE REFERÊNCIA
         * =====================================================
         *
         * Se nada for informado, utilizamos hoje.
         *
         * Posteriormente ela será convertida para a
         * terça-feira da respectiva semana financeira.
         */
        $referenceDate =
            $this->normalizeDate(
                $getFilters['reference_date']
                ?? null
            );


        if ($referenceDate === null) {

            $referenceDate =
                (new DateTimeImmutable())
                    ->format(
                        'Y-m-d'
                    );
        }


        /*
         * =====================================================
         * QUANTIDADE DE SEMANAS
         * =====================================================
         */
        $weeks =
            isset(
                $getFilters['weeks']
            )
                ? (int) $getFilters['weeks']
                : 4;


        /*
         * Valores que queremos oferecer na interface.
         */
        $allowedWeeks = [
            4,
            5,
            6,
            8,
            12,
        ];


        if (
            !in_array(
                $weeks,
                $allowedWeeks,
                true
            )
        ) {
            $weeks = 4;
        }


        /*
         * =====================================================
         * OBRA
         * =====================================================
         */
        $projectId =
            !empty(
                $getFilters['project_id']
            )
                ? (int)
                    $getFilters['project_id']
                : null;


        if (
            $projectId !== null
            &&
            $projectId <= 0
        ) {
            $projectId = null;
        }


        /*
         * =====================================================
         * FORNECEDORES EXCLUÍDOS
         * =====================================================
         */
        $excludedSupplierKeys =
            $getFilters[
                'excluded_supplier_keys'
            ]
            ?? [];


        if (
            !is_array(
                $excludedSupplierKeys
            )
        ) {

            $excludedSupplierKeys = [];
        }


        $excludedSupplierKeys =
            array_values(
                array_filter(
                    array_map(
                        static fn(
                            mixed $supplierKey
                        ): string =>
                            trim(
                                (string) $supplierKey
                            ),

                        $excludedSupplierKeys
                    ),

                    static fn(
                        string $supplierKey
                    ): bool =>
                        $supplierKey !== ''
                )
            );


        return [

            'reference_date' =>
                $referenceDate,

            'weeks' =>
                $weeks,

            'project_id' =>
                $projectId,

            'excluded_supplier_keys' =>
                $excludedSupplierKeys,
        ];
    }


    /**
     * Converter qualquer data para a terça-feira
     * da respectiva semana financeira.
     *
     * Semana:
     *
     * terça -> quarta -> quinta -> sexta
     * -> sábado -> domingo -> segunda
     */
    private function normalizeToFinancialWeekTuesday(
        string $date
    ): string {

        $dateObject =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $date
            );


        if ($dateObject === false) {

            throw new \InvalidArgumentException(
                'Data de referência inválida.'
            );
        }


        /*
         * ISO:
         *
         * segunda = 1
         * terça   = 2
         * ...
         * domingo = 7
         */
        $dayOfWeek =
            (int) $dateObject
                ->format('N');


        /*
         * Quantos dias voltar até terça-feira.
         *
         * terça   -> 0
         * quarta  -> 1
         * quinta  -> 2
         * ...
         * segunda -> 6
         */
        $daysSinceTuesday =
            (
                $dayOfWeek
                -
                2
                +
                7
            )
            %
            7;


        if ($daysSinceTuesday > 0) {

            $dateObject =
                $dateObject->modify(
                    '-'
                    .
                    $daysSinceTuesday
                    .
                    ' days'
                );
        }


        return
            $dateObject->format(
                'Y-m-d'
            );
    }


    /**
     * Validar Y-m-d.
     */
    private function normalizeDate(
        mixed $date
    ): ?string {

        $date =
            trim(
                (string) $date
            );


        if ($date === '') {

            return null;
        }


        $dateObject =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $date
            );


        if (
            $dateObject === false
            ||
            $dateObject->format(
                'Y-m-d'
            ) !== $date
        ) {

            return null;
        }


        return $date;
    }


    /**
     * ============================================================
     * OPÇÕES DOS FILTROS
     * ============================================================
     */
    private function loadFilterOptions(): void
    {
        /*
         * Obras.
         */
        $projectsRepository =
            new ProjectsRepository();


        $this->data['projects'] =
            $projectsRepository
                ->getAllProjectsSelect();


        /*
         * Fornecedores.
         *
         * Reutilizamos exatamente a identidade
         * tax:CNPJ / supplier:ID já utilizada
         * pelo módulo financeiro.
         */
        $purchaseDocumentsRepository =
            new PurchaseDocumentsRepository();


        $this->data['suppliers'] =
            $purchaseDocumentsRepository
                ->getPurchaseSuppliersSelect();
    }


    /**
     * Formatar data.
     */
    private function formatDateBr(
        string $date
    ): string {

        $dateObject =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $date
            );


        if ($dateObject === false) {
            return $date;
        }


        return
            $dateObject->format(
                'd/m/Y'
            );
    }


    /**
     * Configuração da página.
     */
    private function configurePage(): void
    {
        $pageElements = [

            'title_head' =>
                'Provisão de Pagamentos',

            'menu' =>
                'payment-provision',

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
     * Carregar View.
     */
    private function loadView(): void
    {
        $loadView =
            new LoadViewService(

                'admsDaman/Views/accountsPayable/paymentProvision',

                $this->data
            );


        $loadView->loadView();
    }
}