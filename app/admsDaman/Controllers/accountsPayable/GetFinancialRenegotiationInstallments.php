<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Models\Repository\FinancialRenegotiationsRepository;
use Throwable;

/**
 * Endpoint interno utilizado pela tela de renegociação.
 */
class GetFinancialRenegotiationInstallments
{
    public function index(string|int|null $supplierId = null): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $supplierId = (int) $supplierId;

            if ($supplierId <= 0) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => 'Fornecedor inválido.',
                    'installments' => [],
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $repository =
                new FinancialRenegotiationsRepository();

            $installments =
                $repository
                    ->getEligibleInstallmentsBySupplier($supplierId);

            echo json_encode([
                'success' => true,
                'installments' => $installments,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $err) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Não foi possível carregar as parcelas disponíveis.',
                'installments' => [],
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
