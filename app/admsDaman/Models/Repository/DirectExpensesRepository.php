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
        $connection = $this->getConnection();
        $startedTransaction = !$connection->inTransaction();

        try {
            if ($startedTransaction) {
                $connection->beginTransaction();
            }

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

            $stmt = $connection->prepare($sql);

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

            $directExpenseId =
                (int) $connection->lastInsertId();

            if ($directExpenseId <= 0) {
                throw new PDOException(
                    'Não foi possível recuperar o ID da despesa direta.'
                );
            }

            $allocations = $data['allocations'] ?? [];

            if (empty($allocations)) {
                $allocations = [[
                    'adms_daman_project_id' =>
                    (int) $data['adms_daman_project_id'],

                    'allocated_amount' =>
                    (string) $data['amount'],
                ]];
            }

            $allocationSql = "
                INSERT INTO adms_daman_direct_expense_allocations
                (
                    adms_daman_direct_expense_id,
                    adms_daman_project_id,
                    allocated_amount,
                    created_by,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    :direct_expense_id,
                    :project_id,
                    :allocated_amount,
                    :created_by,
                    NOW(),
                    NOW()
                )
            ";

            $allocationStmt =
                $connection->prepare($allocationSql);

            foreach ($allocations as $allocation) {
                $allocationStmt->execute([
                    ':direct_expense_id' =>
                    $directExpenseId,

                    ':project_id' =>
                    (int) $allocation['adms_daman_project_id'],

                    ':allocated_amount' =>
                    (string) $allocation['allocated_amount'],

                    ':created_by' =>
                    (int) $data['created_by'],
                ]);
            }

            if ($startedTransaction) {
                $connection->commit();
            }

            return $directExpenseId;
        } catch (PDOException $err) {
            if (
                $startedTransaction
                && $connection->inTransaction()
            ) {
                $connection->rollBack();
            }

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

                    'allocations' =>
                    $data['allocations']
                        ?? [],

                    'error' =>
                    $err->getMessage(),
                ]
            );

            throw $err;
        }
    }

    /**
     * Recuperar uma despesa direta pelo ID.
     */
    public function getById(
        int $id
    ): ?array {

        if ($id <= 0) {
            return null;
        }


        $sql = "SELECT

                direct_expense.id,

                direct_expense.adms_daman_project_id,

                direct_expense.adms_daman_expense_category_id,

                direct_expense.adms_daman_financial_payment_method_id,

                direct_expense.expense_date,

                direct_expense.description,

                direct_expense.amount,

                direct_expense.observation,

                direct_expense.created_by,

                direct_expense.created_at,

                direct_expense.updated_at,


                project.name AS project_name,

                category.name AS category_name,

                payment_method.name AS payment_method_name,


                user.name AS created_by_name


            FROM
                adms_daman_direct_expenses
                    AS direct_expense


            INNER JOIN
                adms_daman_projects
                    AS project

                ON project.id =
                    direct_expense.adms_daman_project_id


            INNER JOIN
                adms_daman_expense_categories
                    AS category

                ON category.id =
                    direct_expense.adms_daman_expense_category_id


            INNER JOIN
                adms_daman_financial_payment_methods
                    AS payment_method

                ON payment_method.id =
                    direct_expense.adms_daman_financial_payment_method_id


            INNER JOIN
                adms_daman_users
                    AS user

                ON user.id =
                    direct_expense.created_by


            WHERE
                direct_expense.id = :id
                AND direct_expense.status = 1


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


        $expense =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );


        return
            $expense !== false
            ? $expense
            : null;
    }

    /**
     * Recuperar os rateios de uma despesa direta.
     */
    public function getAllocationsByExpenseId(
        int $directExpenseId
    ): array {

        if ($directExpenseId <= 0) {
            return [];
        }


        $sql = "
            SELECT

                allocation.id,

                allocation.adms_daman_direct_expense_id,

                allocation.adms_daman_project_id,

                allocation.allocated_amount,

                allocation.created_by,

                allocation.created_at,

                allocation.updated_at,

                project.name AS project_name


            FROM
                adms_daman_direct_expense_allocations
                    AS allocation


            INNER JOIN
                adms_daman_projects
                    AS project

                ON project.id =
                    allocation.adms_daman_project_id


            WHERE
                allocation.adms_daman_direct_expense_id
                =
                :direct_expense_id


            ORDER BY
                allocation.id ASC
        ";


        $stmt =
            $this->getConnection()
            ->prepare($sql);


        $stmt->bindValue(
            ':direct_expense_id',
            $directExpenseId,
            PDO::PARAM_INT
        );


        $stmt->execute();


        return
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
    }

    /**
     * Atualizar uma despesa direta e seus rateios.
     */
    public function update(
        int $directExpenseId,
        array $data
    ): bool {

        if ($directExpenseId <= 0) {

            throw new PDOException(
                'ID da despesa direta inválido.'
            );
        }


        $connection =
            $this->getConnection();


        $startedTransaction =
            !$connection->inTransaction();


        try {

            if ($startedTransaction) {

                $connection
                    ->beginTransaction();
            }


            /*
            * =====================================================
            * ATUALIZAR DESPESA
            * =====================================================
            */
            $sql = "
                UPDATE
                    adms_daman_direct_expenses

                SET
                    adms_daman_project_id =
                        :adms_daman_project_id,

                    adms_daman_expense_category_id =
                        :adms_daman_expense_category_id,

                    adms_daman_financial_payment_method_id =
                        :adms_daman_financial_payment_method_id,

                    expense_date =
                        :expense_date,

                    description =
                        :description,

                    amount =
                        :amount,

                    observation =
                        :observation,

                    updated_at =
                        NOW()

                WHERE
                    id = :id
            ";


            $stmt =
                $connection
                ->prepare($sql);


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
                trim(
                    (string) $data['description']
                ),

                ':amount' =>
                $data['amount'],

                ':observation' =>
                !empty($data['observation'])
                    ? trim(
                        (string) $data['observation']
                    )
                    : null,

                ':id' =>
                $directExpenseId,
            ]);


            /*
            * =====================================================
            * REMOVER RATEIOS ATUAIS
            * =====================================================
            *
            * Eles serão reconstruídos logo abaixo.
            */
            $deleteAllocationSql = "
                DELETE FROM
                    adms_daman_direct_expense_allocations

                WHERE
                    adms_daman_direct_expense_id
                    =
                    :direct_expense_id
            ";


            $deleteAllocationStmt =
                $connection
                ->prepare(
                    $deleteAllocationSql
                );


            $deleteAllocationStmt->bindValue(
                ':direct_expense_id',
                $directExpenseId,
                PDO::PARAM_INT
            );


            $deleteAllocationStmt->execute();


            /*
            * =====================================================
            * NOVOS RATEIOS
            * =====================================================
            */
            $allocations =
                $data['allocations']
                ?? [];


            /*
            * Segurança adicional.
            *
            * Normalmente o Service já entregará
            * sempre pelo menos uma alocação.
            */
            if (empty($allocations)) {

                $allocations = [[

                    'adms_daman_project_id' =>
                    (int) $data['adms_daman_project_id'],

                    'allocated_amount' =>
                    (string) $data['amount'],
                ]];
            }


            $allocationSql = "
                INSERT INTO
                    adms_daman_direct_expense_allocations
                (
                    adms_daman_direct_expense_id,

                    adms_daman_project_id,

                    allocated_amount,

                    created_by,

                    created_at,

                    updated_at
                )

                VALUES
                (
                    :direct_expense_id,

                    :project_id,

                    :allocated_amount,

                    :created_by,

                    NOW(),

                    NOW()
                )
            ";


            $allocationStmt =
                $connection
                ->prepare(
                    $allocationSql
                );


            foreach (
                $allocations as $allocation
            ) {

                $allocationStmt->execute([

                    ':direct_expense_id' =>
                    $directExpenseId,

                    ':project_id' =>
                    (int) $allocation['adms_daman_project_id'],

                    ':allocated_amount' =>
                    (string) $allocation['allocated_amount'],

                    /*
                 * Como essas linhas estão sendo
                 * reconstruídas agora, registramos
                 * o usuário responsável pela edição.
                 */
                    ':created_by' =>
                    (int) (
                        $data['updated_by']
                        ??
                        $data['created_by']
                        ??
                        0
                    ),
                ]);
            }


            if ($startedTransaction) {

                $connection
                    ->commit();
            }


            return true;
        } catch (PDOException $err) {

            if (
                $startedTransaction
                &&
                $connection
                ->inTransaction()
            ) {

                $connection
                    ->rollBack();
            }


            GenerateLog::generateLog(
                'error',
                'Erro ao atualizar despesa direta.',
                [
                    'direct_expense_id' =>
                    $directExpenseId,

                    'project_id' =>
                    $data['adms_daman_project_id']
                        ?? null,

                    'category_id' =>
                    $data['adms_daman_expense_category_id']
                        ?? null,

                    'amount' =>
                    $data['amount']
                        ?? null,

                    'allocations' =>
                    $data['allocations']
                        ?? [],

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

        $conditions = [
            'direct_expense.status = 1',
        ];

        $params = [];


        if (!empty($filters['project_id'])) {
            $conditions[] =
                'direct_expense.adms_daman_project_id = :project_id';

            $params['project_id'] =
                (int) $filters['project_id'];
        }


        if (!empty($filters['category_id'])) {
            $conditions[] =
                'direct_expense.adms_daman_expense_category_id = :category_id';

            $params['category_id'] =
                (int) $filters['category_id'];
        }


        if (!empty($filters['payment_method_id'])) {
            $conditions[] =
                'direct_expense.adms_daman_financial_payment_method_id = :payment_method_id';

            $params['payment_method_id'] =
                (int) $filters['payment_method_id'];
        }


        if (!empty($filters['date_start'])) {
            $conditions[] =
                'direct_expense.expense_date >= :date_start';

            $params['date_start'] =
                $filters['date_start'];
        }


        if (!empty($filters['date_end'])) {
            $conditions[] =
                'direct_expense.expense_date <= :date_end';

            $params['date_end'] =
                $filters['date_end'];
        }


        if (!empty($filters['description'])) {
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

    /**
     * Cancelar logicamente uma despesa direta.
     */
    public function cancel(
        int $directExpenseId,
        int $cancelledBy,
        ?string $reason = null
    ): bool {

        if (
            $directExpenseId <= 0
            ||
            $cancelledBy <= 0
        ) {
            return false;
        }


        $reason =
            trim(
                (string) $reason
            );


        $sql = "UPDATE
            adms_daman_direct_expenses

        SET
            status = 0,

            cancelled_by =
                :cancelled_by,

            cancelled_at =
                NOW(),

            cancellation_reason =
                :cancellation_reason,

            updated_at =
                NOW()

        WHERE
            id =
                :id

        AND
            status = 1
    ";


        try {

            $stmt =
                $this->getConnection()
                ->prepare(
                    $sql
                );


            $stmt->bindValue(
                ':id',
                $directExpenseId,
                PDO::PARAM_INT
            );


            $stmt->bindValue(
                ':cancelled_by',
                $cancelledBy,
                PDO::PARAM_INT
            );


            if ($reason !== '') {

                $stmt->bindValue(
                    ':cancellation_reason',
                    $reason,
                    PDO::PARAM_STR
                );
            } else {

                $stmt->bindValue(
                    ':cancellation_reason',
                    null,
                    PDO::PARAM_NULL
                );
            }


            $stmt->execute();


            return
                $stmt->rowCount() > 0;
        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao cancelar despesa direta.',
                [
                    'direct_expense_id' =>
                    $directExpenseId,

                    'cancelled_by' =>
                    $cancelledBy,

                    'reason' =>
                    $reason,

                    'error' =>
                    $err->getMessage(),
                ]
            );


            throw $err;
        }
    }
}
