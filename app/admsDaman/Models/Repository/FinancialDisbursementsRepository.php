<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use PDO;
use PDOException;

class FinancialDisbursementsRepository extends DbConnection
{
    /**
     * Listar o desembolso real consolidado.
     *
     * Fontes:
     * - pagamentos ativos de parcelas de compras;
     * - pagamentos ativos de obrigações financeiras;
     * - despesas diretas.
     *
     * Compras rateadas entre obras são apropriadas proporcionalmente
     * pelo valor registrado em adms_daman_purchase_document_allocations.
     *
     * Lançamentos antigos sem rateio utilizam, como fallback,
     * adms_daman_purchase_documents.adms_daman_project_id.
     */
    public function getAll(
        int $page = 1,
        int $limitResult = 20,
        array $filters = []
    ): array {

        $page = max(1, $page);
        $limitResult = max(1, $limitResult);
        $offset = ($page - 1) * $limitResult;

        [
            'where' => $where,
            'params' => $params,
        ] = $this->buildFilters($filters);

        $baseQuery = $this->getBaseQuery();

        $sql = "
            SELECT
                entry.*
            FROM (
                {$baseQuery}
            ) AS entry

            {$where}

            ORDER BY
                entry.event_date DESC,
                entry.source_sort_id DESC,
                entry.origin ASC

            LIMIT :limit_result
            OFFSET :offset
        ";

        try {
            $stmt = $this->getConnection()->prepare($sql);

            $this->bindParams($stmt, $params);

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

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            GenerateLog::generateLog(
                'error',
                'Erro ao listar desembolsos financeiros consolidados.',
                [
                    'filters' => $filters,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }


    /**
     * Quantidade de linhas apropriadas conforme os filtros.
     *
     * Uma compra rateada pode gerar uma linha para cada obra,
     * pois o relatório é orientado ao desembolso por obra.
     */
    public function getAmount(
        array $filters = []
    ): int {

        [
            'where' => $where,
            'params' => $params,
        ] = $this->buildFilters($filters);

        $baseQuery = $this->getBaseQuery();

        $sql = "
            SELECT
                COUNT(*)
            FROM (
                {$baseQuery}
            ) AS entry

            {$where}
        ";

        try {
            $stmt = $this->getConnection()->prepare($sql);

            $this->bindParams($stmt, $params);

            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            GenerateLog::generateLog(
                'error',
                'Erro ao contar desembolsos financeiros consolidados.',
                [
                    'filters' => $filters,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }


    /**
     * Recuperar os totais da visão consolidada.
     */
    public function getSummary(
        array $filters = []
    ): array {

        [
            'where' => $where,
            'params' => $params,
        ] = $this->buildFilters($filters);

        $baseQuery = $this->getBaseQuery();

        $sql = "
            SELECT
                COALESCE(
                    SUM(entry.amount),
                    0
                ) AS total_amount,

                COALESCE(
                    SUM(
                        CASE
                            WHEN entry.origin IN (
                                    'purchase',
                                    'financial_obligation'
                                )
                                THEN entry.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS purchase_amount,

                COALESCE(
                    SUM(
                        CASE
                            WHEN entry.origin = 'direct'
                                THEN entry.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS direct_amount

            FROM (
                {$baseQuery}
            ) AS entry

            {$where}
        ";

        try {
            $stmt = $this->getConnection()->prepare($sql);

            $this->bindParams($stmt, $params);

            $stmt->execute();

            $summary =
                $stmt->fetch(PDO::FETCH_ASSOC)
                ?: [];

            return [
                'total_amount' =>
                    (float) (
                        $summary['total_amount']
                        ?? 0
                    ),

                'purchase_amount' =>
                    (float) (
                        $summary['purchase_amount']
                        ?? 0
                    ),

                'direct_amount' =>
                    (float) (
                        $summary['direct_amount']
                        ?? 0
                    ),
            ];
        } catch (PDOException $e) {
            GenerateLog::generateLog(
                'error',
                'Erro ao calcular resumo de desembolsos financeiros.',
                [
                    'filters' => $filters,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }



    /**
     * Recuperar composição do desembolso por categoria.
     *
     * Compras são apresentadas como "Compra".
     * Obrigações financeiras são apresentadas como "Obrigação Financeira".
     * Despesas diretas preservam suas categorias cadastradas.
     */
    public function getCategoryBreakdown(
        array $filters = [],
        int $limit = 8
    ): array {

        return $this->getGroupedBreakdown(
            $filters,
            'entry.category_key',
            'entry.category_name',
            $limit
        );
    }


    /**
     * Recuperar composição por forma de pagamento.
     */
    public function getPaymentMethodBreakdown(
        array $filters = [],
        int $limit = 8
    ): array {

        return $this->getGroupedBreakdown(
            $filters,
            'COALESCE(entry.payment_method_id, 0)',
            "COALESCE(entry.payment_method_name, 'Não informado')",
            $limit
        );
    }


    /**
     * Recuperar as obras com maior desembolso dentro dos filtros atuais.
     */
    public function getProjectBreakdown(
        array $filters = [],
        int $limit = 5
    ): array {

        return $this->getGroupedBreakdown(
            $filters,
            'entry.project_id',
            "COALESCE(entry.project_name, 'Obra não informada')",
            $limit
        );
    }


    /**
     * Recuperar a evolução mensal dos desembolsos.
     *
     * São retornados, no máximo, os 12 meses mais recentes
     * encontrados dentro dos filtros atuais.
     */
    public function getMonthlyTrend(
        array $filters = [],
        int $limit = 12
    ): array {

        [
            'where' => $where,
            'params' => $params,
        ] = $this->buildFilters(
            $filters
        );


        $baseQuery =
            $this->getBaseQuery();


        $limit =
            max(
                1,
                min(
                    24,
                    $limit
                )
            );


        $sql = "
            SELECT
                DATE_FORMAT(
                    entry.event_date,
                    '%Y-%m'
                ) AS month_key,

                COALESCE(
                    SUM(entry.amount),
                    0
                ) AS total_amount,

                COALESCE(
                    SUM(
                        CASE
                            WHEN entry.origin IN (
                                    'purchase',
                                    'financial_obligation'
                                )
                                THEN entry.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS purchase_amount,

                COALESCE(
                    SUM(
                        CASE
                            WHEN entry.origin = 'direct'
                                THEN entry.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS direct_amount

            FROM (
                {$baseQuery}
            ) AS entry

            {$where}

            GROUP BY
                DATE_FORMAT(
                    entry.event_date,
                    '%Y-%m'
                )

            ORDER BY
                month_key DESC

            LIMIT {$limit}
        ";


        try {

            $stmt =
                $this->getConnection()
                    ->prepare(
                        $sql
                    );


            $this->bindParams(
                $stmt,
                $params
            );


            $stmt->execute();


            $rows =
                $stmt->fetchAll(
                    PDO::FETCH_ASSOC
                );


            /*
             * Exibir cronologicamente:
             * mês mais antigo -> mês mais recente.
             */
            return array_reverse(
                $rows
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar evolução mensal dos desembolsos.',
                [
                    'filters' =>
                        $filters,

                    'error' =>
                        $e->getMessage(),
                ]
            );


            throw $e;
        }
    }


    /**
     * Consulta genérica de composição/ranking financeiro.
     *
     * @param array $filters
     * @param string $groupExpression Expressão SQL controlada internamente.
     * @param string $labelExpression Expressão SQL controlada internamente.
     * @param int $limit
     *
     * @return array
     */
    private function getGroupedBreakdown(
        array $filters,
        string $groupExpression,
        string $labelExpression,
        int $limit
    ): array {

        [
            'where' => $where,
            'params' => $params,
        ] = $this->buildFilters(
            $filters
        );


        $baseQuery =
            $this->getBaseQuery();


        $limit =
            max(
                1,
                min(
                    20,
                    $limit
                )
            );


        $sql = "
            SELECT
                {$groupExpression}
                    AS group_key,

                {$labelExpression}
                    AS label,

                COALESCE(
                    SUM(entry.amount),
                    0
                ) AS total_amount

            FROM (
                {$baseQuery}
            ) AS entry

            {$where}

            GROUP BY
                {$groupExpression},
                {$labelExpression}

            HAVING
                total_amount > 0

            ORDER BY
                total_amount DESC,
                label ASC

            LIMIT {$limit}
        ";


        try {

            $stmt =
                $this->getConnection()
                    ->prepare(
                        $sql
                    );


            $this->bindParams(
                $stmt,
                $params
            );


            $stmt->execute();


            return $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar composição dos desembolsos.',
                [
                    'filters' =>
                        $filters,

                    'group_expression' =>
                        $groupExpression,

                    'error' =>
                        $e->getMessage(),
                ]
            );


            throw $e;
        }
    }


    /**
     * Consulta base comum à listagem, totalizadores e contagem.
     *
     * IMPORTANTE:
     * total_paid representa o desembolso efetivo da compra:
     *
     * principal + juros + multa - desconto.
     *
     * Quando uma compra possui rateio, esse valor pago é distribuído
     * na mesma proporção do valor do documento apropriado para cada obra.
     */
    private function getBaseQuery(): string
    {
        return "
            /*
             * =====================================================
             * PAGAMENTOS DE COMPRAS E OBRIGAÇÕES FINANCEIRAS
             * =====================================================
             */
            SELECT
                CONCAT(
                    'purchase:',
                    payment.id,
                    ':',
                    COALESCE(allocation.id, 0)
                ) AS row_key,

                payment.id AS source_sort_id,

                payment.payment_date AS event_date,

                COALESCE(
                    allocation.adms_daman_project_id,
                    purchase_document.adms_daman_project_id
                ) AS project_id,

                project.name AS project_name,

                CASE
                    WHEN COALESCE(
                        purchase_document.financial_entry_type,
                        'purchase'
                    ) = 'financial_obligation'
                        THEN 'financial_obligation'
                    ELSE 'purchase'
                END AS origin,

                CASE
                    WHEN COALESCE(
                        purchase_document.financial_entry_type,
                        'purchase'
                    ) = 'financial_obligation'
                        THEN 'Obrigação Financeira'
                    ELSE 'Compra'
                END AS origin_label,

                CASE
                    WHEN COALESCE(
                        purchase_document.financial_entry_type,
                        'purchase'
                    ) = 'financial_obligation'
                        THEN 'financial_obligation'
                    ELSE 'purchase'
                END AS category_key,

                NULL AS category_id,

                CASE
                    WHEN COALESCE(
                        purchase_document.financial_entry_type,
                        'purchase'
                    ) = 'financial_obligation'
                        THEN 'Obrigação Financeira'
                    ELSE 'Compra'
                END AS category_name,

                payment.adms_daman_financial_payment_method_id
                    AS payment_method_id,

                COALESCE(
                    financial_payment_method.name,
                    'Não informado'
                ) AS payment_method_name,

                CASE
                    WHEN COALESCE(
                        purchase_document.financial_entry_type,
                        'purchase'
                    ) = 'financial_obligation'
                        THEN 'Obrigação Financeira'
                    WHEN purchase_document.adms_daman_nfe_id IS NOT NULL
                        THEN 'NF-e'
                    ELSE COALESCE(
                        purchase_document.document_type,
                        'Documento'
                    )
                END AS document_type,

                COALESCE(
                    nfe.nfe_number,
                    purchase_document.document_number
                ) AS document_number,

                COALESCE(
                    nfe.issuer_name,
                    supplier.legal_name,
                    'Fornecedor não informado'
                ) AS counterparty,

                CONCAT(
                    'Pagamento da ',
                    purchase_installment.installment_number,
                    'ª parcela'
                ) AS description,

                payment.observation AS observation,

                purchase_document.id AS purchase_document_id,
                payment.id AS purchase_payment_id,
                NULL AS direct_expense_id,

                purchase_installment.installment_number,

                payment.principal_amount,
                payment.interest_amount,
                payment.penalty_amount,
                payment.discount_amount,
                payment.total_paid AS source_total_paid,

                CASE
                    WHEN
                        COALESCE(
                            nfe.total_value,
                            purchase_document.total_value,
                            0
                        ) > 0
                    THEN
                        payment.total_paid
                        *
                        (
                            COALESCE(
                                allocation.allocated_amount,
                                COALESCE(
                                    nfe.total_value,
                                    purchase_document.total_value,
                                    0
                                )
                            )
                            /
                            NULLIF(
                                COALESCE(
                                    nfe.total_value,
                                    purchase_document.total_value,
                                    0
                                ),
                                0
                            )
                        )
                    ELSE 0
                END AS amount

            FROM
                adms_daman_purchase_installment_payments
                    AS payment

            INNER JOIN
                adms_daman_purchase_installments
                    AS purchase_installment
                ON purchase_installment.id =
                    payment.adms_daman_purchase_installment_id

            INNER JOIN
                adms_daman_purchase_documents
                    AS purchase_document
                ON purchase_document.id =
                    purchase_installment.adms_daman_purchase_document_id

            LEFT JOIN
                adms_daman_purchase_document_allocations
                    AS allocation
                ON allocation.adms_daman_purchase_document_id =
                    purchase_document.id

            LEFT JOIN
                adms_daman_projects
                    AS project
                ON project.id =
                    COALESCE(
                        allocation.adms_daman_project_id,
                        purchase_document.adms_daman_project_id
                    )

            LEFT JOIN
                adms_daman_nfes
                    AS nfe
                ON nfe.id =
                    purchase_document.adms_daman_nfe_id

            LEFT JOIN
                adms_daman_suppliers
                    AS supplier
                ON supplier.id =
                    purchase_document.adms_daman_supplier_id

            LEFT JOIN
                adms_daman_financial_payment_methods
                    AS financial_payment_method
                ON financial_payment_method.id =
                    payment.adms_daman_financial_payment_method_id

            WHERE
                payment.status = 'active'


            UNION ALL


            /*
             * =====================================================
             * DESPESAS DIRETAS
             * =====================================================
             */
            SELECT
                CONCAT(
                    'direct:',
                    direct_expense.id,
                    ':',
                    COALESCE(allocation.id, 0)
                ) AS row_key,

                direct_expense.id AS source_sort_id,

                direct_expense.expense_date AS event_date,

                COALESCE(
                    allocation.adms_daman_project_id,
                    direct_expense.adms_daman_project_id
                ) AS project_id,

                project.name AS project_name,

                'direct' AS origin,
                'Despesa Direta' AS origin_label,

                CONCAT(
                    'direct:',
                    category.id
                ) AS category_key,

                category.id AS category_id,
                category.name AS category_name,

                direct_expense.adms_daman_financial_payment_method_id
                    AS payment_method_id,

                financial_payment_method.name
                    AS payment_method_name,

                'Despesa Direta' AS document_type,
                NULL AS document_number,
                NULL AS counterparty,

                direct_expense.description,
                direct_expense.observation,

                NULL AS purchase_document_id,
                NULL AS purchase_payment_id,
                direct_expense.id AS direct_expense_id,

                NULL AS installment_number,

                NULL AS principal_amount,
                NULL AS interest_amount,
                NULL AS penalty_amount,
                NULL AS discount_amount,
                direct_expense.amount AS source_total_paid,

                COALESCE(
                    allocation.allocated_amount,
                    direct_expense.amount
                ) AS amount

            FROM
                adms_daman_direct_expenses
                    AS direct_expense

            LEFT JOIN
                adms_daman_direct_expense_allocations
                    AS allocation
                ON allocation.adms_daman_direct_expense_id =
                    direct_expense.id

            INNER JOIN
                adms_daman_projects
                    AS project
                ON project.id =
                    COALESCE(
                        allocation.adms_daman_project_id,
                        direct_expense.adms_daman_project_id
                    )

            INNER JOIN
                adms_daman_expense_categories
                    AS category
                ON category.id =
                    direct_expense.adms_daman_expense_category_id

            INNER JOIN
                adms_daman_financial_payment_methods
                    AS financial_payment_method
                ON financial_payment_method.id =
                    direct_expense.adms_daman_financial_payment_method_id
        ";
    }


    /**
     * Montar filtros comuns às duas fontes.
     *
     * @return array{where:string,params:array}
     */
    private function buildFilters(
        array $filters
    ): array {

        $conditions = [];
        $params = [];


        if (!empty($filters['project_id'])) {
            $conditions[] =
                'entry.project_id = :project_id';

            $params['project_id'] =
                (int) $filters['project_id'];
        }


        if (!empty($filters['origin'])) {
            $conditions[] =
                'entry.origin = :origin';

            $params['origin'] =
                $filters['origin'];
        }


        if (!empty($filters['category_key'])) {
            $conditions[] =
                'entry.category_key = :category_key';

            $params['category_key'] =
                $filters['category_key'];
        }


        if (!empty($filters['payment_method_id'])) {
            $conditions[] =
                'entry.payment_method_id = :payment_method_id';

            $params['payment_method_id'] =
                (int) $filters['payment_method_id'];
        }


        if (!empty($filters['date_start'])) {
            $conditions[] =
                'entry.event_date >= :date_start';

            $params['date_start'] =
                $filters['date_start'];
        }


        if (!empty($filters['date_end'])) {
            $conditions[] =
                'entry.event_date <= :date_end';

            $params['date_end'] =
                $filters['date_end'];
        }


        if (!empty($filters['search'])) {
            $conditions[] = "
                (
                    entry.description LIKE :search
                    OR entry.document_number LIKE :search
                    OR entry.counterparty LIKE :search
                    OR entry.observation LIKE :search
                )
            ";

            $params['search'] =
                '%'
                . trim(
                    (string) $filters['search']
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
            'where' => $where,
            'params' => $params,
        ];
    }


    /**
     * Vincular parâmetros preservando inteiros quando aplicável.
     */
    private function bindParams(
        \PDOStatement $stmt,
        array $params
    ): void {

        foreach ($params as $key => $value) {
            $stmt->bindValue(
                ':' . $key,
                $value,
                is_int($value)
                    ? PDO::PARAM_INT
                    : PDO::PARAM_STR
            );
        }
    }
}
