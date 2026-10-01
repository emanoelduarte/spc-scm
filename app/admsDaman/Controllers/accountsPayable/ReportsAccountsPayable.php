<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Models\Repository\AccountsPayableReportsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;
use App\admsDaman\Views\Services\LoadViewService;
use DateTimeImmutable;

class ReportsAccountsPayable
{
    /**
     * Dados enviados para a View.
     */
    private array|string|null $data = null;


    /**
     * ============================================================
     * RELATÓRIOS DO CONTAS A PAGAR
     * ============================================================
     *
     * Responsabilidades:
     *
     * - recuperar os filtros informados pelo usuário;
     * - carregar obras e fornecedores;
     * - recuperar valores devidos por obra;
     * - recuperar valores devidos por fornecedor;
     * - calcular o total geral em aberto;
     * - carregar a View.
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


        $this->data['filters'] =
            $filters;

        /*
         * =====================================================
         * REPOSITORY DOS RELATÓRIOS
         * =====================================================
         */
        $reportsRepository =
            new AccountsPayableReportsRepository();


        /*
         * =====================================================
         * VALOR DEVIDO POR OBRA
         * =====================================================
         */
        $reportByProject =
            $reportsRepository
            ->getOpenAmountByProject(
                $filters
            );


        /*
         * =====================================================
         * VALOR DEVIDO POR FORNECEDOR
         * =====================================================
         */
        $reportBySupplier =
            $reportsRepository
            ->getOpenAmountBySupplier(
                $filters
            );


        $this->data['reportByProject'] =
            $reportByProject;


        $this->data['reportBySupplier'] =
            $reportBySupplier;


        /*
         * =====================================================
         * TOTAIS
         * =====================================================
         *
         * Utilizamos o relatório por obra como fonte do
         * total geral.
         *
         * O relatório por fornecedor deverá resultar no
         * mesmo valor, apenas agrupado de maneira diferente.
         */
        $this->data['totalOpenAmount'] =
            $this->calculateTotalOpenAmount(
                $reportByProject
            );

        $this->data['totalOpenAmountBySupplier'] =
            $this->calculateTotalOpenAmount(
                $reportBySupplier
            );

        $this->data['reportTotalsMatch'] =
            abs(
                (float) $this->data['totalOpenAmount']
                    -
                    (float) $this->data['totalOpenAmountBySupplier']
            ) < 0.01;


        /*
         * Quantidades auxiliares para os cards da tela.
         */
        $this->data['projectsCount'] =
            count(
                $reportByProject
            );


        $this->data['suppliersCount'] =
            count(
                $reportBySupplier
            );


        /*
         * =====================================================
         * OPÇÕES DOS FILTROS
         * =====================================================
         */
        $this->loadFilterOptions();


        /*
         * =====================================================
         * PERÍODO EXIBIDO
         * =====================================================
         *
         * Facilita a apresentação posteriormente na View,
         * PDF e Excel.
         */
        $this->data['periodLabel'] =
            $this->getPeriodLabel(
                $filters
            );


        /*
         * =====================================================
         * CONFIGURAÇÃO DA PÁGINA
         * =====================================================
         */
        $this->configurePage();


