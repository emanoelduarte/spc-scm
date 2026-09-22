<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;
use PDO;

class PaymentMethodsRepository extends DbConnection
{
    public function getAllPaymentSelect(): array|bool
    {

        $sql = 'SELECT id, name
        FROM adms_daman_payment_methods
        ORDER BY name ASC';

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a Query
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar a configuração das parcelas
     * da condição de pagamento.
     *
     * @param int $paymentMethodId
     * @return array
     */
    public function getPaymentMethodItems(int $paymentMethodId): array
    {
        $sql = 'SELECT
                installment_number,
                days_after_purchase,
                percentage
            FROM adms_daman_payment_method_items
            WHERE adms_daman_payment_method_id = :payment_method_id
            ORDER BY installment_number ASC';

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        // Vincular o ID da condição de pagamento
        $stmt->bindValue(
            ':payment_method_id',
            $paymentMethodId,
            PDO::PARAM_INT
        );

        // Executar a query
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
