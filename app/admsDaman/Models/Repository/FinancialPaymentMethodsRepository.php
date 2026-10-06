<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;
use PDO;

class FinancialPaymentMethodsRepository extends DbConnection
{
    /**
     * Recuperar formas de pagamento efetivo ativas para select.
     *
     * @return array
     */
    public function getAllActiveSelect(): array
    {
        $sql = "SELECT
                id,
                name
            FROM adms_daman_financial_payment_methods
            WHERE status = 1
            ORDER BY name ASC
        ";

        $stmt =
            $this->getConnection()
            ->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /**
     * Recuperar uma forma de pagamento efetivo pelo ID.
     */
    public function getById(
        int $id
    ): ?array {

        if ($id <= 0) {
            return null;
        }


        $sql = "SELECT
                id,
                name,
                status

            FROM
                adms_daman_financial_payment_methods

            WHERE
                id = :id

            LIMIT 1
        ";


        $stmt =
            $this->getConnection()
            ->prepare($sql);


        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );


        $stmt->execute();


        $paymentMethod =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );


        return
            $paymentMethod !== false
            ? $paymentMethod
            : null;
    }
}
