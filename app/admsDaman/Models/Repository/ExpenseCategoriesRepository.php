<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;
use PDO;

class ExpenseCategoriesRepository extends DbConnection
{
    /**
     * Recuperar categorias ativas para select.
     *
     * @return array
     */
    public function getAllActiveSelect(): array
    {
        $sql = "
            SELECT
                id,
                name
            FROM adms_daman_expense_categories
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
}
