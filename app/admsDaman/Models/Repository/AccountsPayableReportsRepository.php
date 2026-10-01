<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use PDO;
use PDOException;

class AccountsPayableReportsRepository extends DbConnection
{
    /**
     * ============================================================
     * VALOR DEVIDO POR OBRA
     * ============================================================
     *
     * Regras:
     *
     * - considera somente parcelas ainda não quitadas;
     * - saldo = valor original - principal já pago;
     * - pagamentos estornados não reduzem o saldo;
     * - respeita o rateio do documento entre obras;
     * - pode filtrar por período de vencimento;
     * - sem período, retorna todo o saldo em aberto;
     * - AP sem vencimento entra quando não existe filtro de período;
     * - documentos com parcelamento pendente são tratados
     *   separadamente.
     */
    public function getOpenAmountByProject(
        ?array $filters = []
    ): array {

        try {

            $conditions = [
                "pi.status <> 'OK'"
            ];

            $params = [];


            /*
         * =====================================================
         * PERÍODO INICIAL
         * =====================================================
         */
            if (!empty($filters['due_date_start'])) {

                $conditions[] =
                    'pi.due_date >= :due_date_start';

                $params['due_date_start'] =
                    $filters['due_date_start'];
            }


            /*
         * =====================================================
         * PERÍODO FINAL
         * =====================================================
         */
            if (!empty($filters['due_date_end'])) {

                $conditions[] =
                    'pi.due_date <= :due_date_end';

                $params['due_date_end'] =
                    $filters['due_date_end'];
            }


            /*
         * =====================================================
         * OBRA
         * =====================================================
         *
         * Rateio existente:
         * allocation.adms_daman_project_id
         *
         * Sem rateio:
         * pd.adms_daman_project_id
         */
            if (!empty($filters['project_id'])) {

                $conditions[] = "
                COALESCE(
                    allocation.adms_daman_project_id,
                    pd.adms_daman_project_id
                ) = :project_id
            ";

                $params['project_id'] =
                    (int) $filters['project_id'];
            }

            /*
 * =====================================================
 * FORNECEDOR
 * =====================================================
 */
            if (!empty($filters['supplier_key'])) {

                $supplierKey =
                    (string) $filters['supplier_key'];


                /*
     * Fornecedor identificado por CNPJ.
     */
                if (
                    str_starts_with(
                        $supplierKey,
                        'tax:'
                    )
                ) {

                    $taxId =
                        preg_replace(
                            '/\D/',
                            '',
                            substr(
                                $supplierKey,
                                4
                            )
                        );


                    if ($taxId !== '') {

                        $conditions[] = "
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                COALESCE(
                                    nfe.issuer_cnpj,
                                    supplier.cnpj,
                                    ''
                                ),
                                '.',
                                ''
                            ),
                            '/',
                            ''
                        ),
                        '-',
                        ''
                    ),
                    ' ',
                    ''
                ) = :supplier_tax_id
            ";

                        $params['supplier_tax_id'] =
                            $taxId;
                    }
                }


                /*
     * Fornecedor cadastrado sem CNPJ.
     */ elseif (
                    str_starts_with(
                        $supplierKey,
                        'supplier:'
                    )
                ) {

                    $supplierId =
                        (int) substr(
                            $supplierKey,
                            9
                        );


                    if ($supplierId > 0) {

                        $conditions[] =
                            'pd.adms_daman_supplier_id = :supplier_id';

                        $params['supplier_id'] =
                            $supplierId;
                    }
                }
            }


            $where =
                'WHERE '
                . implode(
                    ' AND ',
                    $conditions
                );


            /*
         * =====================================================
         * SALDO DA PARCELA
         * =====================================================
         */
            $installmentOpenAmount = "
            GREATEST(
                pi.original_amount
                -
                COALESCE(
                    payments.principal_paid,
                    0
                ),
                0
            )
        ";


            /*
         * =====================================================
         * VALOR DO SALDO PARA A OBRA
         * =====================================================
         *
         * Havendo rateio:
         *
         * saldo × percentual daquela obra.
         *
         * Sem rateio:
         *
         * 100% do saldo pertence à obra original
         * do lançamento.
         */
            $projectOpenAmount = "
            CASE

                WHEN allocation.id IS NOT NULL
                THEN

                    {$installmentOpenAmount}
                    *
                    (
                        allocation.allocated_amount
                        /
                        NULLIF(
                            COALESCE(
                                nfe.total_value,
                                pd.total_value,
                                0
                            ),
                            0
                        )
                    )

                ELSE

                    {$installmentOpenAmount}

            END
        ";


            $sql = "
            SELECT

                COALESCE(
                    allocation.adms_daman_project_id,
                    pd.adms_daman_project_id
                ) AS project_id,

                project.name AS project_name,

                COUNT(
                    DISTINCT pd.id
                ) AS documents_count,

                COUNT(
                    DISTINCT pi.id
                ) AS installments_count,

                COALESCE(
                    ROUND(
                        SUM(
                            {$projectOpenAmount}
                        ),
                        2
                    ),
                    0
                ) AS open_amount


            FROM
                adms_daman_purchase_installments pi


            INNER JOIN
                adms_daman_purchase_documents pd

                ON pd.id =
                    pi.adms_daman_purchase_document_id


            /*
             * LEFT JOIN é proposital.
             *
             * Documentos antigos podem ainda não possuir
             * registros na tabela de rateio.
             */
            LEFT JOIN
                adms_daman_purchase_document_allocations
                    allocation

                ON allocation
                    .adms_daman_purchase_document_id
                    = pd.id


            /*
             * Recuperar a obra do rateio ou,
             * na ausência dele, a obra original
             * do lançamento.
             */
            INNER JOIN
                adms_daman_projects project

                ON project.id =
                    COALESCE(
                        allocation.adms_daman_project_id,
                        pd.adms_daman_project_id
                    )


            LEFT JOIN
                adms_daman_nfes nfe

                ON nfe.id =
                    pd.adms_daman_nfe_id
            
            LEFT JOIN
                adms_daman_suppliers supplier

                ON supplier.id =
                    pd.adms_daman_supplier_id


            /*
             * Somar somente pagamentos válidos.
             */
            LEFT JOIN (

                SELECT

                    adms_daman_purchase_installment_id,

                    SUM(
                        principal_amount
                    ) AS principal_paid

                FROM
                    adms_daman_purchase_installment_payments

                WHERE
                    status = 'active'

                GROUP BY
                    adms_daman_purchase_installment_id

            ) payments

                ON payments
                    .adms_daman_purchase_installment_id
                    = pi.id


            {$where}


            GROUP BY

                COALESCE(
                    allocation.adms_daman_project_id,
                    pd.adms_daman_project_id
                ),

                project.name


            HAVING

                open_amount > 0


            ORDER BY

                project.name ASC
        ";


            $stmt =
                $this->getConnection()
                ->prepare(
                    $sql
                );


            foreach (
                $params as $key => $value
            ) {

                $stmt->bindValue(

                    ':' . $key,

                    $value,

                    is_int($value)
                        ? PDO::PARAM_INT
                        : PDO::PARAM_STR
                );
            }


            $stmt->execute();


            $projects =
                $stmt->fetchAll(
                    PDO::FETCH_ASSOC
                );


            /*
         * Lançamentos sem parcelas confirmadas
         * só entram quando não existe período.
         */
            if (
                empty($filters['due_date_start'])
                &&
                empty($filters['due_date_end'])
            ) {

                $projects =
                    $this->appendPendingSchedulesByProject(
                        $projects,
                        $filters
                    );
            }


            return $projects;
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao gerar relatório de valores devidos por obra.',
                [
                    'error' =>
                    $e->getMessage(),

                    'filters' =>
                    $filters,
                ]
            );


            throw $e;
        }
    }


    /**
     * Adicionar lançamentos que ainda estão
     * aguardando confirmação das parcelas.
     *
     * Estes valores somente entram quando NÃO
     * existe filtro por período de vencimento.
     */
    private function appendPendingSchedulesByProject(
        array $projects,
        ?array $filters = []
    ): array {

        $conditions = [
            "pd.payment_schedule_status = 'pending'"
        ];

        $params = [];


        if (!empty($filters['project_id'])) {

            $conditions[] = "
                COALESCE(
                    allocation.adms_daman_project_id,
                    pd.adms_daman_project_id
                ) = :project_id
            ";

            $params['project_id'] =
                (int) $filters['project_id'];
        }

        /*
 * =====================================================
 * FORNECEDOR
 * =====================================================
 */
        if (!empty($filters['supplier_key'])) {

            $supplierKey =
                (string) $filters['supplier_key'];


            /*
     * Fornecedor identificado por CNPJ.
     */
            if (
                str_starts_with(
                    $supplierKey,
                    'tax:'
                )
            ) {

                $taxId =
                    preg_replace(
                        '/\D/',
                        '',
                        substr(
                            $supplierKey,
                            4
                        )
                    );


                if ($taxId !== '') {

                    $conditions[] = "
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                COALESCE(
                                    nfe.issuer_cnpj,
                                    supplier.cnpj,
                                    ''
                                ),
                                '.',
                                ''
                            ),
                            '/',
                            ''
                        ),
                        '-',
                        ''
                    ),
                    ' ',
                    ''
                ) = :supplier_tax_id
            ";

                    $params['supplier_tax_id'] =
                        $taxId;
                }
            }


            /*
     * Fornecedor cadastrado sem CNPJ.
     */ elseif (
                str_starts_with(
                    $supplierKey,
                    'supplier:'
                )
            ) {

                $supplierId =
                    (int) substr(
                        $supplierKey,
                        9
                    );


                if ($supplierId > 0) {

                    $conditions[] =
                        'pd.adms_daman_supplier_id = :supplier_id';

                    $params['supplier_id'] =
                        $supplierId;
                }
            }
        }


        $where =
            'WHERE '
            . implode(
                ' AND ',
                $conditions
            );


        $sql = "
            SELECT

                COALESCE(
                    allocation.adms_daman_project_id,
                    pd.adms_daman_project_id
                ) AS project_id,

                project.name AS project_name,

                COUNT(
                    DISTINCT pd.id
                ) AS documents_count,

                COALESCE(
                    ROUND(
                        SUM(
                            CASE

                                WHEN allocation.id IS NOT NULL
                                    THEN allocation.allocated_amount

                                ELSE COALESCE(
                                    nfe.total_value,
                                    pd.total_value,
                                    0
                                )

                            END
                        ),
                        2
                    ),
                    0
                ) AS open_amount


            FROM
                adms_daman_purchase_documents pd


            LEFT JOIN
                adms_daman_purchase_document_allocations
                    allocation

                ON allocation
                    .adms_daman_purchase_document_id
                    = pd.id


            INNER JOIN
                adms_daman_projects project

                ON project.id =
                    COALESCE(
                        allocation.adms_daman_project_id,
                        pd.adms_daman_project_id
                    )


            LEFT JOIN
                adms_daman_nfes nfe

                ON nfe.id =
                    pd.adms_daman_nfe_id

            LEFT JOIN
                adms_daman_suppliers supplier

                ON supplier.id =
                    pd.adms_daman_supplier_id


            {$where}


            GROUP BY

                COALESCE(
                    allocation.adms_daman_project_id,
                    pd.adms_daman_project_id
                ),

                project.name


            ORDER BY

                project.name ASC
        ";


        $stmt =
            $this->getConnection()
            ->prepare(
                $sql
            );


        foreach (
            $params as $key => $value
        ) {

            $stmt->bindValue(
                ':' . $key,
                $value,
                PDO::PARAM_INT
            );
        }


        $stmt->execute();


        $pendingProjects =
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );


        /*
         * Indexar o relatório principal por obra.
         */
        $indexed = [];


        foreach ($projects as $project) {

            $projectId =
                (int) $project['project_id'];

            $indexed[$projectId] =
                $project;
        }


        /*
         * Somar os documentos ainda sem parcelas.
         */
        foreach (
            $pendingProjects as $pending
        ) {

            $projectId =
                (int) $pending['project_id'];


            if (!isset($indexed[$projectId])) {

                $indexed[$projectId] = [

                    'project_id' =>
                    $projectId,

                    'project_name' =>
                    $pending['project_name'],

                    'documents_count' =>
                    0,

                    'installments_count' =>
                    0,

                    'open_amount' =>
                    0,
                ];
            }


            $indexed[$projectId]['documents_count'] =
                (int)
                $indexed[$projectId]['documents_count']
                +
                (int)
                $pending['documents_count'];


            $indexed[$projectId]['open_amount'] =
                round(
                    (float)
                    $indexed[$projectId]['open_amount']
                        +
                        (float)
                        $pending['open_amount'],
                    2
                );
        }


        /*
         * Voltar para array sequencial.
         */
        $projects =
            array_values(
                $indexed
            );


        usort(
            $projects,
            static fn(
                array $a,
                array $b
            ): int =>
            strcasecmp(
                (string) $a['project_name'],
                (string) $b['project_name']
            )
        );


        return $projects;
    }

    /**
     * ============================================================
     * VALOR DEVIDO POR FORNECEDOR
     * ============================================================
     *
     * - saldo = valor original - principal já pago;
     * - considera somente pagamentos ativos;
     * - parcelas OK não representam saldo devido;
     * - AP continua sendo obrigação em aberto;
     * - quando houver período, filtra pelo vencimento;
     * - fornecedores são agrupados por CNPJ sempre que possível;
     * - sem CNPJ, utiliza o ID do fornecedor cadastrado.
     */
    public function getOpenAmountBySupplier(
        ?array $filters = []
    ): array {

        try {

            $conditions = [
                "pi.status <> 'OK'"
            ];

            $params = [];

            $projectAllocationJoin = '';

            $hasProjectFilter =
                !empty($filters['project_id']);


            /*
            * =====================================================
            * PERÍODO
            * =====================================================
            */
            if (!empty($filters['due_date_start'])) {

                $conditions[] =
                    'pi.due_date >= :due_date_start';

                $params['due_date_start'] =
                    $filters['due_date_start'];
            }


            if (!empty($filters['due_date_end'])) {

                $conditions[] =
                    'pi.due_date <= :due_date_end';

                $params['due_date_end'] =
                    $filters['due_date_end'];
            }


            /*
            * =====================================================
            * OBRA
            * =====================================================
            *
            * Quando uma obra for selecionada, precisamos:
            *
            * 1. filtrar os documentos pertencentes a ela;
            * 2. conhecer o percentual do documento pertencente
            *    àquela obra;
            * 3. calcular somente o saldo daquela participação.
            *
            * Documentos antigos sem rateio continuam utilizando
            * pd.adms_daman_project_id como fallback.
            */
            if ($hasProjectFilter) {

                $projectAllocationJoin = "
                    LEFT JOIN
                        adms_daman_purchase_document_allocations
                            allocation_project

                        ON allocation_project
                            .adms_daman_purchase_document_id
                            = pd.id

                        AND allocation_project
                            .adms_daman_project_id
                            = :project_id
                ";


                $conditions[] = "
                    (
                        allocation_project.id IS NOT NULL

                        OR

                        (
                            NOT EXISTS (

                                SELECT
                                    1

                                FROM
                                    adms_daman_purchase_document_allocations
                                        allocation_any

                                WHERE
                                    allocation_any
                                        .adms_daman_purchase_document_id
                                        = pd.id
                            )

                            AND
                                pd.adms_daman_project_id
                                = :project_id_fallback
                        )
                    )
                ";


                $params['project_id'] =
                    (int) $filters['project_id'];

                $params['project_id_fallback'] =
                    (int) $filters['project_id'];
            }


            /*
         * =====================================================
         * FORNECEDOR ESPECÍFICO
         * =====================================================
         */
            if (!empty($filters['supplier_key'])) {

                $supplierKey =
                    (string) $filters['supplier_key'];


                /*
             * Fornecedor identificado por CNPJ.
             */
                if (
                    str_starts_with(
                        $supplierKey,
                        'tax:'
                    )
                ) {

                    $taxId =
                        preg_replace(
                            '/\D/',
                            '',
                            substr(
                                $supplierKey,
                                4
                            )
                        );


                    if ($taxId !== '') {

                        $conditions[] = "
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(
                                        COALESCE(
                                            nfe.issuer_cnpj,
                                            supplier.cnpj,
                                            ''
                                        ),
                                        '.',
                                        ''
                                    ),
                                    '/',
                                    ''
                                ),
                                '-',
                                ''
                            ),
                            ' ',
                            ''
                        ) = :supplier_tax_id
                    ";

                        $params['supplier_tax_id'] =
                            $taxId;
                    }
                }


                /*
             * Fornecedor sem CNPJ.
             */ elseif (
                    str_starts_with(
                        $supplierKey,
                        'supplier:'
                    )
                ) {

                    $supplierId =
                        (int) substr(
                            $supplierKey,
                            9
                        );


                    if ($supplierId > 0) {

                        $conditions[] =
                            'pd.adms_daman_supplier_id = :supplier_id';

                        $params['supplier_id'] =
                            $supplierId;
                    }
                }
            }


            $where =
                'WHERE '
                . implode(
                    ' AND ',
                    $conditions
                );


            /*
         * =====================================================
         * CNPJ NORMALIZADO
         * =====================================================
         */
            $supplierTaxExpression = "
            REPLACE(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            COALESCE(
                                nfe.issuer_cnpj,
                                supplier.cnpj,
                                ''
                            ),
                            '.',
                            ''
                        ),
                        '/',
                        ''
                    ),
                    '-',
                    ''
                ),
                ' ',
                ''
            )
        ";


            /*
         * =====================================================
         * CHAVE ÚNICA DO FORNECEDOR
         * =====================================================
         *
         * CNPJ tem prioridade.
         *
         * Isso permite que:
         *
         * NF-e da Empresa X
         * +
         * Compra manual da Empresa X
         *
         * apareçam como um único fornecedor.
         */
            $supplierKeyExpression = "
            CASE

                WHEN {$supplierTaxExpression} <> ''
                THEN CONCAT(
                    'tax:',
                    {$supplierTaxExpression}
                )

                WHEN pd.adms_daman_supplier_id IS NOT NULL
                THEN CONCAT(
                    'supplier:',
                    pd.adms_daman_supplier_id
                )

                ELSE 'unknown'

            END
        ";


            /*
 * =====================================================
 * SALDO DA PARCELA
 * =====================================================
 */
            $installmentOpenAmount = "
    GREATEST(
        pi.original_amount
        -
        COALESCE(
            payments.principal_paid,
            0
        ),
        0
    )
