<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\financial;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationDirectExpenseService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\ExpenseCategoriesRepository;
use App\admsDaman\Models\Repository\FinancialPaymentMethodsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Services\DirectExpenseService;
use App\admsDaman\Views\Services\LoadViewService;
use Throwable;

class CreateDirectExpense
{
    /**
     * Dados enviados para a View.
     *
     * @var array|string|null
     */
    private array|string|null $data = null;


    /**
     * Abrir e processar o formulário de despesa direta.
     */
    public function index(): void
    {
        $this->data['form'] =
            filter_input_array(
                INPUT_POST,
                FILTER_UNSAFE_RAW
            ) ?? [];

        if (!empty(
            $this->data['form']
        )) {

            if (
                isset(
                    $this->data['form']['csrf_token']
                )
                &&
                CSRFHelper::validateCSRFToken(
                    'form_create_direct_expense',
                    $this->data['form']['csrf_token']
                )
            ) {
                $this->addDirectExpense();

                return;
            }

            $this->data['errors'][] =
                'Token de segurança inválido ou expirado.';
        }

        $this->view();
    }


    /**
     * Carregar formulário.
     */
    private function view(): void
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


        $pageElements = [
            'title_head' =>
                'Nova Despesa Direta',

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


        $loadView =
            new LoadViewService(
                'admsDaman/Views/financial/createDirectExpense',
                $this->data
            );

        $loadView->loadView();
    }


    /**
     * Validar e cadastrar a despesa direta.
     */
    private function addDirectExpense(): void
    {
        try {
            $this->data['form']['created_by'] =
                (int) (
                    $_SESSION['user_id']
                    ?? 0
                );


            $validation =
                new ValidationDirectExpenseService();

            $this->data['errors'] =
                $validation->validate(
                    $this->data['form']
                );


            if (!empty(
                $this->data['errors']
            )) {
                $this->view();

                return;
            }


            $service =
                new DirectExpenseService();

            $directExpenseId =
                $service->create(
                    $this->data['form']
                );


            $_SESSION['success'] =
                'Despesa direta cadastrada com sucesso.';


            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'create-direct-expense'
                    . '?created='
                    . $directExpenseId
            );

            exit;
        } catch (Throwable $err) {
            $this->data['errors'][] =
                $err->getMessage();

            $this->view();
        }
    }
}
