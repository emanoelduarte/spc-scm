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
     */
    public function create(array $data): int
    {
        $expenseDate = trim((string) ($data['expense_date'] ?? ''));

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

        $projectId = (int) ($data['adms_daman_project_id'] ?? 0);

        if ($projectId <= 0) {
            throw new InvalidArgumentException(
                'Selecione a obra.'
            );
        }

        $amount = $this->normalizeMoney($data['amount'] ?? '');
        $amountCents = $this->decimalToCents($amount);

        if ($amountCents <= 0) {
            throw new InvalidArgumentException(
                'O valor da despesa deve ser maior que zero.'
            );
        }

        $hasProration = !empty($data['has_proration']);

        $allocations = $this->prepareAllocations(
            $data['allocations'] ?? [],
            $projectId,
            $amount,
            $hasProration
        );

        /*
         * O campo principal continua existindo por compatibilidade.
         * Mesmo quando houver rateio, a obra escolhida no campo
         * principal permanece como a obra de referência do lançamento.
         * O rateio apenas distribui o valor entre as obras participantes.
         */
        $data['adms_daman_project_id'] = $projectId;

        $data['expense_date'] = $expenseDate;
        $data['amount'] = $amount;
        $data['allocations'] = $allocations;

        $data['description'] = trim(
            (string) ($data['description'] ?? '')
        );

        $data['observation'] = trim(
            (string) ($data['observation'] ?? '')
        );

        $repository = new DirectExpensesRepository();

        return $repository->create($data);
    }


    /**
     * Preparar o rateio entre obras.
     *
     * Sem rateio, é criada uma única alocação de 100% para a obra
     * selecionada. Com rateio, exige duas ou mais obras distintas e
     * soma exatamente igual ao valor total da despesa.
     */
    private function prepareAllocations(
        array $rawAllocations,
        int $projectId,
        string $totalAmount,
        bool $hasProration
    ): array {

        if (!$hasProration) {
            return [[
                'adms_daman_project_id' => $projectId,
                'allocated_amount' => $totalAmount,
            ]];
        }

        $prepared = [];
        $projectIds = [];
        $allocatedTotalCents = 0;

        foreach ($rawAllocations as $allocation) {
            $allocationProjectId =
                (int) ($allocation['adms_daman_project_id'] ?? 0);

            if ($allocationProjectId <= 0) {
                throw new InvalidArgumentException(
                    'Selecione uma obra válida em todas as linhas do rateio.'
                );
            }

            if (in_array($allocationProjectId, $projectIds, true)) {
                throw new InvalidArgumentException(
                    'A mesma obra não pode aparecer duas vezes no rateio.'
                );
            }

            $allocatedAmount = $this->normalizeMoney(
                $allocation['allocated_amount'] ?? ''
            );

            $allocatedCents = $this->decimalToCents($allocatedAmount);

            if ($allocatedCents <= 0) {
                throw new InvalidArgumentException(
                    'Informe um valor maior que zero para cada obra do rateio.'
                );
            }

            $projectIds[] = $allocationProjectId;
            $allocatedTotalCents += $allocatedCents;

            $prepared[] = [
                'adms_daman_project_id' => $allocationProjectId,
                'allocated_amount' => $allocatedAmount,
            ];
        }

        if (count($prepared) < 2) {
            throw new InvalidArgumentException(
                'O rateio deve possuir pelo menos duas obras.'
            );
        }

        /*
         * A obra principal informada no cabeçalho precisa participar
         * obrigatoriamente do rateio. Isso evita divergência entre
         * adms_daman_direct_expenses.adms_daman_project_id e as
         * alocações financeiras da despesa.
         */
        if (!in_array($projectId, $projectIds, true)) {
            throw new InvalidArgumentException(
                'A obra principal deve fazer parte do rateio.'
            );
        }

        $totalAmountCents = $this->decimalToCents($totalAmount);

        if ($allocatedTotalCents !== $totalAmountCents) {
            $difference = abs($totalAmountCents - $allocatedTotalCents) / 100;

            throw new InvalidArgumentException(
                'A soma do rateio deve ser igual ao valor da despesa. '
                . 'Diferença: R$ '
                . number_format($difference, 2, ',', '.')
            );
        }

        return $prepared;
    }


    /**
     * Converter valores digitados em pt-BR ou decimal
     * para o formato aceito pelo DECIMAL do MySQL.
     */
    private function normalizeMoney(mixed $value): string
    {
        $value = trim((string) $value);
        $value = str_replace(['R$', ' '], '', $value);

        if ($value === '') {
            throw new InvalidArgumentException(
                'Informe o valor da despesa.'
            );
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                'Informe um valor válido.'
            );
        }

        return number_format((float) $value, 2, '.', '');
    }


    /**
     * Converter decimal SQL em centavos para comparações exatas.
     */
    private function decimalToCents(string $value): int
    {
        return (int) round(((float) $value) * 100);
    }


    /**
     * Validar data no padrão Y-m-d.
     */
    private function isValidDate(string $date): bool
    {
        $dateObject = DateTime::createFromFormat('Y-m-d', $date);

        return
            $dateObject !== false
            && $dateObject->format('Y-m-d') === $date;
    }
}