";


            /*
 * =====================================================
 * VALOR DEVIDO
 * =====================================================
 *
 * Sem obra selecionada:
 * saldo total da parcela.
 *
 * Com obra selecionada:
 * somente a participação daquela obra.
 */
            if ($hasProjectFilter) {

                $openAmountExpression = "
                    CASE

                        WHEN allocation_project.id IS NOT NULL
                        THEN

                            {$installmentOpenAmount}
                            *
                            (
                                allocation_project.allocated_amount
                                /
                                NULLIF(
                                    COALESCE(
                                        nfe.total_value,
                                        pd.total_value,
                                        0
                                    ),
                                    0
                                )
                            )

                        ELSE

                            {$installmentOpenAmount}

                    END
                ";
            } else {

                $openAmountExpression =
                    $installmentOpenAmount;
            }


            $sql = "
            SELECT

                {$supplierKeyExpression}
                    AS supplier_key,

                MAX(
                    COALESCE(
                        nfe.issuer_name,
                        supplier.legal_name,
                        'Fornecedor não identificado'
                    )
                ) AS supplier_name,

                MAX(
                    NULLIF(
                        {$supplierTaxExpression},
                        ''
                    )
                ) AS supplier_tax_id,

                COUNT(
                    DISTINCT pd.id
                ) AS documents_count,

                COUNT(
                    DISTINCT pi.id
                ) AS installments_count,

                COALESCE(
                    ROUND(
                        SUM(
                            {$openAmountExpression}
                        ),
                        2
                    ),
                    0
                ) AS open_amount


            FROM
                adms_daman_purchase_installments pi


            INNER JOIN
                adms_daman_purchase_documents pd

                ON pd.id =
                    pi.adms_daman_purchase_document_id
            
            {$projectAllocationJoin}

            LEFT JOIN
                adms_daman_nfes nfe

                ON nfe.id =
                    pd.adms_daman_nfe_id


            LEFT JOIN
                adms_daman_suppliers supplier

                ON supplier.id =
                    pd.adms_daman_supplier_id


            /*
             * Somar previamente somente o principal
             * dos pagamentos válidos.
             */
            LEFT JOIN (

                SELECT

                    adms_daman_purchase_installment_id,

                    SUM(
                        principal_amount
                    ) AS principal_paid

                FROM
                    adms_daman_purchase_installment_payments

                WHERE
                    status = 'active'

                GROUP BY
                    adms_daman_purchase_installment_id

            ) payments

                ON payments
                    .adms_daman_purchase_installment_id
                    = pi.id


            {$where}


            GROUP BY

                {$supplierKeyExpression}


            HAVING

                open_amount > 0


            ORDER BY

                supplier_name ASC
        ";


            $stmt =
                $this->getConnection()
                ->prepare(
                    $sql
                );


            foreach (
                $params as $key => $value
            ) {

                $stmt->bindValue(

                    ':' . $key,

                    $value,

                    is_int($value)
                        ? PDO::PARAM_INT
                        : PDO::PARAM_STR
                );
            }


            $stmt->execute();


            $suppliers =
                $stmt->fetchAll(
                    PDO::FETCH_ASSOC
                );

            /*
            * =====================================================
            * PARCELAMENTOS PENDENTES / FALTA BOLETO
            * =====================================================
            *
            * Sem período informado, documentos ainda sem parcelas
            * também representam obrigação financeira.
            */
            if (
                empty($filters['due_date_start'])
                &&
                empty($filters['due_date_end'])
            ) {

                $suppliers =
                    $this->appendPendingSchedulesBySupplier(
                        $suppliers,
                        $filters
                    );
            }


            return $suppliers;
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao gerar relatório de valores devidos por fornecedor.',
                [
                    'error' =>
                    $e->getMessage(),

                    'filters' =>
                    $filters,
                ]
            );


            throw $e;
        }
    }

    /**
     * Adicionar ao relatório por fornecedor os documentos
     * que ainda estão aguardando definição das parcelas.
     *
     * Somente documentos realmente sem parcelas são
     * considerados aqui, evitando dupla contagem.
     */
    private function appendPendingSchedulesBySupplier(
        array $suppliers,
        ?array $filters = []
    ): array {

        $conditions = [

            "pd.payment_schedule_status = 'pending'",

            /*
         * Só utilizar o valor integral do documento
         * quando ele realmente não possuir parcelas.
         */
            "
            NOT EXISTS (

                SELECT
                    1

                FROM
                    adms_daman_purchase_installments
                        pi_pending

                WHERE
                    pi_pending
                        .adms_daman_purchase_document_id
                        = pd.id
            )
            "
        ];


        $params = [];


        /*
     * =====================================================
     * FILTRO POR OBRA
     * =====================================================
     */
        if (!empty($filters['project_id'])) {

            $conditions[] = "
            (
                EXISTS (

                    SELECT
                        1

                    FROM
                        adms_daman_purchase_document_allocations
                            allocation_filter

                    WHERE
                        allocation_filter
                            .adms_daman_purchase_document_id
                            = pd.id

                        AND allocation_filter
                            .adms_daman_project_id
                            = :project_id
                )

                OR

                (
                    NOT EXISTS (

                        SELECT
                            1

                        FROM
                            adms_daman_purchase_document_allocations
                                allocation_any

                        WHERE
                            allocation_any
                                .adms_daman_purchase_document_id
                                = pd.id
                    )

                    AND
                        pd.adms_daman_project_id
                        = :project_id_fallback
                )
            )
        ";


            $params['project_id'] =
                (int) $filters['project_id'];


            $params['project_id_fallback'] =
                (int) $filters['project_id'];
        }


        /*
        * =====================================================
        * FILTRO POR FORNECEDOR
        * =====================================================
        */
        if (!empty($filters['supplier_key'])) {

            $supplierKey =
                (string) $filters['supplier_key'];


            if (
                str_starts_with(
                    $supplierKey,
                    'tax:'
                )
            ) {

                $taxId =
                    preg_replace(
                        '/\D/',
                        '',
                        substr(
                            $supplierKey,
                            4
                        )
                    );


                if ($taxId !== '') {

                    $conditions[] = "
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(
                                        COALESCE(
                                            nfe.issuer_cnpj,
                                            supplier.cnpj,
                                            ''
                                        ),
                                        '.',
                                        ''
                                    ),
                                    '/',
                                    ''
                                ),
                                '-',
                                ''
                            ),
                            ' ',
                            ''
                        ) = :supplier_tax_id
                    ";


                    $params['supplier_tax_id'] =
                        $taxId;
                }
            } elseif (
                str_starts_with(
                    $supplierKey,
                    'supplier:'
                )
            ) {

                $supplierId =
                    (int) substr(
                        $supplierKey,
                        9
                    );


                if ($supplierId > 0) {

                    $conditions[] =
                        'pd.adms_daman_supplier_id = :supplier_id';


                    $params['supplier_id'] =
                        $supplierId;
                }
            }
        }


        $where =
            'WHERE '
            . implode(
                ' AND ',
                $conditions
            );


        /*
        * CNPJ sem máscara.
        */
        $supplierTaxExpression = "
            REPLACE(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            COALESCE(
                                nfe.issuer_cnpj,
                                supplier.cnpj,
                                ''
                            ),
                            '.',
                            ''
                        ),
                        '/',
                        ''
                    ),
                    '-',
                    ''
                ),
                ' ',
                ''
            )
        ";


        /*
        * Mesma chave utilizada no relatório principal.
        */
        $supplierKeyExpression = "
            CASE

                WHEN {$supplierTaxExpression} <> ''

                THEN CONCAT(
                    'tax:',
                    {$supplierTaxExpression}
                )

                WHEN pd.adms_daman_supplier_id IS NOT NULL

                THEN CONCAT(
                    'supplier:',
                    pd.adms_daman_supplier_id
                )

                ELSE 'unknown'

            END
        ";


        $sql = "
            SELECT

                {$supplierKeyExpression}
                    AS supplier_key,

                MAX(
                    COALESCE(
                        nfe.issuer_name,
                        supplier.legal_name,
                        'Fornecedor não identificado'
                    )
                ) AS supplier_name,

                MAX(
                    NULLIF(
                        {$supplierTaxExpression},
                        ''
                    )
                ) AS supplier_tax_id,

                COUNT(
                    DISTINCT pd.id
                ) AS documents_count,

                0 AS installments_count,

                COALESCE(
                    ROUND(
                        SUM(
                            COALESCE(
                                nfe.total_value,
                                pd.total_value,
                                0
                            )
                        ),
                        2
                    ),
                    0
                ) AS open_amount


            FROM
                adms_daman_purchase_documents pd


            LEFT JOIN
                adms_daman_nfes nfe

                ON nfe.id =
                    pd.adms_daman_nfe_id


            LEFT JOIN
                adms_daman_suppliers supplier

                ON supplier.id =
                    pd.adms_daman_supplier_id


            {$where}


            GROUP BY

                {$supplierKeyExpression}


            HAVING

                open_amount > 0


            ORDER BY

                supplier_name ASC
        ";


        $stmt =
            $this->getConnection()
            ->prepare(
                $sql
            );


        foreach (
            $params as $key => $value
        ) {

            $stmt->bindValue(

                ':' . $key,

                $value,

                is_int($value)
                    ? PDO::PARAM_INT
                    : PDO::PARAM_STR
            );
        }


        $stmt->execute();


        $pendingSuppliers =
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );


        /*
        * =====================================================
        * INDEXAR RESULTADO PRINCIPAL
        * =====================================================
        */
        $indexed = [];


        foreach ($suppliers as $supplier) {

            $indexed[(string) $supplier['supplier_key']] = $supplier;
        }


        /*
        * =====================================================
        * SOMAR OS FB AOS FORNECEDORES
        * =====================================================
        */
        foreach (
            $pendingSuppliers as $pending
        ) {

            $supplierKey =
                (string) $pending['supplier_key'];


            if (!isset($indexed[$supplierKey])) {

                $indexed[$supplierKey] = [

                    'supplier_key' =>
                    $supplierKey,

                    'supplier_name' =>
                    $pending['supplier_name'],

                    'supplier_tax_id' =>
                    $pending['supplier_tax_id'],

                    'documents_count' =>
                    0,

                    'installments_count' =>
                    0,

                    'open_amount' =>
                    0,
                ];
            }


            $indexed[$supplierKey]['documents_count'] =
                (int)
                $indexed[$supplierKey]['documents_count']
                +
                (int)
                $pending['documents_count'];


            $indexed[$supplierKey]['open_amount'] =
                round(

                    (float)
                    $indexed[$supplierKey]['open_amount']
                        +
                        (float)
                        $pending['open_amount'],

                    2
                );
        }


        $suppliers =
            array_values(
                $indexed
            );


        usort(
            $suppliers,

            static fn(
                array $a,
                array $b
            ): int =>

            strcasecmp(
                (string) $a['supplier_name'],
                (string) $b['supplier_name']
            )
        );


        return $suppliers;
    }

    /**
     * ============================================================
     * PROVISÃO DE PAGAMENTOS POR SEMANAS
     * ============================================================
     *
     * A semana financeira utilizada pela empresa é:
     *
     * TERÇA-FEIRA -> SEGUNDA-FEIRA
     *
     * Exemplo:
     *
     * Semana 1: 29/09/2026 a 05/10/2026
     * Semana 2: 06/10/2026 a 12/10/2026
     *
     * Cada linha representa o saldo financeiro de uma parcela
     * que deverá ser desembolsado naquela semana.
     *
     * Regras:
     *
     * - OK não entra;
     * - AP / Permuta não entra na provisão de caixa;
     * - pagamentos ativos reduzem o saldo;
     * - pagamentos estornados não reduzem o saldo;
     * - documentos rateados são distribuídos entre as obras;
     * - documentos sem rateio utilizam a obra original;
     * - fornecedores podem ser excluídos da provisão;
     * - itens excluídos continuam sendo retornados separadamente
     *   para transparência do relatório.
     *
     * @param string $startDate Data inicial da primeira semana (Y-m-d)
     * @param int $weeks Quantidade de semanas
     * @param array $filters
     *
     * @return array
     */
    public function getPaymentProvisionByWeeks(
        string $startDate,
        int $weeks = 4,
        array $filters = []
    ): array {

        /*
        * =====================================================
        * VALIDAR QUANTIDADE DE SEMANAS
        * =====================================================
        */
        $weeks =
            max(
                1,
                min(
                    $weeks,
                    52
                )
            );


        /*
        * =====================================================
        * DATA INICIAL
        * =====================================================
        */
        $start =
            \DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $startDate
            );


        if (
            $start === false
            ||
            $start->format('Y-m-d') !== $startDate
        ) {

            throw new \InvalidArgumentException(
                'Data inicial da provisão inválida.'
            );
        }


        /*
        * O Repository recebe a terça-feira inicial já resolvida.
        *
        * A Controller fará posteriormente a normalização
        * automática caso o usuário escolha outro dia.
        */


        /*
        * =====================================================
        * DATA FINAL DA PROJEÇÃO
        * =====================================================
        *
        * 4 semanas:
        *
        * terça inicial
        * +
        * 27 dias
        *
        * termina na segunda-feira da quarta semana.
        */
        $end =
            $start->modify(
                '+'
                    . (($weeks * 7) - 1)
                    . ' days'
            );


        /*
        * =====================================================
        * FILTROS
        * =====================================================
        */
        $conditions = [

            /*
            * Apenas parcelas com data conhecida.
            */
            'pi.due_date IS NOT NULL',

            /*
            * Somente dentro da janela projetada.
            */
            'pi.due_date >= :provision_start',

            'pi.due_date <= :provision_end',

            /*
            * Quitadas não possuem desembolso futuro.
            *
            * Permuta também fica fora porque este relatório
            * representa necessidade de CAIXA.
            */
            "pi.status NOT IN ('OK', 'AP')",
        ];


        $params = [

            'provision_start' =>
            $start->format('Y-m-d'),

            'provision_end' =>
            $end->format('Y-m-d'),
        ];


        /*
        * =====================================================
        * OBRA
        * =====================================================
        */
        if (!empty($filters['project_id'])) {

            $conditions[] = "
                COALESCE(
                    allocation.adms_daman_project_id,
                    pd.adms_daman_project_id
                ) = :project_id
            ";


            $params['project_id'] =
                (int) $filters['project_id'];
        }


        /*
        * =====================================================
        * MONTAR WHERE
        * =====================================================
        */
        $where =
            'WHERE '
            .
            implode(
                ' AND ',
                $conditions
            );


        /*
        * =====================================================
        * CNPJ NORMALIZADO
        * =====================================================
        */
        $supplierTaxExpression = "
            REPLACE(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            COALESCE(
                                nfe.issuer_cnpj,
                                supplier.cnpj,
                                ''
                            ),
                            '.',
                            ''
                        ),
                        '/',
                        ''
                    ),
                    '-',
                    ''
                ),
                ' ',
                ''
            )
        ";


        /*
        * =====================================================
        * CHAVE DO FORNECEDOR
        * =====================================================
        */
        $supplierKeyExpression = "
            CASE

                WHEN {$supplierTaxExpression} <> ''

                THEN CONCAT(
                    'tax:',
                    {$supplierTaxExpression}
                )

                WHEN pd.adms_daman_supplier_id IS NOT NULL

                THEN CONCAT(
                    'supplier:',
                    pd.adms_daman_supplier_id
                )

                ELSE CONCAT(
                    'unknown:',
                    pd.id
                )

            END
        ";


        /*
        * =====================================================
        * SALDO DA PARCELA
        * =====================================================
        */
        $installmentOpenAmount = "
            GREATEST(
                pi.original_amount
                -
                COALESCE(
                    payments.principal_paid,
                    0
                ),
                0
            )
        ";


        /*
        * =====================================================
        * VALOR DA PARCELA PARA A OBRA
        * =====================================================
        *
        * Se houver rateio:
        *
        * saldo da parcela × participação da obra.
        *
        * Sem rateio:
        *
        * 100% pertence à obra original.
        */
        $projectInstallmentAmount = "
            CASE

                WHEN allocation.id IS NOT NULL

                THEN

                    {$installmentOpenAmount}
                    *
                    (
                        allocation.allocated_amount
                        /
                        NULLIF(
                            COALESCE(
                                nfe.total_value,
                                pd.total_value,
                                0
                            ),
                            0
                        )
                    )

                ELSE

                    {$installmentOpenAmount}

            END
        ";


        /*
        * =====================================================
        * VALOR TOTAL DO LANÇAMENTO PARA A OBRA
        * =====================================================
        *
        * Para documentos rateados, mostrar o valor apropriado
        * àquela obra.
        *
        * Isso evita exibir o valor integral do documento duas
        * vezes em duas obras distintas.
        */
        $projectDocumentAmount = "
            CASE

                WHEN allocation.id IS NOT NULL

                THEN allocation.allocated_amount

                ELSE COALESCE(
                    nfe.total_value,
                    pd.total_value,
                    0
                )

            END
        ";


        /*
        * =====================================================
        * CONSULTA
        * =====================================================
        */
        $sql = "
            SELECT

                /*
                * Documento / lançamento.
                */
                pd.id AS document_id,

                COALESCE(
                    nfe.nfe_number,
                    pd.document_number,
                    CONCAT(
                        '#',
                        pd.id
                    )
                ) AS document_number,


                /*
                * Obra.
                */
                COALESCE(
                    allocation.adms_daman_project_id,
                    pd.adms_daman_project_id
                ) AS project_id,

                project.name AS project_name,


                /*
                * Fornecedor.
                */
                {$supplierKeyExpression}
                    AS supplier_key,

                COALESCE(
                    nfe.issuer_name,
                    supplier.legal_name,
                    'Fornecedor não identificado'
                ) AS supplier_name,

                NULLIF(
                    {$supplierTaxExpression},
                    ''
                ) AS supplier_tax_id,


                /*
                * Compra.
                */
                pd.purchase_date,

                /*
                * Valor integral do documento.
                *
                * Exemplo:
                * NF = R$ 4.560,00
                */
                COALESCE(
                    nfe.total_value,
                    pd.total_value,
                    0
                ) AS document_total_amount,


                /*
                * Valor do documento apropriado àquela obra.
                *
                * Exemplo:
                *
                * Cesário   = 2.000
                * FADESP    = 1.460
                * Kia       =   600
                * Pirajá    =   500
                */
                {$projectDocumentAmount}
                    AS allocated_document_amount,


                /*
                * Parcela.
                */
                pi.id AS installment_id,

                pi.installment_number,

                pi.due_date,

                pi.status AS installment_status,

                pi.original_amount
                    AS installment_original_amount,

                COALESCE(
                    payments.principal_paid,
                    0
                ) AS principal_paid,

                ROUND(
                    {$projectInstallmentAmount},
                    2
                ) AS provision_amount


            FROM
                adms_daman_purchase_installments pi


            INNER JOIN
                adms_daman_purchase_documents pd

                ON pd.id =
                    pi.adms_daman_purchase_document_id


            /*
            * LEFT JOIN é proposital.
            *
            * Documento antigo pode não ter rateio.
            *
            * Documento rateado gera uma linha por obra.
            */
            LEFT JOIN
                adms_daman_purchase_document_allocations
                    allocation

                ON allocation
                    .adms_daman_purchase_document_id
                    = pd.id


            LEFT JOIN
                adms_daman_projects project

                ON project.id =
                    COALESCE(
                        allocation.adms_daman_project_id,
                        pd.adms_daman_project_id
                    )


            LEFT JOIN
                adms_daman_nfes nfe

                ON nfe.id =
                    pd.adms_daman_nfe_id


            LEFT JOIN
                adms_daman_suppliers supplier

                ON supplier.id =
                    pd.adms_daman_supplier_id


            /*
            * Somar somente pagamentos válidos.
            */
            LEFT JOIN (

                SELECT

                    adms_daman_purchase_installment_id,

                    SUM(
                        principal_amount
                    ) AS principal_paid

                FROM
                    adms_daman_purchase_installment_payments

                WHERE
                    status = 'active'

                GROUP BY
                    adms_daman_purchase_installment_id

            ) payments

                ON payments
                    .adms_daman_purchase_installment_id
                    = pi.id


            {$where}


            /*
            * Não trazer parcela já zerada matematicamente.
            */
            HAVING
                provision_amount > 0


            ORDER BY

                pi.due_date ASC,

                project.name ASC,

                supplier_name ASC,

                document_number ASC,

                pi.installment_number ASC
        ";


        try {

            $stmt =
                $this->getConnection()
                ->prepare(
                    $sql
                );


            foreach (
                $params as $key => $value
            ) {

                $stmt->bindValue(

                    ':' . $key,

                    $value,

                    is_int($value)
                        ? PDO::PARAM_INT
                        : PDO::PARAM_STR
                );
            }


            $stmt->execute();


            $rows =
                $stmt->fetchAll(
                    PDO::FETCH_ASSOC
                );


            /*
            * =====================================================
            * FORNECEDORES EXCLUÍDOS
            * =====================================================
            */
            $excludedSupplierKeys =
                $this->normalizeExcludedSupplierKeys(
                    $filters['excluded_supplier_keys']
                        ?? []
                );


            /*
            * =====================================================
            * MONTAR AS SEMANAS
            * =====================================================
            */
            $weekGroups = [];


            for (
                $weekNumber = 1;
                $weekNumber <= $weeks;
                $weekNumber++
            ) {

                $weekStart =
                    $start->modify(
                        '+'
                            . (($weekNumber - 1) * 7)
                            . ' days'
                    );


                $weekEnd =
                    $weekStart->modify(
                        '+6 days'
                    );


                $weekGroups[$weekNumber] = [

                    'week_number' =>
                    $weekNumber,

                    'start_date' =>
                    $weekStart->format(
                        'Y-m-d'
                    ),

                    'end_date' =>
                    $weekEnd->format(
                        'Y-m-d'
                    ),

                    'total' =>
                    0.0,

                    'items' =>
                    [],
                ];
            }


            /*
            * =====================================================
            * ITENS EXCLUÍDOS
            * =====================================================
            */
            $excludedItems = [];

            $excludedAmount = 0.0;


            /*
            * =====================================================
            * FORNECEDORES CONSIDERADOS
            * =====================================================
            */
            $includedSuppliers = [];

            $excludedSuppliers = [];


            /*
            * =====================================================
            * DISTRIBUIR AS PARCELAS NAS SEMANAS
            * =====================================================
            */
            foreach ($rows as $row) {

                $supplierKey =
                    (string) (
                        $row['supplier_key']
                        ?? ''
                    );


                $provisionAmount =
                    round(
                        (float) (
                            $row['provision_amount']
                            ?? 0
                        ),
                        2
                    );


                /*
                * Fornecedor manualmente retirado
                * da provisão.
                */
                if (
                    in_array(
                        $supplierKey,
                        $excludedSupplierKeys,
                        true
                    )
                ) {

                    $excludedItems[] =
                        $row;


                    $excludedAmount +=
                        $provisionAmount;


                    $excludedSuppliers[$supplierKey] = [

                        'supplier_key' =>
                        $supplierKey,

                        'supplier_name' =>
                        $row['supplier_name']
                            ?? 'Fornecedor não identificado',

                    ];


                    continue;
                }


                /*
                * Descobrir em qual semana o vencimento caiu.
                */
                $dueDate =
                    \DateTimeImmutable::createFromFormat(
                        '!Y-m-d',
                        (string) $row['due_date']
                    );


                if ($dueDate === false) {
                    continue;
                }


                $daysFromStart =
                    (int) $start
                        ->diff(
                            $dueDate
                        )
                        ->format('%a');


                /*
                * Como a SQL já restringiu ao intervalo,
                * o resultado estará entre 0 e weeks*7-1.
                */
                $weekNumber =
                    intdiv(
                        $daysFromStart,
                        7
                    )
                    + 1;


                if (
                    !isset(
                        $weekGroups[$weekNumber]
                    )
                ) {
                    continue;
                }


                $weekGroups[$weekNumber]['items'][] =
                    $row;


                $weekGroups[$weekNumber]['total'] =
                    round(
                        (float)
                        $weekGroups[$weekNumber]['total']
                            +
                            $provisionAmount,
                        2
                    );


                $includedSuppliers[$supplierKey] = [

                    'supplier_key' =>
                    $supplierKey,

                    'supplier_name' =>
                    $row['supplier_name']
                        ?? 'Fornecedor não identificado',

                ];
            }


            /*
            * =====================================================
            * TOTAL CONSIDERADO
            * =====================================================
            */
            $totalProvision =
                0.0;


            foreach ($weekGroups as $week) {

                $totalProvision +=
                    (float) $week['total'];
            }


            $totalProvision =
                round(
                    $totalProvision,
                    2
                );


            $excludedAmount =
                round(
                    $excludedAmount,
                    2
                );


            /*
            * =====================================================
            * RETORNO
            * =====================================================
            */
            return [

                'start_date' =>
                $start->format(
                    'Y-m-d'
                ),

                'end_date' =>
                $end->format(
                    'Y-m-d'
                ),

                'weeks_count' =>
                $weeks,

                'weeks' =>
                array_values(
                    $weekGroups
                ),


                /*
                * Caixa efetivamente provisionado.
                */
                'total_provision' =>
                $totalProvision,


                /*
                * Valor que estaria na projeção,
                * mas foi retirado por escolha do usuário.
                */
                'excluded_amount' =>
                $excludedAmount,


                /*
                * Soma gerencial antes das exclusões.
                */
                'gross_projected_amount' =>
                round(
                    $totalProvision
                        +
                        $excludedAmount,
                    2
                ),


                'included_suppliers_count' =>
                count(
                    $includedSuppliers
                ),

                'excluded_suppliers_count' =>
                count(
                    $excludedSuppliers
                ),


                'included_suppliers' =>
                array_values(
                    $includedSuppliers
                ),

                'excluded_suppliers' =>
                array_values(
                    $excludedSuppliers
                ),


                /*
                * Mantemos as linhas retiradas.
                *
                * Isso permitirá posteriormente gerar uma seção:
                *
                * "Valores retirados da provisão".
                */
                'excluded_items' =>
                $excludedItems,

            ];
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao gerar provisão de pagamentos.',
                [
                    'error' =>
                    $e->getMessage(),

                    'start_date' =>
                    $startDate,

                    'weeks' =>
                    $weeks,

                    'filters' =>
                    $filters,
                ]
            );


            throw $e;
        }
    }

    /**
     * Normalizar a lista de fornecedores excluídos.
     *
     * Valores aceitos:
     *
     * tax:04082321000133
     * supplier:15
     */
    private function normalizeExcludedSupplierKeys(
        mixed $supplierKeys
    ): array {

        if (!is_array($supplierKeys)) {

            return [];
        }


        $normalized = [];


        foreach ($supplierKeys as $supplierKey) {

            $supplierKey =
                trim(
                    (string) $supplierKey
                );


            if ($supplierKey === '') {
                continue;
            }


            /*
            * =====================================================
            * CNPJ
            * =====================================================
            */
            if (
                str_starts_with(
                    $supplierKey,
                    'tax:'
                )
            ) {

                $taxId =
                    preg_replace(
                        '/\D/',
                        '',
                        substr(
                            $supplierKey,
                            4
                        )
                    );


                if ($taxId !== '') {

                    $normalized[] =
                        'tax:'
                        .
                        $taxId;
                }


                continue;
            }


            /*
            * =====================================================
            * ID DO FORNECEDOR
            * =====================================================
            */
            if (
                str_starts_with(
                    $supplierKey,
                    'supplier:'
                )
            ) {

                $supplierId =
                    (int) substr(
                        $supplierKey,
                        9
                    );


                if ($supplierId > 0) {

                    $normalized[] =
                        'supplier:'
                        .
                        $supplierId;
                }
            }
        }


        return array_values(
            array_unique(
                $normalized
            )
        );
    }
}
