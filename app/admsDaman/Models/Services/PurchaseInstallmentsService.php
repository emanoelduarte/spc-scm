<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Services;

use App\admsDaman\Models\Repository\PaymentMethodsRepository;
use DateTimeImmutable;

class PurchaseInstallmentsService
{
    private PaymentMethodsRepository $paymentMethodsRepository;

    public function __construct()
    {
        $this->paymentMethodsRepository = new PaymentMethodsRepository();
    }

    /**
     * Gerar parcelas conforme a condição de pagamento.
     */
    public function generate(int $paymentMethodId, string $purchaseDate, float $totalValue): array
    {
        $items = $this->paymentMethodsRepository
            ->getPaymentMethodItems($paymentMethodId);

        if (empty($items)) {
            return [];
        }

        $installmentsCount = count($items);

        // Trabalhar em centavos para evitar problemas de arredondamento.
        $totalCents = (int) round($totalValue * 100);

        // Valor base de cada parcela em centavos.
        $baseAmount = intdiv(
            $totalCents,
            $installmentsCount
        );

        // Resto da divisão para ajustar a última parcela.
        $remainder = $totalCents
            - ($baseAmount * $installmentsCount);

        $baseDate = new DateTimeImmutable($purchaseDate);

        $installments = [];

        foreach ($items as $index => $item) {

            $amountCents = $baseAmount;

            // Acrescentar a diferença de centavos na última parcela.
            if ($index === ($installmentsCount - 1)) {
                $amountCents += $remainder;
            }

            // Obter o número de dias após a compra para esta parcela.
            $days = (int) $item['days_after_purchase'];

            // Calcular a data de vencimento da parcela.
            $dueDate = $baseDate->modify(
                "+{$days} days"
            );

            // Adicionar a parcela ao array de parcelas.
            $installments[] = [
                'installment_number' =>
                (int) $item['installment_number'],

                'due_date' =>
                $dueDate->format('Y-m-d'),

                'original_amount' =>
                $amountCents / 100,

                'status' => 'AV',
            ];
        }

        return $installments;
    }
}
