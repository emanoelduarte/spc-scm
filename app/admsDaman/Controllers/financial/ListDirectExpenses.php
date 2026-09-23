<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\financial;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Models\Repository\DirectExpensesRepository;
use App\admsDaman\Models\Repository\ExpenseCategoriesRepository;
use App\admsDaman\Models\Repository\FinancialPaymentMethodsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Views\Services\LoadViewService;
use DateTime;

class ListDirectExpenses
{
    /**
     * Dados enviados para a View.
     *
     * @var array|string|null
     */
    private array|string|null $data = null;


    /**
     * Quantidade de registros por página.
     */
    private int $limitResult = 10;


    /**
     * Listar despesas diretas.
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


        $filters = [
            'project_id' =>
                !empty(
                    $getFilters['project_id']
                )
                    ? (int) $getFilters['project_id']
                    : null,

            'category_id' =>
                !empty(
                    $getFilters['category_id']
                )
                    ? (int) $getFilters['category_id']
                    : null,

            'payment_method_id' =>
                !empty(
                    $getFilters['payment_method_id']
                )
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

            'description' =>
                !empty(
                    $getFilters['description']
                )
                    ? trim(
                        (string) $getFilters['description']
                    )
                    : null,
        ];


        $this->data['filters'] =
            $filters;


        $repository =
            new DirectExpensesRepository();


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


        $this->data['direct_expenses'] =
            $repository->getAll(
                $page,
                $this->limitResult,
                $filters
            );


        $this->data['total_amount'] =
            $repository->getTotalAmount(
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
     * Carregar selects dos filtros.
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
     * Aceitar somente datas válidas no padrão Y-m-d.
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
     * Configuração visual/permissões.
     */
    private function configurePage(): void
    {
        $pageElements = [
            'title_head' =>
                'Despesas Diretas',

            'menu' =>
                'list-direct-expenses',

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
     * Carregar view.
     */
    private function loadView(): void
    {
        $loadView =
            new LoadViewService(
                'admsDaman/Views/financial/listDirectExpenses',
                $this->data
            );


        $loadView->loadView();
    }
}
