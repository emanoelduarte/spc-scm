<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Services;

use App\admsDaman\Models\Repository\DirectExpensesRepository;
use DateTime;
use InvalidArgumentException;

class DirectExpenseService
{
    /**
     * Criar uma despesa direta já realizada.
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        $expenseDate =
            trim(
                (string) (
                    $data['expense_date']
                    ?? ''
                )
            );

        if (!$this->isValidDate($expenseDate)) {
            throw new InvalidArgumentException(
                'Informe uma data de desembolso válida.'
            );
        }

        if ($expenseDate > date('Y-m-d')) {
            throw new InvalidArgumentException(
                'A data do desembolso não pode ser futura.'
            );
        }

        $amount =
            $this->normalizeMoney(
                $data['amount']
                ?? ''
            );

        if ((float) $amount <= 0) {
            throw new InvalidArgumentException(
                'O valor da despesa deve ser maior que zero.'
            );
        }

        $data['expense_date'] =
            $expenseDate;

        $data['amount'] =
            $amount;

        $data['description'] =
            trim(
                (string) (
                    $data['description']
                    ?? ''
                )
            );

        $data['observation'] =
            trim(
                (string) (
                    $data['observation']
                    ?? ''
                )
            );

        $repository =
            new DirectExpensesRepository();

        return
            $repository->create(
                $data
            );
    }


    /**
     * Converter valores digitados em pt-BR ou decimal
     * para o formato aceito pelo DECIMAL do MySQL.
     *
     * Exemplos:
     * 8.450,00 -> 8450.00
     * 8450,00  -> 8450.00
     * 8450.00  -> 8450.00
     */
    private function normalizeMoney(
        mixed $value
    ): string {

        $value =
            trim(
                (string) $value
            );

        $value =
            str_replace(
                ['R$', ' '],
                '',
                $value
            );

        if ($value === '') {
            throw new InvalidArgumentException(
                'Informe o valor da despesa.'
            );
        }

        if (
            str_contains($value, ',')
            &&
            str_contains($value, '.')
        ) {
            $value =
                str_replace(
                    '.',
                    '',
                    $value
                );

            $value =
                str_replace(
                    ',',
                    '.',
                    $value
                );
        } elseif (
            str_contains(
                $value,
                ','
            )
        ) {
            $value =
                str_replace(
                    ',',
                    '.',
                    $value
                );
        }

        if (
            !is_numeric($value)
        ) {
            throw new InvalidArgumentException(
                'Informe um valor válido.'
            );
        }

        return number_format(
            (float) $value,
            2,
            '.',
            ''
        );
    }


    /**
     * Validar data no padrão Y-m-d.
     */
    private function isValidDate(
        string $date
    ): bool {

        $dateObject =
            DateTime::createFromFormat(
                'Y-m-d',
                $date
            );

        return
            $dateObject !== false
            &&
            $dateObject->format(
                'Y-m-d'
            ) === $date;
    }
}
