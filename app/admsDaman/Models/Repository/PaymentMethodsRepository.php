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
}