        /*
         * =====================================================
         * VIEW
         * =====================================================
         */
        $this->loadView();
    }


    /**
     * Recuperar e normalizar os filtros do relatório.
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
         * OBRA
         * =====================================================
         */
        $projectId =
            !empty($getFilters['project_id'])
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
         * FORNECEDOR
         * =====================================================
         */
        $supplierKey =
            trim(
                (string) (
                    $getFilters['supplier_key']
                    ?? ''
                )
            );


        if ($supplierKey === '') {
            $supplierKey = null;
        }


        /*
         * =====================================================
         * PERÍODO
         * =====================================================
         */
        $dueDateStart =
            $this->normalizeDate(
                $getFilters['due_date_start']
                    ?? null
            );


        $dueDateEnd =
            $this->normalizeDate(
                $getFilters['due_date_end']
                    ?? null
            );


        /*
         * Se o usuário informar as datas invertidas,
         * normalizamos automaticamente.
         *
         * Exemplo:
         *
         * De: 10/10/2026
         * Até: 01/10/2026
         *
         * passa a:
         *
         * De: 01/10/2026
         * Até: 10/10/2026
         */
        if (
            $dueDateStart !== null
            &&
            $dueDateEnd !== null
            &&
            $dueDateStart > $dueDateEnd
        ) {

            [
                $dueDateStart,
                $dueDateEnd
            ] = [
                $dueDateEnd,
                $dueDateStart
            ];
        }


        return [

            'project_id' =>
            $projectId,

            'supplier_key' =>
            $supplierKey,

            'due_date_start' =>
            $dueDateStart,

            'due_date_end' =>
            $dueDateEnd,
        ];
    }


    /**
     * Validar uma data no formato utilizado pelo
     * input HTML type="date": Y-m-d.
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
     * Carregar dados utilizados nos filtros.
     */
    private function loadFilterOptions(): void
    {
        /*
         * =====================================================
         * OBRAS
         * =====================================================
         */
        $projectsRepository =
            new ProjectsRepository();


        $this->data['projects'] =
            $projectsRepository
            ->getAllProjectsSelect();


        /*
         * =====================================================
         * FORNECEDORES
         * =====================================================
         *
         * Reutilizamos o método já existente porque ele
         * conhece a regra:
         *
         * tax:CNPJ
         *
         * ou
         *
         * supplier:ID
         */
        $purchaseDocumentsRepository =
            new PurchaseDocumentsRepository();


        $this->data['suppliers'] =
            $purchaseDocumentsRepository
            ->getPurchaseSuppliersSelect();
    }


    /**
     * Calcular o total geral devido.
     */
    private function calculateTotalOpenAmount(
        array $reportByProject
    ): float {

        $total = 0.0;


        foreach (
            $reportByProject as $project
        ) {

            $total +=
                (float) (
                    $project['open_amount']
                    ?? 0
                );
        }


        return round(
            $total,
            2
        );
    }


    /**
     * Criar uma descrição amigável do período.
     *
     * Exemplos:
     *
     * Sem período
     * 29/09/2026 a 05/10/2026
     * A partir de 29/09/2026
     * Até 05/10/2026
     */
    private function getPeriodLabel(
        array $filters
    ): string {

        $start =
            $filters['due_date_start']
            ?? null;


        $end =
            $filters['due_date_end']
            ?? null;


        if (
            empty($start)
            &&
            empty($end)
        ) {

            return 'Todo o período';
        }


        if (
            !empty($start)
            &&
            !empty($end)
        ) {

            return
                $this->formatDateBr(
                    $start
                )
                .
                ' a '
                .
                $this->formatDateBr(
                    $end
                );
        }


        if (!empty($start)) {

            return
                'A partir de '
                .
                $this->formatDateBr(
                    $start
                );
        }


        return
            'Até '
            .
            $this->formatDateBr(
                (string) $end
            );
    }


    /**
     * Converter Y-m-d para d/m/Y.
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


        return $dateObject->format(
            'd/m/Y'
        );
    }


    /**
     * Configurar título e menu ativo.
     */
    private function configurePage(): void
    {
        $pageElements = [

            'title_head' =>
            'Relatórios - Contas a Pagar',

            /*
             * Este valor deve corresponder ao nome da página
             * cadastrada no banco.
             *
             * Vamos cadastrar exatamente com este slug
             * quando chegarmos nessa etapa.
             */
            'menu' =>
            'reports-accounts-payable',

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
     * Carregar a View do relatório.
     */
    private function loadView(): void
    {
        $loadView =
            new LoadViewService(

                'admsDaman/Views/accountsPayable/reports',

                $this->data
            );


        $loadView->loadView();
    }
}
