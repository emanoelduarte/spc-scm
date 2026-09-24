<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use PDO;
use PDOException;

class PurchaseDocumentsRepository extends DbConnection
{

    /**
     * Recuperar todos os lançamentos financeiros de compras.
     *
     * Este método é utilizado na tela principal de Contas a Pagar /
     * Compras lançadas.
     *
     * Para cada lançamento, são recuperados:
     *
     * - Dados principais do lançamento;
     * - NF-e vinculada;
     * - Obra;
     * - Comprador responsável;
     * - Fornecedor emitente da NF-e;
     * - Condição de pagamento;
     * - Valor total da NF-e;
     * - Quantidade de parcelas cadastradas;
     * - Soma das parcelas.
     *
     * As parcelas individuais ainda não são carregadas aqui.
     * Elas serão recuperadas separadamente para montarmos a
     * visualização dinâmica da listagem.
     *
     * @return array
     * Retorna todos os lançamentos financeiros encontrados.
     * Caso não existam registros, retorna um array vazio.
     *
     * @throws PDOException
     * Relança a exceção caso ocorra erro na consulta ao banco.
     */
    public function getAllPurchaseDocuments(int $page = 1, int $limitResult = 10, ?array $filters = []): array
    {

        $offset = max(0, ($page - 1) * $limitResult);

        $conditions = [];
        $params = [];


        /*
        * =====================================================
        * FILTRO POR OBRA
        * =====================================================
        *
        * A obra não é mais determinada apenas pelo campo
        * adms_daman_project_id do lançamento.
        *
        * Um lançamento pode pertencer a várias obras através
        * da tabela de rateio.
        *
        * EXISTS evita duplicar o lançamento principal quando
        * houver várias alocações.
        */
        if (!empty($filters['project_id'])) {

            $conditions[] = "
                EXISTS (
                    SELECT 1

                    FROM
                        adms_daman_purchase_document_allocations
                            AS allocation_filter

                    WHERE
                        allocation_filter
                            .adms_daman_purchase_document_id
                            = pd.id

                        AND allocation_filter
                            .adms_daman_project_id
                            = :project_id
                )
            ";


            $params['project_id'] =
                (int) $filters['project_id'];
        }

        /*
        * =====================================================
        * FILTRO POR FORNECEDOR
        * =====================================================
        *
        * supplier_key pode assumir:
        *
        * tax:11542745000107
        * supplier:38
        *
        * tax:
        * Fornecedor identificado por CNPJ.
        * Funciona tanto para NF-e quanto para compra avulsa.
        *
        * supplier:
        * Fornecedor avulso sem CNPJ.
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
                                            supplier.cnpj
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
            * Fornecedor avulso sem CNPJ.
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

        /*
        * =====================================================
        * FILTRO POR NÚMERO DO DOCUMENTO
        * =====================================================
        */
        if (!empty($filters['document_number'])) {

            $conditions[] = "
            COALESCE(
                nfe.nfe_number,
                pd.document_number
            ) LIKE :document_number
        ";

            $params['document_number'] =
                '%' . trim(
                    (string) $filters['document_number']
                ) . '%';
        }

        /*
        * =====================================================
        * SITUAÇÃO DO PARCELAMENTO
        * =====================================================
        *
        * pending   = falta boleto / parcelas a confirmar.
        * confirmed = parcelamento já definido.
        */
        if (!empty($filters['payment_schedule_status'])) {

            $conditions[] =
                'pd.payment_schedule_status = :payment_schedule_status';

            $params['payment_schedule_status'] =
                $filters['payment_schedule_status'];
        }


        /*
        * =====================================================
        * FILTROS DAS PARCELAS
        * =====================================================
        *
        * Os filtros abaixo são aplicados dentro do mesmo EXISTS
        * para garantir que período e status pertençam
        * à mesma parcela.
        */
        if (
            !empty($filters['installment_status'])
            || !empty($filters['due_date_start'])
            || !empty($filters['due_date_end'])
        ) {

            $installmentConditions = [

                /*
                * Relacionar a parcela ao lançamento.
                */
                'pi_filter.adms_daman_purchase_document_id = pd.id'
            ];


            /*
            * =================================================
            * DATA INICIAL DO VENCIMENTO
            * =================================================
            */
            if (!empty($filters['due_date_start'])) {

                $installmentConditions[] =
                    'pi_filter.due_date >= :due_date_start';

                $params['due_date_start'] =
                    $filters['due_date_start'];
            }


            /*
            * =================================================
            * DATA FINAL DO VENCIMENTO
            * =================================================
            */
            if (!empty($filters['due_date_end'])) {

                $installmentConditions[] =
                    'pi_filter.due_date <= :due_date_end';

                $params['due_date_end'] =
                    $filters['due_date_end'];
            }


            /*
            * =================================================
            * STATUS DA PARCELA
            * =================================================
            */
            if (!empty($filters['installment_status'])) {

                $installmentConditions[] = "
                CASE

                    /*
                    * Status manuais possuem prioridade.
                    */
                    WHEN pi_filter.status = 'OK'
                        THEN 'OK'

                    WHEN pi_filter.status = 'AP'
                        THEN 'AP'


                    /*
                    * Demais status são calculados
                    * pela data atual.
                    */
                    WHEN pi_filter.due_date < CURDATE()
                        THEN 'ON'

                    WHEN pi_filter.due_date <= DATE_ADD(
                        CURDATE(),
                        INTERVAL 7 DAY
                    )
                        THEN 'AT'

                    ELSE 'AV'

                END = :installment_status
            ";

                $params['installment_status'] =
                    $filters['installment_status'];
            }


            /*
            * Adicionar o filtro das parcelas
            * à consulta principal.
            */
            $conditions[] = "
                EXISTS (
                    SELECT
                        1

                    FROM
                        adms_daman_purchase_installments pi_filter

                    WHERE
                        " . implode(
                ' AND ',
                $installmentConditions
            ) . "
                )
            ";
        }


        /*
        * Montar WHERE somente se houver filtros.
        */
        $where = !empty($conditions)
            ? 'WHERE ' . implode(' AND ', $conditions)
            : '';

