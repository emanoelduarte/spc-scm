<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Models\Repository\PaymentMethodsRepository;
use Throwable;

class GetPaymentMethodItems
{
    /**
     * Retornar configuração das parcelas
     * de uma condição de pagamento.
     */
    public function index(string|int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {

            $paymentMethodId = (int) $id;

            if ($paymentMethodId <= 0) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'Condição de pagamento inválida.',
                ]);

                return;
            }

            $repository = new PaymentMethodsRepository();

            $items = $repository->getPaymentMethodItems(
                $paymentMethodId
            );

            if (empty($items)) {

                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Nenhuma configuração de parcelas encontrada.',
                    'items' => [],
                ]);

                return;
            }

            echo json_encode([
                'success' => true,
                'items' => $items,
            ]);

        } catch (Throwable $err) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao recuperar configuração das parcelas.',
            ]);
        }
    }
}