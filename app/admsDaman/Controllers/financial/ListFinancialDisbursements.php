<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\financial;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Models\Repository\ExpenseCategoriesRepository;
use App\admsDaman\Models\Repository\FinancialDisbursementsRepository;
use App\admsDaman\Models\Repository\FinancialPaymentMethodsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Views\Services\LoadViewService;
use DateTime;

class ListFinancialDisbursements
{
    /**
     * Dados enviados para a View.
     *
     * @var array|string|null
     */
    private array|string|null $data = null;


    /**
     * Quantidade de linhas apropriadas por página.
     */
    private int $limitResult = 20;


    /**
     * Exibir o desembolso real consolidado das obras.
     */
    public function index(): void
    {
        $getFilters =
            filter_input_array(
                INPUT_GET,
                FILTER_UNSAFE_RAW
            ) ?? [];


        $page =
            isset($getFilters['page'])
                ? max(
                    1,
                    (int) $getFilters['page']
                )
                : 1;


        $origin =
            strtolower(
                trim(
                    (string) (
                        $getFilters['origin']
                        ?? ''
                    )
                )
            );


        $allowedOrigins = [
            'purchase',
            'direct',
        ];


        $categoryKey =
            $this->normalizeCategoryKey(
                $getFilters['category_key']
                ?? null
            );


        $filters = [
            'project_id' =>
                !empty($getFilters['project_id'])
                    ? (int) $getFilters['project_id']
                    : null,

            'origin' =>
                in_array(
                    $origin,
                    $allowedOrigins,
                    true
                )
                    ? $origin
                    : null,

            'category_key' =>
                $categoryKey,

            'payment_method_id' =>
                !empty($getFilters['payment_method_id'])
                    ? (int) $getFilters['payment_method_id']
                    : null,

            'date_start' =>
                $this->normalizeDate(
                    $getFilters['date_start']
                    ?? null
                ),

            'date_end' =>
                $this->normalizeDate(
                    $getFilters['date_end']
                    ?? null
                ),

            'search' =>
                !empty($getFilters['search'])
                    ? trim(
                        (string) $getFilters['search']
                    )
                    : null,
        ];


        /*
         * Uma categoria direta nunca pode retornar compras,
         * e a categoria sintética "purchase" representa
         * exclusivamente pagamentos de compras.
         */
        if (
            $filters['category_key'] === 'purchase'
        ) {
            $filters['origin'] =
                'purchase';
        } elseif (
            str_starts_with(
                (string) $filters['category_key'],
                'direct:'
            )
        ) {
            $filters['origin'] =
                'direct';
        }


        $this->data['filters'] =
            $filters;


        $repository =
            new FinancialDisbursementsRepository();


        $amountRecords =
            $repository->getAmount(
                $filters
            );


        $totalPages =
            max(
                1,
                (int) ceil(
                    $amountRecords
                    / $this->limitResult
                )
            );


        $page =
            min(
                $page,
                $totalPages
            );


        $this->data['financial_disbursements'] =
            $repository->getAll(
                $page,
                $this->limitResult,
                $filters
            );


        $this->data['summary'] =
            $repository->getSummary(
                $filters
            );


        /*
         * =====================================================
         * INDICADORES GERENCIAIS
         * =====================================================
         *
         * Todos respeitam exatamente os mesmos filtros da
         * listagem e dos cards superiores.
         */
        $this->data['category_breakdown'] =
            $repository->getCategoryBreakdown(
                $filters
            );


        $this->data['payment_method_breakdown'] =
            $repository->getPaymentMethodBreakdown(
                $filters
            );


        $this->data['project_breakdown'] =
            $repository->getProjectBreakdown(
                $filters
            );


        $this->data['monthly_trend'] =
            $repository->getMonthlyTrend(
                $filters
            );


        $this->data['pagination'] = [
            'current_page' =>
                $page,

            'total_pages' =>
                $totalPages,

            'total_records' =>
                $amountRecords,

            'limit' =>
                $this->limitResult,
        ];


        $this->loadFilterOptions();

        $this->configurePage();

        $this->loadView();
    }


    /**
     * Carregar opções dos filtros.
     */
    private function loadFilterOptions(): void
    {
        $projectsRepository =
            new ProjectsRepository();

        $this->data['projects'] =
            $projectsRepository
                ->getAllProjectsSelectActive();


        $categoriesRepository =
            new ExpenseCategoriesRepository();

        $this->data['expense_categories'] =
            $categoriesRepository
                ->getAllActiveSelect();


        $paymentMethodsRepository =
            new FinancialPaymentMethodsRepository();

        $this->data['financial_payment_methods'] =
            $paymentMethodsRepository
                ->getAllActiveSelect();
    }


    /**
     * Validar data Y-m-d.
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
            DateTime::createFromFormat(
                'Y-m-d',
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
     * Validar a chave de categoria utilizada pela visão unificada.
     *
     * purchase  = categoria sintética das compras;
     * direct:N  = categoria cadastrada nas despesas diretas.
     */
    private function normalizeCategoryKey(
        mixed $categoryKey
    ): ?string {

        $categoryKey =
            trim(
                (string) $categoryKey
            );


        if ($categoryKey === '') {
            return null;
        }


        if ($categoryKey === 'purchase') {
            return 'purchase';
        }


        if (
            preg_match(
                '/^direct:[1-9][0-9]*$/',
                $categoryKey
            )
        ) {
            return $categoryKey;
        }


        return null;
    }


    /**
     * Configurar título e menu ativo.
     */
    private function configurePage(): void
    {
        $pageElements = [
            'title_head' =>
                'Desembolso de Obras',

            'menu' =>
                'list-financial-disbursements',

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
                'admsDaman/Views/financial/listFinancialDisbursements',
                $this->data
            );


        $loadView->loadView();
    }
}
