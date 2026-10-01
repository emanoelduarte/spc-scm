<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\financial;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationDirectExpenseService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\DirectExpensesRepository;
use App\admsDaman\Models\Repository\ExpenseCategoriesRepository;
use App\admsDaman\Models\Repository\FinancialPaymentMethodsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Services\DirectExpenseService;
use App\admsDaman\Views\Services\LoadViewService;
use Throwable;

class EditDirectExpense
{
    /**
     * Dados enviados para a View.
     */
    private array|string|null $data = null;


    /**
     * ID da despesa direta.
     */
    private int $id;


    /**
     * Abrir/processar edição.
     */
    public function index(
        string|int $id
    ): void {

        $this->id =
            (int) $id;


        if ($this->id <= 0) {

            $_SESSION['error'] =
                'Despesa direta inválida.';


            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'list-direct-expenses'
            );

            exit;
        }


        /*
         * =====================================================
         * POST
         * =====================================================
         */
        $this->data['form'] =
            filter_input_array(
                INPUT_POST,
                FILTER_UNSAFE_RAW
            )
            ?? [];


        if (
            !empty($this->data['form'])
        ) {

            if (
                isset(
                    $this->data['form']['csrf_token']
                )
                &&
                CSRFHelper::validateCSRFToken(
                    'form_edit_direct_expense_'
                        . $this->id,
                    $this->data['form']['csrf_token']
                )
            ) {

                $this->updateDirectExpense();

                return;
            }


            $this->data['errors'][] =
                'Token de segurança inválido ou expirado.';


            $this->view();

            return;
        }


        /*
         * =====================================================
         * GET
         * =====================================================
         */
        $this->loadDirectExpense();


        $this->view();
    }


    /**
     * ============================================================
     * CARREGAR DADOS EXISTENTES
     * ============================================================
     */
    private function loadDirectExpense(): void
    {
        $repository =
            new DirectExpensesRepository();


        $expense =
            $repository->getById(
                $this->id
            );


        if ($expense === null) {

            $_SESSION['error'] =
                'Despesa direta não encontrada.';


            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'list-direct-expenses'
            );

            exit;
        }


        $allocations =
            $repository
            ->getAllocationsByExpenseId(
                $this->id
            );


        /*
         * Existe rateio real quando existem
         * duas ou mais obras.
         *
         * Sem rateio o sistema possui apenas
         * a alocação automática de 100%.
         */
        $hasProration =
            count(
                $allocations
            ) > 1;


        /*
         * Preparar rateios no mesmo formato
         * esperado pelo formulário / JS.
         */
        $formAllocations = [];


        if ($hasProration) {

            foreach (
                $allocations
                as $allocation
            ) {

                $formAllocations[] = [

                    'adms_daman_project_id' =>
                    (int) $allocation['adms_daman_project_id'],

                    'allocated_amount' =>
                    number_format(
                        (float) $allocation['allocated_amount'],
                        2,
                        ',',
                        '.'
                    ),
                ];
            }
        }


        /*
         * =====================================================
         * FORMULÁRIO
         * =====================================================
         */
        $this->data['form'] = [

            'adms_daman_project_id' =>
            (int) $expense['adms_daman_project_id'],

            'adms_daman_expense_category_id' =>
            (int) $expense['adms_daman_expense_category_id'],

            'adms_daman_financial_payment_method_id' =>
            (int) $expense['adms_daman_financial_payment_method_id'],

            'expense_date' =>
            $expense['expense_date'],

            'description' =>
            $expense['description'],

            'amount' =>
            number_format(
                (float) $expense['amount'],
                2,
                ',',
                '.'
            ),

            'observation' =>
            $expense['observation']
                ?? '',

            'has_proration' =>
            $hasProration
                ? 1
                : 0,

            'allocations' =>
            $formAllocations,
        ];


        /*
         * Guardamos os dados originais caso
         * seja útil mostrar alguma informação.
         */
        $this->data['direct_expense'] =
            $expense;
    }


    /**
     * ============================================================
     * ATUALIZAR
     * ============================================================
     */
    private function updateDirectExpense(): void
    {
        try {

            /*
             * Usuário responsável pela alteração.
             */
            $this->data['form']['updated_by'] =
                (int) (
                    $_SESSION['user_id']
                    ?? 0
                );


            /*
             * =================================================
             * VALIDAÇÃO BÁSICA
             * =================================================
             */
            $validation =
                new ValidationDirectExpenseService();


            $this->data['errors'] =
                $validation->validate(
                    $this->data['form']
                );


            if (
                !empty($this->data['errors'])
            ) {

                $this->view();

                return;
            }


            /*
             * =================================================
             * SERVICE
             * =================================================
             */
            $service =
                new DirectExpenseService();


            $updated =
                $service->update(
                    $this->id,
                    $this->data['form']
                );


            if (!$updated) {

                throw new \RuntimeException(
                    'Não foi possível atualizar a despesa direta.'
                );
            }


            $_SESSION['success'] =
                'Despesa direta atualizada com sucesso.';


            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'list-direct-expenses'
            );

            exit;
        } catch (Throwable $err) {

            $this->data['errors'][] =
                $err->getMessage();


            $this->view();
        }
    }


    /**
     * ============================================================
     * VIEW
     * ============================================================
     */
    private function view(): void
    {
        /*
         * Obras.
         */
        $projectsRepository =
            new ProjectsRepository();


        $this->data['projects'] =
            $projectsRepository
            ->getAllProjectsSelectActive();


        /*
         * Categorias.
         */
        $categoriesRepository =
            new ExpenseCategoriesRepository();


        $this->data['expense_categories'] =
            $categoriesRepository
            ->getAllActiveSelect();


        /*
         * Formas de pagamento.
         */
        $paymentMethodsRepository =
            new FinancialPaymentMethodsRepository();


        $this->data['financial_payment_methods'] =
            $paymentMethodsRepository
            ->getAllActiveSelect();


        $this->data['direct_expense_id'] =
            $this->id;


        /*
         * Página.
         */
        $pageElements = [

            'title_head' =>
            'Editar Despesa Direta',

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


        /*
         * REUTILIZAR A VIEW DE CRIAÇÃO.
         */
        $loadView =
            new LoadViewService(
                'admsDaman/Views/financial/updateDirectExpense',
                $this->data
            );

        $loadView->loadView();
    }
}
