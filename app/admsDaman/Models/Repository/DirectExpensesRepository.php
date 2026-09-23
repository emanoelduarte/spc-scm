<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use PDO;
use PDOException;

class DirectExpensesRepository extends DbConnection
{
    /**
     * Cadastrar uma despesa direta.
     *
     * @param array $data
     * @return int ID do lançamento criado.
     */
    public function create(array $data): int
    {
        try {
            $sql = "
                INSERT INTO adms_daman_direct_expenses
                (
                    adms_daman_project_id,
                    adms_daman_expense_category_id,
                    adms_daman_financial_payment_method_id,
                    expense_date,
                    description,
                    amount,
                    observation,
                    created_by,
                    created_at
                )
                VALUES
                (
                    :adms_daman_project_id,
                    :adms_daman_expense_category_id,
                    :adms_daman_financial_payment_method_id,
                    :expense_date,
                    :description,
                    :amount,
                    :observation,
                    :created_by,
                    NOW()
                )
            ";

            $stmt = $this->getConnection()->prepare($sql);

            $stmt->execute([
                ':adms_daman_project_id' =>
                    (int) $data['adms_daman_project_id'],

                ':adms_daman_expense_category_id' =>
                    (int) $data['adms_daman_expense_category_id'],

                ':adms_daman_financial_payment_method_id' =>
                    (int) $data['adms_daman_financial_payment_method_id'],

                ':expense_date' =>
                    $data['expense_date'],

                ':description' =>
                    trim((string) $data['description']),

                ':amount' =>
                    $data['amount'],

                ':observation' =>
                    $data['observation'] !== ''
                        ? trim((string) $data['observation'])
                        : null,

                ':created_by' =>
                    (int) $data['created_by'],
            ]);

            return (int) $this->getConnection()->lastInsertId();
        } catch (PDOException $err) {
            GenerateLog::generateLog(
                'error',
                'Erro ao cadastrar despesa direta.',
                [
                    'project_id' =>
                        $data['adms_daman_project_id']
                        ?? null,

                    'category_id' =>
                        $data['adms_daman_expense_category_id']
                        ?? null,

                    'amount' =>
                        $data['amount']
                        ?? null,

                    'error' =>
                        $err->getMessage(),
                ]
            );

            throw $err;
        }
    }


    /**
     * Listar despesas diretas paginadas.
     *
     * @param int $page
     * @param int $limitResult
     * @param array $filters
     * @return array
     */
    public function getAll(
        int $page = 1,
        int $limitResult = 10,
        array $filters = []
    ): array {

        $page =
            max(
                1,
                $page
            );

        $limitResult =
            max(
                1,
                $limitResult
            );

        $offset =
            ($page - 1)
            * $limitResult;


        [
            'where' => $where,
            'params' => $params,
        ] = $this->buildFilters(
            $filters
        );


        $sql = "
            SELECT
                direct_expense.id,
                direct_expense.expense_date,
                direct_expense.description,
                direct_expense.amount,
                direct_expense.observation,
                direct_expense.created_at,

                project.id AS project_id,
                project.name AS project_name,

                category.id AS category_id,
                category.name AS category_name,

                payment_method.id AS payment_method_id,
                payment_method.name AS payment_method_name,

                user.name AS created_by_name

            FROM adms_daman_direct_expenses
                AS direct_expense

            INNER JOIN adms_daman_projects
                AS project
                ON project.id =
                    direct_expense.adms_daman_project_id

            INNER JOIN adms_daman_expense_categories
                AS category
                ON category.id =
                    direct_expense.adms_daman_expense_category_id

            INNER JOIN adms_daman_financial_payment_methods
                AS payment_method
                ON payment_method.id =
                    direct_expense.adms_daman_financial_payment_method_id

            INNER JOIN adms_daman_users
                AS user
                ON user.id =
                    direct_expense.created_by

            {$where}

            ORDER BY
                direct_expense.expense_date DESC,
                direct_expense.id DESC

            LIMIT :limit_result
            OFFSET :offset
        ";


        $stmt =
            $this->getConnection()
                ->prepare($sql);


        foreach ($params as $key => $value) {
            $stmt->bindValue(
                ':' . $key,
                $value
            );
        }


        $stmt->bindValue(
            ':limit_result',
            $limitResult,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );


        $stmt->execute();


        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    /**
     * Recuperar a quantidade de despesas conforme os filtros.
     */
    public function getAmount(
        array $filters = []
    ): int {

        [
            'where' => $where,
            'params' => $params,
        ] = $this->buildFilters(
            $filters
        );


        $sql = "
            SELECT
                COUNT(direct_expense.id)

            FROM adms_daman_direct_expenses
                AS direct_expense

            {$where}
        ";


        $stmt =
            $this->getConnection()
                ->prepare($sql);


        foreach ($params as $key => $value) {
            $stmt->bindValue(
                ':' . $key,
                $value
            );
        }


        $stmt->execute();


        return (int) $stmt->fetchColumn();
    }


    /**
     * Recuperar o total desembolsado conforme os filtros.
     */
    public function getTotalAmount(
        array $filters = []
    ): float {

        [
            'where' => $where,
            'params' => $params,
        ] = $this->buildFilters(
            $filters
        );


        $sql = "
            SELECT
                COALESCE(
                    SUM(direct_expense.amount),
                    0
                )

            FROM adms_daman_direct_expenses
                AS direct_expense

            {$where}
        ";


        $stmt =
            $this->getConnection()
                ->prepare($sql);


        foreach ($params as $key => $value) {
            $stmt->bindValue(
                ':' . $key,
                $value
            );
        }


        $stmt->execute();


        return (float) $stmt->fetchColumn();
    }


    /**
     * Montar WHERE comum à listagem, quantidade e total.
     *
     * @return array{where:string,params:array}
     */
    private function buildFilters(
        array $filters
    ): array {

        $conditions = [];
        $params = [];


        if (!empty(
            $filters['project_id']
        )) {
            $conditions[] =
                'direct_expense.adms_daman_project_id = :project_id';

            $params['project_id'] =
                (int) $filters['project_id'];
        }


        if (!empty(
            $filters['category_id']
        )) {
            $conditions[] =
                'direct_expense.adms_daman_expense_category_id = :category_id';

            $params['category_id'] =
                (int) $filters['category_id'];
        }


        if (!empty(
            $filters['payment_method_id']
        )) {
            $conditions[] =
                'direct_expense.adms_daman_financial_payment_method_id = :payment_method_id';

            $params['payment_method_id'] =
                (int) $filters['payment_method_id'];
        }


        if (!empty(
            $filters['date_start']
        )) {
            $conditions[] =
                'direct_expense.expense_date >= :date_start';

            $params['date_start'] =
                $filters['date_start'];
        }


        if (!empty(
            $filters['date_end']
        )) {
            $conditions[] =
                'direct_expense.expense_date <= :date_end';

            $params['date_end'] =
                $filters['date_end'];
        }


        if (!empty(
            $filters['description']
        )) {
            $conditions[] =
                'direct_expense.description LIKE :description';

            $params['description'] =
                '%'
                . trim(
                    (string) $filters['description']
                )
                . '%';
        }


        $where =
            !empty($conditions)
                ? 'WHERE '
                    . implode(
                        ' AND ',
                        $conditions
                    )
                : '';


        return [
            'where' =>
                $where,

            'params' =>
                $params,
        ];
    }
}