        try {

            $query = "
                SELECT

                    /*
                    * Dados principais do lançamento.
                    */
                    pd.id,
                    pd.adms_daman_nfe_id,
                    pd.adms_daman_supplier_id,
                    pd.financial_entry_type,
                    pd.purchase_date,
                    pd.status,
                    pd.payment_schedule_status,
                    pd.observation,
                    pd.created_at,


                    /*
                    * Origem do lançamento.
                    */
                    CASE
                        WHEN pd.adms_daman_nfe_id IS NOT NULL
                            THEN 'NFE'
                        WHEN pd.financial_entry_type = 'financial_obligation'
                            THEN 'FINANCIAL_OBLIGATION'
                        ELSE 'MANUAL'
                    END AS document_origin,


                    /*
                    * Tipo do documento.
                    */
                    CASE
                        WHEN pd.adms_daman_nfe_id IS NOT NULL
                            THEN 'NFE'
                        ELSE pd.document_type
                    END AS document_type,


                    /*
                    * Número do documento.
                    */
                    COALESCE(
                        nfe.nfe_number,
                        pd.document_number
                    ) AS document_number,


                    /*
                    * Série existe somente para NF-e.
                    */
                    nfe.series,


                    /*
                    * Fornecedor.
                    */
                    COALESCE(
                        nfe.issuer_name,
                        supplier.legal_name
                    ) AS supplier_name,

                    COALESCE(
                        nfe.issuer_cnpj,
                        supplier.cnpj
                    ) AS supplier_cnpj,


                    /*
                    * Data do documento.
                    */
                    COALESCE(
                        DATE(nfe.issue_date),
                        pd.document_date
                    ) AS document_date,


                    /*
                    * Valor total.
                    */
                    COALESCE(
                        nfe.total_value,
                        pd.total_value
                    ) AS total_value,


                    /*
                    * Relacionamentos.
                    */
                    project.name AS project_name,

                    buyer.name AS buyer_name,

                    payment.name AS payment_method_name,


                    /*
                    * Resumo das parcelas.
                    */
                    COUNT(installment.id)
                        AS installments_count,

                    COALESCE(
                        SUM(installment.original_amount),
                        0
                    ) AS installments_total


                FROM adms_daman_purchase_documents pd


                /*
                * NF-e é opcional.
                */
                LEFT JOIN adms_daman_nfes nfe
                    ON nfe.id = pd.adms_daman_nfe_id


                /*
                * Fornecedor é utilizado no lançamento avulso.
                */
                LEFT JOIN adms_daman_suppliers supplier
                    ON supplier.id = pd.adms_daman_supplier_id


                INNER JOIN adms_daman_projects project
                    ON project.id = pd.adms_daman_project_id


                INNER JOIN adms_daman_users buyer
                    ON buyer.id = pd.adms_daman_user_id


                LEFT JOIN adms_daman_payment_methods payment
                    ON payment.id = pd.adms_daman_payment_method_id


                LEFT JOIN adms_daman_purchase_installments installment
                    ON installment.adms_daman_purchase_document_id = pd.id

                $where

                GROUP BY

                    pd.id,
                    pd.adms_daman_nfe_id,
                    pd.adms_daman_supplier_id,
                    pd.financial_entry_type,
                    pd.purchase_date,
                    pd.status,
                    pd.payment_schedule_status,
                    pd.observation,
                    pd.created_at,

                    pd.document_type,
                    pd.document_number,
                    pd.document_date,
                    pd.total_value,

                    nfe.nfe_number,
                    nfe.series,
                    nfe.issuer_name,
                    nfe.issuer_cnpj,
                    nfe.issue_date,
                    nfe.total_value,

                    supplier.legal_name,
                    supplier.cnpj,

                    project.name,
                    buyer.name,
                    payment.name

                ORDER BY
                    pd.purchase_date DESC,
                    pd.id DESC
                LIMIT :limit
                OFFSET :offset
            ";


            $stmt = $this->getConnection()->prepare($query);

            foreach ($params as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;

                $stmt->bindValue(':' . $key, $value, $type);
            }

            $stmt->bindValue(':limit', $limitResult, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar lançamentos financeiros.',
                [
                    'error' => $err->getMessage(),
                ]
            );

            throw $err;
        }
    }

    /**
     * Recuperar a quantidade total de lançamentos
     * para gerar a paginação.
     *
     * @return int Quantidade de lançamentos.
     */
    public function getAmountPurchaseDocuments(?array $filters = []): int
    {
        $conditions = [];
        $params = [];


        /*
        * =====================================================
        * FILTRO POR OBRA
        * =====================================================
        *
        * A obra não é mais determinada apenas pelo campo
        * adms_daman_project_id do lançamento.
        *
        * Um lançamento pode pertencer a várias obras através
        * da tabela de rateio.
        *
        * EXISTS evita duplicar o lançamento principal quando
        * houver várias alocações.
        */
        if (!empty($filters['project_id'])) {

            $conditions[] = "
                EXISTS (
                    SELECT 1

                    FROM
                        adms_daman_purchase_document_allocations
                            AS allocation_filter

                    WHERE
                        allocation_filter
                            .adms_daman_purchase_document_id
                            = pd.id

                        AND allocation_filter
                            .adms_daman_project_id
                            = :project_id
                )
            ";


            $params['project_id'] =
                (int) $filters['project_id'];
        }

        /*
        * =====================================================
        * FILTRO POR FORNECEDOR
        * =====================================================
        *
        * supplier_key pode assumir:
        *
        * tax:11542745000107
        * supplier:38
        *
        * tax:
        * Fornecedor identificado por CNPJ.
        * Funciona tanto para NF-e quanto para compra avulsa.
        *
        * supplier:
        * Fornecedor avulso sem CNPJ.
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
                                        supplier.cnpj
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
     * Fornecedor avulso sem CNPJ.
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

        /*
        * =====================================================
        * FILTRO POR NÚMERO DO DOCUMENTO
        * =====================================================
        */
        if (!empty($filters['document_number'])) {

            $conditions[] = "
            COALESCE(
                nfe.nfe_number,
                pd.document_number
            ) LIKE :document_number
        ";

            $params['document_number'] =
                '%' . trim(
                    (string) $filters['document_number']
                ) . '%';
        }
        /*
            * =====================================================
            * SITUAÇÃO DO PARCELAMENTO
            * =====================================================
            */
        if (!empty($filters['payment_schedule_status'])) {

            $conditions[] =
                'pd.payment_schedule_status = :payment_schedule_status';

            $params['payment_schedule_status'] =
                $filters['payment_schedule_status'];
        }


        /*
            * =====================================================
            * FILTROS RELACIONADOS ÀS PARCELAS
            * =====================================================
            *
            * Usamos EXISTS para não duplicar o valor
            * do lançamento quando houver várias parcelas.
            */
        if (
            !empty($filters['installment_status'])
            || !empty($filters['due_date_start'])
            || !empty($filters['due_date_end'])
        ) {

            $installmentConditions = [

                /*
                * Relacionar a parcela ao lançamento.
                */
                'pi_filter.adms_daman_purchase_document_id = pd.id'
            ];


            /*
            * =================================================
            * DATA INICIAL DO VENCIMENTO
            * =================================================
            */
            if (!empty($filters['due_date_start'])) {

                $installmentConditions[] =
                    'pi_filter.due_date >= :due_date_start';

                $params['due_date_start'] =
                    $filters['due_date_start'];
            }


            /*
            * =================================================
            * DATA FINAL DO VENCIMENTO
            * =================================================
            */
            if (!empty($filters['due_date_end'])) {

                $installmentConditions[] =
                    'pi_filter.due_date <= :due_date_end';

                $params['due_date_end'] =
                    $filters['due_date_end'];
            }


            /*
            * =================================================
            * STATUS DA PARCELA
            * =================================================
            */
            if (!empty($filters['installment_status'])) {

                $installmentConditions[] = "
                CASE

                    /*
                    * Status manuais possuem prioridade.
                    */
                    WHEN pi_filter.status = 'OK'
                        THEN 'OK'

                    WHEN pi_filter.status = 'AP'
                        THEN 'AP'


                    /*
                    * Demais status são calculados
                    * pela data atual.
                    */
                    WHEN pi_filter.due_date < CURDATE()
                        THEN 'ON'

                    WHEN pi_filter.due_date <= DATE_ADD(
                        CURDATE(),
                        INTERVAL 7 DAY
                    )
                        THEN 'AT'

                    ELSE 'AV'

                END = :installment_status
            ";

                $params['installment_status'] =
                    $filters['installment_status'];
            }


            /*
            * Adicionar o filtro das parcelas
            * à consulta principal.
            */
            $conditions[] = "
                EXISTS (
                    SELECT
                        1

                    FROM
                        adms_daman_purchase_installments pi_filter

                    WHERE
                        " . implode(
                ' AND ',
                $installmentConditions
            ) . "
                )
            ";
        }


        $where = !empty($conditions)
            ? 'WHERE ' . implode(' AND ', $conditions)
            : '';

        try {

            $sql = "
                SELECT
                    COUNT(pd.id) AS amount_records

                FROM
                    adms_daman_purchase_documents pd
                
                LEFT JOIN adms_daman_nfes nfe
                    ON nfe.id = pd.adms_daman_nfe_id

                LEFT JOIN adms_daman_suppliers supplier
                    ON supplier.id = pd.adms_daman_supplier_id
                
                {$where}
            ";


            $stmt = $this->getConnection()->prepare($sql);

            foreach ($params as $key => $value) {

                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;

                $stmt->bindValue(':' . $key, $value, $type);
            }

            $stmt->execute();


            $result =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );


            return (int) (
                $result['amount_records']
                ?? 0
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao contar lançamentos de compras.',
                [
                    'error' => $e->getMessage()
                ]
            );

            throw $e;
        }
    }


    /**
     * Criar o lançamento financeiro originado de uma NF-e.
     *
     * O parcelamento pode estar:
     *
     * - confirmed: condição de pagamento e parcelas já definidas;
     * - pending: aguardando boleto / vencimentos definitivos.
     *
     * Quando estiver pendente, a condição de pagamento pode
     * permanecer NULL até a confirmação posterior das parcelas.
     */
    public function create(array $data): int
    {
        try {

            /*
             * Situação do parcelamento.
             *
             * Qualquer valor diferente de "pending" é tratado
             * como "confirmed" para manter compatibilidade com
             * os lançamentos antigos.
             */
            $paymentScheduleStatus =
                (
                    ($data['payment_schedule_status'] ?? 'confirmed')
                    === 'pending'
                )
                    ? 'pending'
                    : 'confirmed';


            $sql = "
                INSERT INTO adms_daman_purchase_documents
                (
                    adms_daman_nfe_id,
                    adms_daman_project_id,
                    adms_daman_user_id,
                    adms_daman_payment_method_id,
                    payment_schedule_status,
                    purchase_date,
                    observation,
                    status,
                    created_by,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    :adms_daman_nfe_id,
                    :adms_daman_project_id,
                    :adms_daman_user_id,
                    :adms_daman_payment_method_id,
                    :payment_schedule_status,
                    :purchase_date,
                    :observation,
                    :status,
                    :created_by,
                    NOW(),
                    NOW()
                )
            ";


            $stmt =
                $this->getConnection()
                ->prepare($sql);


            $stmt->bindValue(
                ':adms_daman_nfe_id',
                (int) $data['adms_daman_nfe_id'],
                PDO::PARAM_INT
            );


            $stmt->bindValue(
                ':adms_daman_project_id',
                (int) $data['adms_daman_project_id'],
                PDO::PARAM_INT
            );


            $stmt->bindValue(
                ':adms_daman_user_id',
                (int) $data['adms_daman_user_id'],
                PDO::PARAM_INT
            );


            /*
             * Em lançamentos FB (Falta boleto),
             * a condição de pagamento ainda pode não existir.
             */
            $paymentMethodId =
                !empty($data['adms_daman_payment_method_id'])
                    ? (int) $data['adms_daman_payment_method_id']
                    : null;


            $stmt->bindValue(
                ':adms_daman_payment_method_id',
                $paymentMethodId,
                $paymentMethodId === null
                    ? PDO::PARAM_NULL
                    : PDO::PARAM_INT
            );


            $stmt->bindValue(
                ':payment_schedule_status',
                $paymentScheduleStatus,
                PDO::PARAM_STR
            );


            $stmt->bindValue(
                ':purchase_date',
                $data['purchase_date'],
                PDO::PARAM_STR
            );


            $observation =
                !empty($data['observation'])
                    ? (string) $data['observation']
                    : null;


            $stmt->bindValue(
                ':observation',
                $observation,
                $observation === null
                    ? PDO::PARAM_NULL
                    : PDO::PARAM_STR
            );


            $stmt->bindValue(
                ':status',
                $data['status'] ?? 'open',
                PDO::PARAM_STR
            );


            $stmt->bindValue(
                ':created_by',
                (int) $data['created_by'],
                PDO::PARAM_INT
            );


            $stmt->execute();


            return (int) $this
                ->getConnection()
                ->lastInsertId();

        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao criar lançamento financeiro da NF-e.',
                [
                    'nfe_id' =>
                        $data['adms_daman_nfe_id']
                        ?? null,

                    'project_id' =>
                        $data['adms_daman_project_id']
                        ?? null,

                    'user_id' =>
                        $data['adms_daman_user_id']
                        ?? null,

                    'payment_schedule_status' =>
                        $data['payment_schedule_status']
                        ?? null,

                    'error' =>
                        $err->getMessage(),
                ]
            );


            throw $err;
        }
    }

    // Verifica se já existe um lançamento para a NF-e especificada
    public function existsByNfeId(int $nfeId): bool
    {
        try {

            $sql = 'SELECT id
                FROM adms_daman_purchase_documents
                WHERE adms_daman_nfe_id = :nfe_id
                LIMIT 1';

            $stmt = $this->getConnection()->prepare($sql);

            $stmt->bindValue(
                ':nfe_id',
                $nfeId,
                PDO::PARAM_INT
            );

            $stmt->execute();

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao verificar lançamento da NF-e.',
                [
                    'nfe_id' => $nfeId,
                    'error' => $err->getMessage(),
                ]
            );

            throw $err;
        }
    }

    /**
     * Recuperar um lançamento financeiro pelo ID.
     *
     * Este método retorna os dados principais do lançamento
     * juntamente com as informações relacionadas:
     *
     * - NF-e que originou o lançamento;
     * - Obra vinculada à compra;
     * - Comprador responsável;
     * - Condição de pagamento utilizada;
     * - Usuário que cadastrou o lançamento.
     *
     * Os JOINs são utilizados para evitar que a Controller
     * precise realizar várias consultas separadas apenas
     * para montar a tela de visualização do lançamento.
     *
     * @param int $id ID do lançamento financeiro.
     *
     * @return array|bool
     * Retorna um array com os dados do lançamento quando encontrado
     * ou false quando não existir registro com o ID informado.
     *
     * @throws \PDOException
     * Relança a exceção caso ocorra erro na consulta ao banco.
     */
    public function getById(int $id): array|bool
    {
        try {

            /*
         * Consultar o lançamento financeiro.
         *
         * pd      = Purchase Document
         * nfe     = Nota Fiscal Eletrônica
         * project = Obra
         * buyer   = Comprador
         * payment = Condição de pagamento
         * creator = Usuário que cadastrou o lançamento
         */
            $query = "
            SELECT

                /*
                * Origem do lançamento.
                */
                CASE
                    WHEN pd.adms_daman_nfe_id IS NOT NULL
                        THEN 'NFE'
                    WHEN pd.financial_entry_type = 'financial_obligation'
                        THEN 'FINANCIAL_OBLIGATION'
                    ELSE 'MANUAL'
                END AS document_origin,


                /*
                * Documento.
                *
                * Se existir NF-e, utiliza os dados importados.
                * Caso contrário, utiliza os dados informados
                * no lançamento avulso.
                */
                COALESCE(
                    nfe.nfe_number,
                    pd.document_number
                ) AS document_number,

                pd.document_type,

                COALESCE(
                    DATE(nfe.issue_date),
                    pd.document_date
                ) AS document_date,


                /*
                * Fornecedor.
                */
                COALESCE(
                    nfe.issuer_name,
                    supplier.legal_name
                ) AS supplier_name,

                COALESCE(
                    nfe.issuer_cnpj,
                    supplier.cnpj
                ) AS supplier_cnpj,


                /*
                * Valor total.
                */
                COALESCE(
                    nfe.total_value,
                    pd.total_value
                ) AS total_value,

                /*
                 * Dados principais do lançamento.
                 */
                pd.id,
                pd.adms_daman_nfe_id,
                pd.financial_entry_type,
                pd.adms_daman_project_id,
                pd.adms_daman_user_id,
                pd.adms_daman_payment_method_id,
                pd.purchase_date,
                pd.observation,
                pd.status,
                pd.payment_schedule_status,
                pd.created_by,
                pd.created_at,
                pd.updated_at,


                /*
                 * Dados da NF-e.
                 *
                 * O valor total continua vindo da NF-e,
                 * que é a fonte oficial do valor da compra.
                 */
                nfe.nfe_number,
                nfe.series,
                nfe.access_key,
                nfe.issuer_cnpj,
                nfe.issuer_name,
                nfe.issue_date,
                nfe.total_value AS nfe_total_value,


                /*
                 * Nome da obra vinculada ao lançamento.
                 */
                project.name AS project_name,


                /*
                 * Nome do comprador responsável pela compra.
                 */
                buyer.name AS buyer_name,


                /*
                 * Nome da condição de pagamento.
                 *
                 * Exemplo:
                 * BOL. 30/60/90 DIAS
                 */
                payment.name AS payment_method_name,


                /*
                 * Nome do usuário que realizou
                 * o lançamento no sistema.
                 */
                creator.name AS created_by_name


            FROM adms_daman_purchase_documents pd


            /*
             * Recuperar os dados da NF-e vinculada.
             */
            LEFT JOIN adms_daman_nfes nfe
                ON nfe.id = pd.adms_daman_nfe_id

            /*
            * Recuperar os dados do fornecedor avulso
            */
            LEFT JOIN adms_daman_suppliers supplier
                ON supplier.id = pd.adms_daman_supplier_id


            /*
             * Recuperar a obra da compra.
             */
            INNER JOIN adms_daman_projects project
                ON project.id = pd.adms_daman_project_id


            /*
             * Recuperar o comprador responsável.
             */
            INNER JOIN adms_daman_users buyer
                ON buyer.id = pd.adms_daman_user_id


            /*
             * Recuperar a condição de pagamento.
             *
             * LEFT JOIN porque o campo foi criado como nullable
             * e assim o lançamento continua sendo recuperado
             * mesmo que não exista condição vinculada.
             */
            LEFT JOIN adms_daman_payment_methods payment
                ON payment.id = pd.adms_daman_payment_method_id


            /*
             * Recuperar quem cadastrou o lançamento.
             *
             * Comprador e criador podem ser pessoas diferentes.
             */
            INNER JOIN adms_daman_users creator
                ON creator.id = pd.created_by


            /*
             * Recuperar apenas o lançamento solicitado.
             */
            WHERE pd.id = :id

            LIMIT 1
        ";


            /*
         * Preparar a consulta.
         */
            $stmt = $this->getConnection()->prepare(
                $query
            );


            /*
         * Vincular o ID recebido ao parâmetro da consulta.
         *
         * PDO::PARAM_INT garante o tratamento como inteiro.
         */
            $stmt->bindValue(
                ':id',
                $id,
                \PDO::PARAM_INT
            );


            /*
         * Executar a consulta.
         */
            $stmt->execute();


            /*
         * fetch() retorna somente um registro.
         *
         * Caso não encontre o lançamento,
         * o PDO retorna false.
         */
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $err) {

            /*
         * Registrar o erro no log da aplicação
         * para facilitar diagnóstico posterior.
         */
            GenerateLog::generateLog(
                "error",
                "Erro ao recuperar lançamento financeiro: ",
                ['Error' => $err->getMessage()]
            );


            /*
         * Relançar a exceção para que a camada superior
         * possa decidir como tratar o erro.
         */
            throw $err;
        }
    }

    /**
     * Cadastrar um lançamento financeiro avulso.
     *
     * Este método é utilizado quando a compra não possui
     * uma NF-e importada pelo sistema.
     *
     * Nesse cenário:
     *
     * - adms_daman_nfe_id permanece NULL;
     * - fornecedor é informado manualmente;
     * - tipo/número/data do documento podem ser informados;
     * - o valor total é armazenado no próprio lançamento.
     *
     * @param array $data Dados do lançamento avulso.
     *
     * @return int ID do lançamento criado.
     *
     * @throws PDOException
     */
    public function createManual(array $data): int
    {
        try {

            /*
             * Natureza do lançamento financeiro.
             *
             * Mantemos "purchase" como padrão para preservar
             * compatibilidade com todos os fluxos atuais.
             */
            $financialEntryType =
                strtolower(
                    trim(
                        (string) (
                            $data['financial_entry_type']
                            ?? 'purchase'
                        )
                    )
                );

            if (
                !in_array(
                    $financialEntryType,
                    ['purchase', 'financial_obligation'],
                    true
                )
            ) {
                throw new \InvalidArgumentException(
                    'Tipo de lançamento financeiro inválido.'
                );
            }

            /*
         * Inserir o lançamento avulso.
         *
         * Não utilizamos adms_daman_nfe_id porque
         * este lançamento não foi originado de NF-e.
         */
            $sql = "
            INSERT INTO adms_daman_purchase_documents
            (
                adms_daman_nfe_id,
                financial_entry_type,
                adms_daman_supplier_id,
                document_type,
                document_number,
                document_date,
                total_value,

                adms_daman_project_id,
                adms_daman_user_id,
                adms_daman_payment_method_id,
                payment_schedule_status,

                purchase_date,
                observation,
                status,
                created_by,

                created_at,
                updated_at
            )
            VALUES
            (
                NULL,
                :financial_entry_type,
                :adms_daman_supplier_id,
                :document_type,
                :document_number,
                :document_date,
                :total_value,

                :adms_daman_project_id,
                :adms_daman_user_id,
                :adms_daman_payment_method_id,
                :payment_schedule_status,

                :purchase_date,
                :observation,
                :status,
                :created_by,

                NOW(),
                NOW()
            )
        ";


            /*
            * Preparar a query.
            */
            $stmt =
                $this->getConnection()
                ->prepare($sql);


            /*
             * Natureza do lançamento financeiro.
             */
            $stmt->bindValue(
                ':financial_entry_type',
                $financialEntryType,
                \PDO::PARAM_STR
            );


            /*
            * Fornecedor da compra avulsa.
            */
            $stmt->bindValue(
                ':adms_daman_supplier_id',
                (int) $data['adms_daman_supplier_id'],
                \PDO::PARAM_INT
            );


            /*
            * Tipo do documento.
            *
            * Pode ser NULL caso não exista
            * documento formal.
            */
            $stmt->bindValue(
                ':document_type',
                !empty($data['document_type'])
                    ? $data['document_type']
                    : null
            );


            /*
            * Número ou identificação do documento.
            */
            $stmt->bindValue(
                ':document_number',
                !empty($data['document_number'])
                    ? $data['document_number']
                    : null
            );


            /*
            * Data do documento.
            */
            $stmt->bindValue(
                ':document_date',
                !empty($data['document_date'])
                    ? $data['document_date']
                    : null
            );


            /*
            * Valor total da compra avulsa.
            */
            $stmt->bindValue(
                ':total_value',
                $data['total_value']
            );


            /*
            * Obra vinculada.
            */
            $stmt->bindValue(
                ':adms_daman_project_id',
                (int) $data['adms_daman_project_id'],
                \PDO::PARAM_INT
            );


            /*
            * Comprador responsável.
            */
            $stmt->bindValue(
                ':adms_daman_user_id',
                (int) $data['adms_daman_user_id'],
                \PDO::PARAM_INT
            );


            /*
            * Condição de pagamento.
            *
            * Em um lançamento com parcelamento pendente
            * ("Falta boleto"), a condição pode ainda não
            * estar definida.
            */
            $paymentMethodId =
                !empty($data['adms_daman_payment_method_id'])
                ? (int) $data['adms_daman_payment_method_id']
                : null;


            $stmt->bindValue(
                ':adms_daman_payment_method_id',
                $paymentMethodId,
                $paymentMethodId === null
                    ? \PDO::PARAM_NULL
                    : \PDO::PARAM_INT
            );

            /*
            * Situação da confirmação do parcelamento.
            *
            * pending:
            * boletos / parcelas ainda precisam ser confirmados.
            *
            * confirmed:
            * parcelamento definitivo já informado.
            *
            * O valor padrão continua sendo "confirmed"
            * como proteção para fluxos antigos.
            */
            $paymentScheduleStatus =
                (
                    ($data['payment_schedule_status'] ?? 'confirmed')
                    === 'pending'
                )
                ? 'pending'
                : 'confirmed';


            $stmt->bindValue(
                ':payment_schedule_status',
                $paymentScheduleStatus,
                \PDO::PARAM_STR
            );


            /*
            * Data efetiva da compra.
            */
            $stmt->bindValue(
                ':purchase_date',
                $data['purchase_date']
            );


            /*
            * Observação geral do lançamento.
            */
            $stmt->bindValue(
                ':observation',
                !empty($data['observation'])
                    ? $data['observation']
                    : null
            );


            /*
            * Situação inicial do lançamento.
            */
            $stmt->bindValue(
                ':status',
                $data['status'] ?? 'open'
            );


            /*
            * Usuário que realizou o cadastro.
            */
            $stmt->bindValue(
                ':created_by',
                (int) $data['created_by'],
                \PDO::PARAM_INT
            );


            /*
            * Executar o INSERT.
            */
            $stmt->execute();


            /*
            * Retornar o ID do lançamento criado.
            *
            * Esse ID será utilizado posteriormente
            * para vincular as parcelas.
            */
            return (int) $this
                ->getConnection()
                ->lastInsertId();
        } catch (\PDOException $err) {

            /*
            * Utilize aqui o mesmo padrão de GenerateLog
            * que já está sendo usado neste Repository.
            */

            throw $err;
        }
    }

    /**
     * Recuperar fornecedores que possuem
     * lançamentos financeiros.
     *
     * A chave do filtro pode ser:
     *
     * tax:CNPJ
     * supplier:ID
     *
     * @return array
     */
    public function getPurchaseSuppliersSelect(): array
    {
        try {

            $sql = "
            SELECT
                MAX(
                    COALESCE(
                        nfe.issuer_name,
                        supplier.legal_name
                    )
                ) AS supplier_name,

                CASE

                    /*
                     * Havendo CNPJ, utilizar documento fiscal.
                     *
                     * Isso permite agrupar NF-e e compras
                     * avulsas do mesmo fornecedor.
                     */
                    WHEN
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
                        ) <> ''

                    THEN CONCAT(
                        'tax:',
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(
                                        COALESCE(
                                            nfe.issuer_cnpj,
                                            supplier.cnpj
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
                    )


                    /*
                     * Compra avulsa sem CNPJ.
                     */
                    WHEN
                        pd.adms_daman_supplier_id IS NOT NULL

                    THEN CONCAT(
                        'supplier:',
                        pd.adms_daman_supplier_id
                    )


                    ELSE NULL

                END AS supplier_key

            FROM
                adms_daman_purchase_documents pd

            LEFT JOIN
                adms_daman_nfes nfe
                ON nfe.id = pd.adms_daman_nfe_id

            LEFT JOIN
                adms_daman_suppliers supplier
                ON supplier.id = pd.adms_daman_supplier_id

            GROUP BY
                supplier_key

            HAVING
                supplier_key IS NOT NULL

            ORDER BY
                supplier_name ASC
        ";


            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );

            $stmt->execute();


            return $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar fornecedores dos lançamentos.',
                [
                    'error' => $e->getMessage()
                ]
            );

            throw $e;
        }
    }

    /**
     * Recuperar o valor total das parcelas em aberto,
     * respeitando os filtros aplicados na listagem.
     *
     * Parcelas consideradas em aberto:
     * AV = A vencer
     * AT = Atenção
     * ON = Vencida
     * AP = Permuta
     *
     * Parcelas OK e AP não entram no total.
     *
     * @param array|null $filters
     * @return float
     */
    public function getTotalOpenAmount(
        ?array $filters = [],
        ?string $forcedStatus = null
    ): float {

        try {

            $conditions = [
                /*
             * OK = paga
             *
             * Nenhuma das duas representa
             * valor financeiro em aberto.
             */
                "pi.status NOT IN ('OK')"
            ];

            $params = [];

            /*
            * JOIN opcional da obra selecionada.
            *
            * Quando houver filtro por obra, precisamos não apenas
            * saber se a obra participa do lançamento, mas também
            * conhecer o valor apropriado a ela.
            */
            $projectAllocationJoin = '';

            $hasProjectFilter =
                !empty($filters['project_id']);


            /*
            * =====================================================
            * FILTRO POR OBRA
            * =====================================================
            *
            * Aqui usamos JOIN porque os cards precisam conhecer
            * o valor efetivamente apropriado à obra.
            *
            * Existe UNIQUE por documento + obra, portanto esse
            * JOIN não duplica a parcela.
            */
            if ($hasProjectFilter) {

                $projectAllocationJoin = "
                    INNER JOIN
                        adms_daman_purchase_document_allocations
                            AS allocation_project

                        ON allocation_project
                            .adms_daman_purchase_document_id
                            = pd.id

                        AND allocation_project
                            .adms_daman_project_id
                            = :project_id
                ";

                $params['project_id'] =
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
                                            supplier.cnpj
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
                * Compra avulsa sem CNPJ.
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


            /*
            * =====================================================
            * FILTRO POR DOCUMENTO
            * =====================================================
            */
            if (!empty($filters['document_number'])) {

                $conditions[] = "
                COALESCE(
                    nfe.nfe_number,
                    pd.document_number
                ) LIKE :document_number
            ";

                $params['document_number'] =
                    '%'
                    . trim(
                        (string) $filters['document_number']
                    )
                    . '%';
            }

            /*
            * =====================================================
            * SITUAÇÃO DO PARCELAMENTO
            * =====================================================
            */
            if (!empty($filters['payment_schedule_status'])) {

                $conditions[] =
                    'pd.payment_schedule_status = :payment_schedule_status';

                $params['payment_schedule_status'] =
                    $filters['payment_schedule_status'];
            }


            /*
            * =====================================================
            * FILTRO POR DATA INICIAL DE VENCIMENTO
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
            * FILTRO POR DATA FINAL DE VENCIMENTO
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
            * STATUS DA PARCELA
            * =====================================================
            *
            * Quando $forcedStatus for informado,
            * ele tem prioridade sobre o filtro escolhido
            * pelo usuário.
            *
            * Exemplo:
            *
            * Total em Aberto
            * → usa installment_status do filtro.
            *
            * Vence em até 7 dias
            * → força AT.
            */
            $statusToFilter =
                !empty($forcedStatus)
                ? $forcedStatus
                : (
                    $filters['installment_status']
                    ?? null
                );


            if (!empty($statusToFilter)) {

                $conditions[] = "
                    CASE

                        /*
                        * Status manuais têm prioridade.
                        */
                        WHEN pi.status = 'OK'
                            THEN 'OK'

                        WHEN pi.status = 'AP'
                            THEN 'AP'


                        /*
                        * Status financeiros calculados
                        * pela data de vencimento.
                        */
                        WHEN pi.due_date < CURDATE()
                            THEN 'ON'

                        WHEN pi.due_date <= DATE_ADD(
                            CURDATE(),
                            INTERVAL 7 DAY
                        )
                            THEN 'AT'

                        ELSE 'AV'

                    END = :installment_status
                ";

                $params['installment_status'] =
                    $statusToFilter;
            }


            /*
            * =====================================================
            * MONTAR WHERE
            * =====================================================
            */
            $where =
                !empty($conditions)
                ? 'WHERE '
                . implode(
                    ' AND ',
                    $conditions
                )
                : '';

            /*
            * =====================================================
            * VALOR FINANCEIRO A CONSIDERAR
            * =====================================================
            *
            * Sem filtro de obra:
            *   utiliza o saldo integral da parcela.
            *
            * Com filtro de obra:
            *   utiliza somente a proporção pertencente
            *   à obra selecionada.
            */
            if ($hasProjectFilter) {

                $openAmountExpression = "
                    GREATEST(
                        pi.original_amount
                        - COALESCE(
                            payments.principal_paid,
                            0
                        ),
                        0
                    )
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
                ";
            } else {

                $openAmountExpression = "
                    GREATEST(
                        pi.original_amount
                        - COALESCE(
                            payments.principal_paid,
                            0
                        ),
                        0
                    )
                ";
            }


            /*
            * =====================================================
            * CONSULTA
            * =====================================================
            */
            $sql = "
                SELECT
                    COALESCE(
                        ROUND(
                            SUM(
                                {$openAmountExpression}
                            ),
                            2
                        ),
                        0
                    ) AS total_open

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
                
                LEFT JOIN (
                    SELECT
                        adms_daman_purchase_installment_id,
                        SUM(principal_amount) AS principal_paid

                    FROM
                        adms_daman_purchase_installment_payments

                    WHERE
                        status = 'active'

                    GROUP BY
                        adms_daman_purchase_installment_id

                ) payments
                    ON payments.adms_daman_purchase_installment_id = pi.id

                {$where}
            ";


            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );


            /*
            * Bind dos parâmetros.
            */
            foreach ($params as $key => $value) {

                $type =
                    is_int($value)
                    ? PDO::PARAM_INT
                    : PDO::PARAM_STR;

                $stmt->bindValue(
                    ':' . $key,
                    $value,
                    $type
                );
            }


            $stmt->execute();


            $result =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );


            $totalOpen =
                (float) (
                    $result['total_open']
                    ?? 0
                );


            /*
            * =====================================================
            * PARCELAMENTOS PENDENTES / FALTA BOLETO
            * =====================================================
            *
            * Um lançamento com payment_schedule_status = pending
            * continua representando uma dívida, mesmo que ainda
            * não possua parcelas cadastradas.
            *
            * Ele entra somente no Total em Aberto.
            *
            * Não entra quando:
            * - estamos calculando AT ou ON;
            * - existe filtro de status de parcela;
            * - existe filtro por período de vencimento.
            *
            * Isso porque ainda não existe vencimento definido.
            */
            /*
            * Quando o usuário filtrar explicitamente por "confirmed",
            * os documentos FB não podem ser adicionados ao Total em Aberto.
            *
            * Quando não houver filtro, ou quando o filtro for "pending",
            * eles continuam sendo considerados.
            */
            $paymentScheduleStatus =
                $filters['payment_schedule_status']
                ?? null;


            $shouldIncludePendingSchedules =
                empty($forcedStatus)
                &&
                empty($filters['installment_status'])
                &&
                empty($filters['due_date_start'])
                &&
                empty($filters['due_date_end'])
                &&
                $paymentScheduleStatus !== 'confirmed';


            if ($shouldIncludePendingSchedules) {

                $totalOpen +=
                    $this->getPendingScheduleOpenAmount(
                        $filters
                    );
            }


            return $totalOpen;
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar total em aberto.',
                [
                    'error' => $e->getMessage()
                ]
            );

            throw $e;
        }
    }

    /**
     * Recuperar o valor dos lançamentos que ainda estão
     * aguardando confirmação dos boletos / parcelas.
     *
     * Esses documentos representam obrigação financeira,
     * porém ainda não possuem vencimentos definidos.
     */
    private function getPendingScheduleOpenAmount(
        ?array $filters = []
    ): float {

        $conditions = [
            "pd.payment_schedule_status = 'pending'"
        ];

        $params = [];

        $projectAllocationJoin = '';

        $hasProjectFilter =
            !empty($filters['project_id']);


        /*
     * =====================================================
     * OBRA
     * =====================================================
     *
     * Quando houver filtro de obra, considerar somente
     * o valor apropriado àquela obra.
     */
        if ($hasProjectFilter) {

            $projectAllocationJoin = "
            INNER JOIN
                adms_daman_purchase_document_allocations
                    AS allocation_project

                ON allocation_project
                    .adms_daman_purchase_document_id = pd.id

                AND allocation_project
                    .adms_daman_project_id = :project_id
        ";

            $params['project_id'] =
                (int) $filters['project_id'];
        }


        /*
     * Valor a considerar.
     */
        $amountExpression =
            $hasProjectFilter
            ? 'allocation_project.allocated_amount'
            : 'COALESCE(
                nfe.total_value,
                pd.total_value,
                0
            )';


        /*
     * =====================================================
     * FORNECEDOR
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
                                        supplier.cnpj
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


        /*
     * =====================================================
     * DOCUMENTO
     * =====================================================
     */
        if (!empty($filters['document_number'])) {

            $conditions[] = "
                COALESCE(
                    nfe.nfe_number,
                    pd.document_number
                ) LIKE :document_number
            ";

            $params['document_number'] =
                '%'
                . trim(
                    (string) $filters['document_number']
                )
                . '%';
        }


        $where =
            'WHERE '
            . implode(
                ' AND ',
                $conditions
            );


        try {

            $sql = "
                SELECT
                    COALESCE(
                        SUM(
                            {$amountExpression}
                        ),
                        0
                    ) AS total_pending

                FROM
                    adms_daman_purchase_documents pd

                {$projectAllocationJoin}

                LEFT JOIN
                    adms_daman_nfes nfe
                    ON nfe.id = pd.adms_daman_nfe_id

                LEFT JOIN
                    adms_daman_suppliers supplier
                    ON supplier.id =
                        pd.adms_daman_supplier_id

                {$where}
            ";


            $stmt =
                $this->getConnection()
                ->prepare(
                    $sql
                );


            foreach ($params as $key => $value) {

                $type =
                    is_int($value)
                    ? PDO::PARAM_INT
                    : PDO::PARAM_STR;


                $stmt->bindValue(
                    ':' . $key,
                    $value,
                    $type
                );
            }


            $stmt->execute();


            $result =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );


            return (float) (
                $result['total_pending']
                ?? 0
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar lançamentos com parcelamento pendente.',
                [
                    'error' => $e->getMessage()
                ]
            );

            throw $e;
        }
    }

    /**
     * Recuperar o valor total dos lançamentos
     * encontrados pelos filtros.
     *
     * Cada documento é somado apenas uma vez,
     * independentemente da quantidade de parcelas.
     */
    public function getTotalPurchaseDocumentsAmount(
        ?array $filters = []
    ): float {

        try {

            $conditions = [];
            $params = [];

            $projectAllocationJoin = '';

            $hasProjectFilter =
                !empty($filters['project_id']);


            /*
            * =====================================================
            * FILTRO POR OBRA
            * =====================================================
            *
            * A obra não é mais determinada apenas pelo campo
            * adms_daman_project_id do lançamento.
            *
            * Um lançamento pode pertencer a várias obras através
            * da tabela de rateio.
            *
            * EXISTS evita duplicar o lançamento principal quando
            * houver várias alocações.
            */
            if ($hasProjectFilter) {

                $projectAllocationJoin = "
                    INNER JOIN
                        adms_daman_purchase_document_allocations
                            AS allocation_project

                        ON allocation_project
                            .adms_daman_purchase_document_id
                            = pd.id

                        AND allocation_project
                            .adms_daman_project_id
                            = :project_id
                ";

                $params['project_id'] =
                    (int) $filters['project_id'];
            }

            /*
            * Sem filtro de obra, mostrar o valor total do documento.
            *
            * Com filtro, mostrar apenas o valor apropriado
            * àquela obra.
            */
            $documentAmountExpression =
                $hasProjectFilter
                ? 'allocation_project.allocated_amount'
                : 'COALESCE(
                nfe.total_value,
                pd.total_value,
                0
            )';


            /*
            * =====================================================
            * FORNECEDOR
            * =====================================================
            */
            if (!empty($filters['supplier_key'])) {

                $supplierKey =
                    (string) $filters['supplier_key'];


                /*
             * Fornecedor identificado pelo CNPJ.
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
                                            supplier.cnpj
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
             * Compra avulsa sem CNPJ.
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


            /*
            * =====================================================
            * DOCUMENTO
            * =====================================================
            */
            if (!empty($filters['document_number'])) {

                $conditions[] = "
                    COALESCE(
                        nfe.nfe_number,
                        pd.document_number
                    ) LIKE :document_number
                ";

                $params['document_number'] =
                    '%'
                    . trim(
                        (string) $filters['document_number']
                    )
                    . '%';
            }

            /*
            * =====================================================
            * SITUAÇÃO DO PARCELAMENTO
            * =====================================================
            */
            if (!empty($filters['payment_schedule_status'])) {

                $conditions[] =
                    'pd.payment_schedule_status = :payment_schedule_status';

                $params['payment_schedule_status'] =
                    $filters['payment_schedule_status'];
            }


            /*
            * =====================================================
            * FILTROS RELACIONADOS ÀS PARCELAS
            * =====================================================
            *
            * Usamos EXISTS para não duplicar o valor
            * do lançamento quando houver várias parcelas.
            */
            if (
                !empty($filters['installment_status'])
                || !empty($filters['due_date_start'])
                || !empty($filters['due_date_end'])
            ) {

                $installmentConditions = [
                    'pi_filter.adms_daman_purchase_document_id = pd.id'
                ];


                /*
                * Vencimento inicial.
                */
                if (!empty($filters['due_date_start'])) {

                    $installmentConditions[] =
                        'pi_filter.due_date >= :due_date_start';

                    $params['due_date_start'] =
                        $filters['due_date_start'];
                }


                /*
                * Vencimento final.
                */
                if (!empty($filters['due_date_end'])) {

                    $installmentConditions[] =
                        'pi_filter.due_date <= :due_date_end';

                    $params['due_date_end'] =
                        $filters['due_date_end'];
                }


                /*
                * Status da parcela.
                */
                if (!empty($filters['installment_status'])) {

                    $installmentConditions[] = "
                        CASE

                            WHEN pi_filter.status = 'OK'
                                THEN 'OK'

                            WHEN pi_filter.status = 'AP'
                                THEN 'AP'

                            WHEN pi_filter.due_date < CURDATE()
                                THEN 'ON'

                            WHEN pi_filter.due_date <= DATE_ADD(
                                CURDATE(),
                                INTERVAL 7 DAY
                            )
                                THEN 'AT'

                            ELSE 'AV'

                        END = :installment_status
                    ";

                    $params['installment_status'] =
                        $filters['installment_status'];
                }


                $conditions[] = "
                    EXISTS (
                        SELECT
                            1

                        FROM
                            adms_daman_purchase_installments pi_filter

                        WHERE
                            " . implode(
                    ' AND ',
                    $installmentConditions
                ) . "
                    )
                ";
            }


            /*
            * Montar WHERE.
            */
            $where =
                !empty($conditions)
                ? 'WHERE '
                . implode(
                    ' AND ',
                    $conditions
                )
                : '';


            /*
            * =====================================================
            * SOMAR VALOR DOS DOCUMENTOS
            * =====================================================
            *
            * NF-e:
            * nfe.total_value
            *
            * Compra avulsa:
            * pd.total_value
            */
            $sql = "
                SELECT
                    COALESCE(
                        SUM(
                            {$documentAmountExpression}
                        )
                    ) AS total_documents

                FROM
                    adms_daman_purchase_documents pd
                
                {$projectAllocationJoin}

                LEFT JOIN
                    adms_daman_nfes nfe
                    ON nfe.id =
                        pd.adms_daman_nfe_id

                LEFT JOIN
                    adms_daman_suppliers supplier
                    ON supplier.id =
                        pd.adms_daman_supplier_id

                {$where}
            ";


            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );


            foreach ($params as $key => $value) {

                $type =
                    is_int($value)
                    ? PDO::PARAM_INT
                    : PDO::PARAM_STR;

                $stmt->bindValue(
                    ':' . $key,
                    $value,
                    $type
                );
            }


            $stmt->execute();


            $result =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );


            return (float) (
                $result['total_documents']
                ?? 0
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar valor total dos lançamentos.',
                [
                    'error' => $e->getMessage()
                ]
            );

            throw $e;
        }
    }

    /**
     * Confirmar o cronograma de pagamento de um lançamento FB.
     *
     * Vincula a condição de pagamento definitiva e altera
     * payment_schedule_status de pending para confirmed.
     *
     * @param int $purchaseDocumentId ID do lançamento.
     * @param int $paymentMethodId ID da condição de pagamento.
     *
     * @return bool True quando exatamente um lançamento foi atualizado.
     *
     * @throws PDOException
     */
    public function confirmPaymentSchedule(
        int $purchaseDocumentId,
        int $paymentMethodId
    ): bool {
        try {

            $sql = "
                UPDATE
                    adms_daman_purchase_documents

                SET
                    adms_daman_payment_method_id =
                        :payment_method_id,

                    payment_schedule_status =
                        'confirmed',

                    updated_at =
                        NOW()

                WHERE
                    id =
                        :purchase_document_id

                    AND payment_schedule_status =
                        'pending'
            ";


            $stmt =
                $this->getConnection()
                ->prepare(
                    $sql
                );


            $stmt->bindValue(
                ':payment_method_id',
                $paymentMethodId,
                PDO::PARAM_INT
            );


            $stmt->bindValue(
                ':purchase_document_id',
                $purchaseDocumentId,
                PDO::PARAM_INT
            );


            $stmt->execute();


            return $stmt->rowCount() === 1;
        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao confirmar cronograma de pagamento.',
                [
                    'purchase_document_id' =>
                    $purchaseDocumentId,

                    'payment_method_id' =>
                    $paymentMethodId,

                    'error' =>
                    $err->getMessage(),
                ]
            );


            throw $err;
        }
    }

    /**
     * Atualizar o status financeiro do lançamento.
     *
     * open   = possui saldo em aberto.
     * closed = obrigação totalmente quitada.
     *
     * @param int $purchaseDocumentId
     * @param string $status
     *
     * @return bool
     *
     * @throws PDOException
     */
    public function updateStatus(
        int $purchaseDocumentId,
        string $status
    ): bool {

        /*
     * Evitar gravar qualquer status inesperado.
     */
        $allowedStatuses = [
            'open',
            'closed',
        ];


        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {

            throw new \InvalidArgumentException(
                'Status do lançamento financeiro inválido.'
            );
        }


        try {

            $sql = "
            UPDATE
                adms_daman_purchase_documents

            SET
                status = :status,
                updated_at = NOW()

            WHERE
                id = :purchase_document_id
        ";


            $stmt =
                $this->getConnection()
                ->prepare(
                    $sql
                );


            $stmt->bindValue(
                ':status',
                $status,
                PDO::PARAM_STR
            );


            $stmt->bindValue(
                ':purchase_document_id',
                $purchaseDocumentId,
                PDO::PARAM_INT
            );


            return $stmt->execute();
        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao atualizar status do lançamento financeiro.',
                [
                    'purchase_document_id' =>
                    $purchaseDocumentId,

                    'status' =>
                    $status,

                    'error' =>
                    $err->getMessage(),
                ]
            );


            throw $err;
        }
    }

    /**
     * Recuperar os contadores de alerta exibidos no topo
     * da tela de Contas a Pagar.
     *
     * Os indicadores são globais e não dependem dos filtros
     * atualmente aplicados na listagem. Assim, funcionam como
     * atalhos permanentes para pendências financeiras.
     *
     * Retorna:
     *
     * - pending_payment_schedule_count:
     *   quantidade de lançamentos FB / parcelas a confirmar;
     *
     * - attention_installments_count:
     *   quantidade de parcelas AT, com vencimento entre hoje
     *   e os próximos 7 dias;
     *
     * - overdue_installments_count:
     *   quantidade de parcelas ON, com vencimento anterior a hoje.
     *
     * A classificação de AT e ON segue exatamente a mesma regra
     * utilizada na listagem: OK e AP têm prioridade; os demais
     * status são recalculados dinamicamente pela data de vencimento.
     *
     * @return array
     *
     * @throws PDOException
     */
    public function getFinancialAlertCounts(): array
    {
        try {

            $sql = "
                SELECT

                    /*
                     * FB = lançamento aguardando boleto /
                     * confirmação do parcelamento.
                     */
                    (
                        SELECT
                            COUNT(*)

                        FROM
                            adms_daman_purchase_documents pd_pending

                        WHERE
                            pd_pending.payment_schedule_status = 'pending'
                    ) AS pending_payment_schedule_count,


                    /*
                     * AT = parcelas que vencem hoje ou
                     * nos próximos 7 dias.
                     */
                    COALESCE(
                        SUM(
                            CASE
                                WHEN status_rows.installment_status = 'AT'
                                    THEN 1
                                ELSE 0
                            END
                        ),
                        0
                    ) AS attention_installments_count,


                    /*
                     * ON = parcelas vencidas.
                     */
                    COALESCE(
                        SUM(
                            CASE
                                WHEN status_rows.installment_status = 'ON'
                                    THEN 1
                                ELSE 0
                            END
                        ),
                        0
                    ) AS overdue_installments_count


                FROM
                (
                    SELECT
                        CASE

                            /*
                             * Status manuais possuem prioridade.
                             */
                            WHEN pi.status = 'OK'
                                THEN 'OK'

                            WHEN pi.status = 'AP'
                                THEN 'AP'


                            /*
                             * Demais status são calculados
                             * pela data atual.
                             */
                            WHEN pi.due_date < CURDATE()
                                THEN 'ON'

                            WHEN pi.due_date <= DATE_ADD(
                                CURDATE(),
                                INTERVAL 7 DAY
                            )
                                THEN 'AT'

                            ELSE 'AV'

                        END AS installment_status

                    FROM
                        adms_daman_purchase_installments pi

                    INNER JOIN
                        adms_daman_purchase_documents pd
                        ON pd.id =
                            pi.adms_daman_purchase_document_id

                    WHERE
                        pd.payment_schedule_status = 'confirmed'

                ) AS status_rows
            ";


            $stmt =
                $this->getConnection()
                    ->prepare($sql);

            $stmt->execute();


            $result =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                ) ?: [];


            return [
                'pending_payment_schedule_count' =>
                    (int) (
                        $result['pending_payment_schedule_count']
                        ?? 0
                    ),

                'attention_installments_count' =>
                    (int) (
                        $result['attention_installments_count']
                        ?? 0
                    ),

                'overdue_installments_count' =>
                    (int) (
                        $result['overdue_installments_count']
                        ?? 0
                    ),
            ];

        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar contadores de alerta financeiro.',
                [
                    'error' => $err->getMessage(),
                ]
            );


            throw $err;
        }
    }


    /**
     * Recuperar, em lote, o resumo financeiro dos lançamentos
     * vinculados às NF-e informadas.
     *
     * O objetivo deste método é abastecer a tela de NF-e Recebidas
     * sem executar uma consulta para cada nota fiscal.
     *
     * Para cada NF-e lançada, retorna:
     *
     * - ID do lançamento financeiro;
     * - situação do parcelamento (pending / confirmed);
     * - status do lançamento (open / closed);
     * - quantidade de parcelas;
     * - valor total do documento;
     * - principal já pago em pagamentos ativos;
     * - saldo principal ainda em aberto;
     * - situação financeira simplificada para a View.
     *
     * Regras da situação financeira:
     *
     * pending = FB / parcelas ainda não confirmadas;
     * paid    = obrigação quitada;
     * open    = possui saldo financeiro em aberto.
     *
     * Pagamentos estornados não entram no cálculo.
     *
     * @param array $nfeIds IDs das NF-e.
     *
     * @return array Resumo indexado pelo ID da NF-e.
     *
     * @throws PDOException
     */
    public function getFinancialSummaryByNfeIds(
        array $nfeIds
    ): array {

        /*
         * Normalizar os IDs recebidos.
         */
        $nfeIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $nfeIds
                    ),
                    static fn(int $id): bool => $id > 0
                )
            )
        );


        if (empty($nfeIds)) {
            return [];
        }


        try {

            /*
             * Criar placeholders nomeados para o IN.
             *
             * Exemplo:
             * :nfe_id_0, :nfe_id_1, :nfe_id_2
             */
            $placeholders = [];

            foreach ($nfeIds as $index => $nfeId) {
                $placeholders[] =
                    ':nfe_id_' . $index;
            }


            $query = "
                SELECT
                    pd.adms_daman_nfe_id AS nfe_id,
                    pd.id AS purchase_document_id,
                    pd.payment_schedule_status,
                    pd.status,

                    COUNT(pi.id) AS installments_count,

                    COALESCE(
                        nfe.total_value,
                        pd.total_value,
                        0
                    ) AS total_amount,

                    COALESCE(
                        SUM(
                            COALESCE(
                                payments.principal_paid,
                                0
                            )
                        ),
                        0
                    ) AS paid_amount,

                    CASE
                        /*
                         * FB continua sendo uma obrigação integral,
                         * embora ainda não possua parcelas definitivas.
                         */
                        WHEN pd.payment_schedule_status = 'pending'
                            THEN COALESCE(
                                nfe.total_value,
                                pd.total_value,
                                0
                            )

                        /*
                         * Proteção para registros confirmados antigos
                         * que eventualmente ainda não possuam parcelas.
                         */
                        WHEN COUNT(pi.id) = 0
                            THEN COALESCE(
                                nfe.total_value,
                                pd.total_value,
                                0
                            )

                        ELSE COALESCE(
                            SUM(
                                GREATEST(
                                    pi.original_amount
                                    - COALESCE(
                                        payments.principal_paid,
                                        0
                                    ),
                                    0
                                )
                            ),
                            0
                        )
                    END AS remaining_amount

                FROM
                    adms_daman_purchase_documents pd

                LEFT JOIN
                    adms_daman_nfes nfe
                    ON nfe.id = pd.adms_daman_nfe_id

                LEFT JOIN
                    adms_daman_purchase_installments pi
                    ON pi.adms_daman_purchase_document_id = pd.id

                /*
                 * Somar primeiro o principal pago por parcela.
                 * Assim evitamos duplicar parcelas quando houver
                 * mais de um pagamento registrado no histórico.
                 */
                LEFT JOIN (
                    SELECT
                        adms_daman_purchase_installment_id,
                        SUM(principal_amount) AS principal_paid

                    FROM
                        adms_daman_purchase_installment_payments

                    WHERE
                        status = 'active'

                    GROUP BY
                        adms_daman_purchase_installment_id
                ) payments
                    ON payments.adms_daman_purchase_installment_id = pi.id

                WHERE
                    pd.adms_daman_nfe_id IN (
                        " . implode(', ', $placeholders) . "
                    )

                GROUP BY
                    pd.id,
                    pd.adms_daman_nfe_id,
                    pd.payment_schedule_status,
                    pd.status,
                    pd.total_value,
                    nfe.total_value
            ";


            $stmt =
                $this->getConnection()
                ->prepare(
                    $query
                );


            foreach ($nfeIds as $index => $nfeId) {

                $stmt->bindValue(
                    ':nfe_id_' . $index,
                    $nfeId,
                    PDO::PARAM_INT
                );
            }


            $stmt->execute();


            $rows =
                $stmt->fetchAll(
                    PDO::FETCH_ASSOC
                );


            /*
             * Indexar pelo ID da NF-e para a Controller conseguir
             * anexar o resumo sem novos loops de pesquisa.
             */
            $summaryByNfeId = [];


            foreach ($rows as $row) {

                $nfeId =
                    (int) (
                        $row['nfe_id']
                        ?? 0
                    );


                if ($nfeId <= 0) {
                    continue;
                }


                $remainingAmount =
                    (float) (
                        $row['remaining_amount']
                        ?? 0
                    );


                $installmentsCount =
                    (int) (
                        $row['installments_count']
                        ?? 0
                    );


                /*
                 * Situação simplificada para a tela de NF-e.
                 */
                if (
                    ($row['payment_schedule_status'] ?? 'confirmed')
                    === 'pending'
                ) {

                    $financialStatus = 'pending';
                } elseif (
                    ($row['status'] ?? 'open') === 'closed'
                    ||
                    (
                        $installmentsCount > 0
                        &&
                        $remainingAmount <= 0.00001
                    )
                ) {

                    $financialStatus = 'paid';
                } else {

                    $financialStatus = 'open';
                }


                $row['financial_status'] =
                    $financialStatus;


                $summaryByNfeId[$nfeId] =
                    $row;
            }


            return $summaryByNfeId;
        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar resumo financeiro das NF-e.',
                [
                    'nfe_ids' => $nfeIds,
                    'error' => $err->getMessage(),
                ]
            );


            throw $err;
        }
    }

    /**
     * Atualizar somente os dados administrativos editáveis
     * de um lançamento financeiro.
     *
     * Nesta etapa NÃO são alterados:
     *
     * - fornecedor;
     * - documento;
     * - valor total;
     * - condição de pagamento;
     * - rateio;
     * - status financeiro.
     *
     * Essas proteções pertencem à regra de negócio definida
     * para a edição do lançamento.
     */
    public function updateEditableData(
        int $purchaseDocumentId,
        int $buyerId,
        string $purchaseDate,
        ?string $observation
    ): bool {

        try {

            $sql = "
                UPDATE
                    adms_daman_purchase_documents

                SET
                    adms_daman_user_id =
                        :buyer_id,

                    purchase_date =
                        :purchase_date,

                    observation =
                        :observation,

                    updated_at =
                        NOW()

                WHERE
                    id =
                        :purchase_document_id
            ";


            $stmt =
                $this->getConnection()
                    ->prepare(
                        $sql
                    );


            $stmt->bindValue(
                ':buyer_id',
                $buyerId,
                PDO::PARAM_INT
            );


            $stmt->bindValue(
                ':purchase_date',
                $purchaseDate,
                PDO::PARAM_STR
            );


            if ($observation === null) {

                $stmt->bindValue(
                    ':observation',
                    null,
                    PDO::PARAM_NULL
                );

            } else {

                $stmt->bindValue(
                    ':observation',
                    $observation,
                    PDO::PARAM_STR
                );
            }


            $stmt->bindValue(
                ':purchase_document_id',
                $purchaseDocumentId,
                PDO::PARAM_INT
            );


            return $stmt->execute();

        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao atualizar dados administrativos do lançamento financeiro.',
                [
                    'purchase_document_id' =>
                        $purchaseDocumentId,

                    'buyer_id' =>
                        $buyerId,

                    'purchase_date' =>
                        $purchaseDate,

                    'error' =>
                        $err->getMessage(),
                ]
            );


            throw $err;
        }
    }


    /**
     * Excluir definitivamente um lançamento financeiro.
     *
     * As regras que autorizam ou bloqueiam a exclusão
     * são validadas no PurchaseDocumentService.
     *
     * As relações de parcelas e rateio utilizam ON DELETE CASCADE,
     * portanto a remoção do lançamento elimina esses registros
     * vinculados. Pagamentos possuem proteção por FK e também são
     * validados previamente pelo Service.
     *
     * @param int $purchaseDocumentId
     * @return bool
     * @throws PDOException
     */
    public function deleteById(
        int $purchaseDocumentId
    ): bool {

        try {

            $sql = "
                DELETE FROM
                    adms_daman_purchase_documents

                WHERE
                    id =
                        :purchase_document_id

                LIMIT 1
            ";


            $stmt =
                $this->getConnection()
                    ->prepare(
                        $sql
                    );


            $stmt->bindValue(
                ':purchase_document_id',
                $purchaseDocumentId,
                PDO::PARAM_INT
            );


            $stmt->execute();


            return
                $stmt->rowCount()
                === 1;

        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao excluir lançamento financeiro.',
                [
                    'purchase_document_id' =>
                        $purchaseDocumentId,

                    'error' =>
                        $err->getMessage(),
                ]
            );


            throw $err;
        }
    }
}
