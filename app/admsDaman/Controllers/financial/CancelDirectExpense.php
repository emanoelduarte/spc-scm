<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\financial;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\DirectExpensesRepository;
use Throwable;

class CancelDirectExpense
{
    /**
     * Cancelar uma despesa direta.
     */
    public function index(
        string|int $id
    ): void {

        $directExpenseId =
            (int) $id;


        /*
         * ============================================================
         * VALIDAR ID
         * ============================================================
         */
        if ($directExpenseId <= 0) {

            $_SESSION['error'] =
                'Despesa direta inválida.';

            $this->redirect();

            return;
        }


        /*
         * ============================================================
         * ACEITAR SOMENTE POST
         * ============================================================
         */
        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET')
            !==
            'POST'
        ) {

            $_SESSION['error'] =
                'Operação inválida.';

            $this->redirect();

            return;
        }


        /*
         * ============================================================
         * RECUPERAR FORMULÁRIO
         * ============================================================
         */
        $form =
            filter_input_array(
                INPUT_POST,
                FILTER_UNSAFE_RAW
            )
            ?? [];


        /*
         * ============================================================
         * CSRF
         * ============================================================
         */
        if (
            empty(
                $form['csrf_token']
            )
            ||
            !CSRFHelper::validateCSRFToken(
                'form_cancel_direct_expense_'
                . $directExpenseId,
                $form['csrf_token']
            )
        ) {

            $_SESSION['error'] =
                'Token de segurança inválido ou expirado.';

            $this->redirect();

            return;
        }


        /*
         * ============================================================
         * MOTIVO
         * ============================================================
         */
        $cancellationReason =
            trim(
                (string) (
                    $form['cancellation_reason']
                    ?? ''
                )
            );


        if ($cancellationReason === '') {

            $_SESSION['error'] =
                'Informe o motivo do cancelamento.';

            $this->redirect();

            return;
        }


        if (
            mb_strlen(
                $cancellationReason
            ) > 500
        ) {

            $_SESSION['error'] =
                'O motivo do cancelamento deve possuir no máximo 500 caracteres.';

            $this->redirect();

            return;
        }


        /*
         * ============================================================
         * USUÁRIO
         * ============================================================
         */
        $userId =
            (int) (
                $_SESSION['user_id']
                ?? 0
            );


        if ($userId <= 0) {

            $_SESSION['error'] =
                'Usuário responsável pelo cancelamento não identificado.';

            $this->redirect();

            return;
        }


        try {

            $repository =
                new DirectExpensesRepository();


            /*
             * Como getById() agora considera status = 1,
             * uma despesa já cancelada não será encontrada.
             */
            $expense =
                $repository->getById(
                    $directExpenseId
                );


            if ($expense === null) {

                $_SESSION['error'] =
                    'Despesa direta não encontrada ou já cancelada.';

                $this->redirect();

                return;
            }


            $cancelled =
                $repository->cancel(
                    $directExpenseId,
                    $userId,
                    $cancellationReason
                );


            if (!$cancelled) {

                $_SESSION['error'] =
                    'Não foi possível cancelar a despesa direta.';

                $this->redirect();

                return;
            }


            $_SESSION['success'] =
                'Despesa direta cancelada com sucesso.';


            $this->redirect();

        } catch (Throwable $err) {

            $_SESSION['error'] =
                'Erro ao cancelar a despesa direta: '
                . $err->getMessage();


            $this->redirect();
        }
    }


    /**
     * Retornar para a listagem.
     */
    private function redirect(): void
    {
        header(
            'Location: '
            . $_ENV['URL_ADM']
            . 'list-direct-expenses'
        );

        exit;
    }
}