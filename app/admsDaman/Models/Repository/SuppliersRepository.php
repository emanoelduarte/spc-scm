<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class SuppliersRepository extends DbConnection

{
    /*
     * ==========================================================
     * TIPOS DE FORNECEDOR
     * ==========================================================
     *
     * Centralizar os IDs evita espalhar números "mágicos"
     * pelos Controllers e pelas consultas.
     */
    public const TYPE_SALE = 1;
    public const TYPE_RENTAL = 2;
    public const TYPE_SERVICE = 3;
    public const TYPE_FINANCIAL_OBLIGATION = 4;

    public function getAllSuppliers(int $page = 1, int $limitResult = 10, ?array $filters = []): array|bool
    {
        $offset = max(0, ($page - 1) * $limitResult);

        $conditions = [];
        $params = [];

        if (!empty($filters['legal_name'])) {
            $conditions[] = "legal_name LIKE :legal_name";
            $params['legal_name'] = '%' . $filters['legal_name'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT id, legal_name, cnpj, contact_name, phone, supplier_status
            FROM adms_daman_suppliers
            {$where}
            ORDER BY legal_name ASC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->bindValue(':limit', $limitResult, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar a quantidade de usuários para paginação
     * @return int|bool Quantidade de usuários encontrados no banco de dados
     */

    public function getAmountSuppliers(?array $filters = []): int|bool
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['legal_name'])) {
            $conditions[] = "legal_name LIKE :legal_name";
            $params['legal_name'] = '%' . $filters['legal_name'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT COUNT(id) AS amount_records
            FROM adms_daman_suppliers
            {$where}";

        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    /**
     * Recuperar o fornecedor
     * 
     * @return array|bool Fornecedor recuperado do banco de dados
     */
    public function getSupplier(int $id): array|bool
    {
        try {
            $sql = '
                SELECT
                    ads.id,
                    ads.legal_name,
                    ads.trade_name,
                    ads.cnpj,
                    ads.contact_name,
                    ads.phone,
                    ads.email,
                    ads.adms_daman_suppliers_types_id,
                    ads.accepted_payments,
                    ads.supplier_status,
                    ads.created_at,
                    ads.updated_at,
                    adst.name AS supplier_type
                FROM adms_daman_suppliers AS ads
                INNER JOIN adms_daman_suppliers_types AS adst
                    ON adst.id = ads.adms_daman_suppliers_types_id
                WHERE ads.id = :id
                LIMIT 1
            ';

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$supplier) {
                return false;
            }

            $additionalCnpjs = [];

            foreach ($this->getSupplierTaxIds($id) as $taxId) {
                if ((int) ($taxId['is_primary'] ?? 0) === 1) {
                    continue;
                }

                $additionalCnpjs[] =
                    $this->formatCnpj(
                        (string) ($taxId['cnpj'] ?? '')
                    );
            }

            $supplier['additional_cnpjs'] =
                implode(PHP_EOL, $additionalCnpjs);

            return $supplier;
        } catch (Exception $err) {
            GenerateLog::generateLog(
                'error',
                'Fornecedor não encontrado',
                [
                    'id' => $id,
                    'error' => $err->getMessage(),
                ]
            );

            return false;
        }
    }

    /**
     * Cadastrar novo Fornecedor
     * @param array $data Dados do fornecedor
     * @return bool Sucesso ou falha
     */
    public function createSupplier(array $data): bool|int
    {
        $connection = $this->getConnection();

        try {
            $connection->beginTransaction();

            $sql = '
                INSERT INTO adms_daman_suppliers
                (
                    legal_name,
                    trade_name,
                    cnpj,
                    contact_name,
                    phone,
                    email,
                    adms_daman_suppliers_types_id,
                    supplier_status,
                    created_at
                )
                VALUES
                (
                    :legal_name,
                    :trade_name,
                    :cnpj,
                    :contact_name,
                    :phone,
                    :email,
                    :adms_daman_suppliers_types_id,
                    :supplier_status,
                    :created_at
                )
            ';

            $email =
                trim((string) ($data['email'] ?? '')) !== ''
                    ? trim((string) $data['email'])
                    : null;

            $stmt = $connection->prepare($sql);
            $stmt->bindValue(':legal_name', strtoupper((string) $data['legal_name']), PDO::PARAM_STR);
            $stmt->bindValue(':trade_name', strtoupper((string) $data['trade_name']), PDO::PARAM_STR);
            $stmt->bindValue(':cnpj', (string) $data['cnpj'], PDO::PARAM_STR);
            $stmt->bindValue(':contact_name', strtoupper((string) $data['contact_name']), PDO::PARAM_STR);
            $stmt->bindValue(':phone', (string) $data['phone'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, $email === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(
                ':adms_daman_suppliers_types_id',
                (int) $data['adms_daman_suppliers_types_id'],
                PDO::PARAM_INT
            );
            $stmt->bindValue(':supplier_status', 1, PDO::PARAM_INT);
            $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
            $stmt->execute();

            $supplierId = (int) $connection->lastInsertId();

            $this->syncSupplierTaxIds(
                $supplierId,
                $data
            );

            $connection->commit();

            return $supplierId;
        } catch (Exception $e) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            GenerateLog::generateLog(
                'error',
                'Fornecedor não cadastrado.',
                [
                    'legal_name' => $data['legal_name'] ?? null,
                    'error' => $e->getMessage(),
                ]
            );

            return false;
        }
    }

    /**
     * Editar os dados do Fornecedor
     * @param array $data Dados atualizados do Fornecedor
     * @return bool Sucesso ou Falha | true|false
     */
    public function updateSupplier(array $data): bool
    {
        $connection = $this->getConnection();

        try {
            $connection->beginTransaction();

            $sql = '
                UPDATE adms_daman_suppliers
                SET
                    legal_name = :legal_name,
                    trade_name = :trade_name,
                    cnpj = :cnpj,
                    contact_name = :contact_name,
                    phone = :phone,
                    email = :email,
                    adms_daman_suppliers_types_id = :adms_daman_suppliers_types_id,
                    supplier_status = :supplier_status,
                    updated_at = :updated_at
                WHERE id = :id
            ';

            $email =
                trim((string) ($data['email'] ?? '')) !== ''
                    ? trim((string) $data['email'])
                    : null;

            $stmt = $connection->prepare($sql);
            $stmt->bindValue(':legal_name', strtoupper((string) $data['legal_name']), PDO::PARAM_STR);
            $stmt->bindValue(':trade_name', strtoupper((string) $data['trade_name']), PDO::PARAM_STR);
            $stmt->bindValue(':cnpj', (string) $data['cnpj'], PDO::PARAM_STR);
            $stmt->bindValue(':contact_name', strtoupper((string) $data['contact_name']), PDO::PARAM_STR);
            $stmt->bindValue(':phone', (string) $data['phone'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, $email === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(
                ':adms_daman_suppliers_types_id',
                (int) $data['adms_daman_suppliers_types_id'],
                PDO::PARAM_INT
            );
            $stmt->bindValue(
                ':supplier_status',
                (int) ($data['supplier_status'] ?? 1),
                PDO::PARAM_INT
            );
            $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':id', (int) $data['id'], PDO::PARAM_INT);
            $stmt->execute();

            $this->syncSupplierTaxIds(
                (int) $data['id'],
                $data
            );

            $connection->commit();

            return true;
        } catch (Exception $e) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            GenerateLog::generateLog(
                'error',
                'Fornecedor não editado.',
                [
                    'id' => (int) ($data['id'] ?? 0),
                    'error' => $e->getMessage(),
                ]
            );

            return false;
        }
    }


    public function validateSupplierTaxIds(
        array $data,
        ?int $supplierId = null
    ): ?string {
        try {
            $taxIdsData =
                $this->extractSupplierTaxIds(
                    $data
                );

            $taxIds = $taxIdsData['all'];

            $placeholders =
                implode(
                    ', ',
                    array_fill(
                        0,
                        count($taxIds),
                        '?'
                    )
                );

            $sql = "
                SELECT
                    supplier_tax_id.cnpj,
                    supplier.legal_name
                FROM
                    adms_daman_supplier_tax_ids AS supplier_tax_id
                INNER JOIN
                    adms_daman_suppliers AS supplier
                    ON supplier.id =
                        supplier_tax_id.adms_daman_supplier_id
                WHERE
                    supplier_tax_id.cnpj IN ({$placeholders})
            ";

            $params = $taxIds;

            if ($supplierId !== null && $supplierId > 0) {
                $sql .= '
                    AND supplier_tax_id.adms_daman_supplier_id <> ?
                ';

                $params[] = $supplierId;
            }

            $sql .= ' LIMIT 1';

            $stmt =
                $this->getConnection()
                    ->prepare(
                        $sql
                    );

            $stmt->execute(
                $params
            );

            $conflict =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );

            if (!$conflict) {
                return null;
            }

            return
                'O CNPJ '
                . $this->formatCnpj(
                    (string) $conflict['cnpj']
                )
                . ' já está vinculado ao fornecedor '
                . (string) $conflict['legal_name']
                . '.';

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    public function findSupplierIdByCnpj(
        string $cnpj
    ): ?int {
        $normalizedCnpj =
            $this->normalizeCnpj(
                $cnpj
            );

        if (strlen($normalizedCnpj) !== 14) {
            return null;
        }

        $sql = '
            SELECT
                adms_daman_supplier_id
            FROM
                adms_daman_supplier_tax_ids
            WHERE
                cnpj = :cnpj
                AND status = 1
            LIMIT 1
        ';

        $stmt =
            $this->getConnection()
                ->prepare(
                    $sql
                );

        $stmt->bindValue(
            ':cnpj',
            $normalizedCnpj,
            PDO::PARAM_STR
        );

        $stmt->execute();

        $supplierId =
            $stmt->fetchColumn();

        if ($supplierId !== false) {
            return (int) $supplierId;
        }

        $sql = "
            SELECT
                id
            FROM
                adms_daman_suppliers
            WHERE
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                cnpj,
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
                ) = :cnpj
            LIMIT 1
        ";

        $stmt =
            $this->getConnection()
                ->prepare(
                    $sql
                );

        $stmt->bindValue(
            ':cnpj',
            $normalizedCnpj,
            PDO::PARAM_STR
        );

        $stmt->execute();

        $supplierId =
            $stmt->fetchColumn();

        return
            $supplierId !== false
                ? (int) $supplierId
                : null;
    }


    public function getSupplierTaxIds(
        int $supplierId
    ): array {
        if ($supplierId <= 0) {
            return [];
        }

        $sql = '
            SELECT
                id,
                cnpj,
                is_primary,
                status
            FROM
                adms_daman_supplier_tax_ids
            WHERE
                adms_daman_supplier_id = :supplier_id
                AND status = 1
            ORDER BY
                is_primary DESC,
                id ASC
        ';

        $stmt =
            $this->getConnection()
                ->prepare(
                    $sql
                );

        $stmt->bindValue(
            ':supplier_id',
            $supplierId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    private function syncSupplierTaxIds(
        int $supplierId,
        array $data
    ): void {
        $taxIdsData =
            $this->extractSupplierTaxIds(
                $data
            );

        $taxIds = $taxIdsData['all'];
        $primaryCnpj = $taxIdsData['primary'];
        $connection = $this->getConnection();

        $validationError =
            $this->validateSupplierTaxIds(
                $data,
                $supplierId
            );

        if ($validationError !== null) {
            throw new Exception(
                $validationError
            );
        }

        $deleteStmt =
            $connection->prepare(
                '
                    DELETE FROM
                        adms_daman_supplier_tax_ids
                    WHERE
                        adms_daman_supplier_id = :supplier_id
                '
            );

        $deleteStmt->bindValue(
            ':supplier_id',
            $supplierId,
            PDO::PARAM_INT
        );

        $deleteStmt->execute();

        $insertStmt =
            $connection->prepare(
                '
                    INSERT INTO adms_daman_supplier_tax_ids
                    (
                        adms_daman_supplier_id,
                        cnpj,
                        is_primary,
                        status,
                        created_at,
                        updated_at
                    )
                    VALUES
                    (
                        :supplier_id,
                        :cnpj,
                        :is_primary,
                        1,
                        NOW(),
                        NOW()
                    )
                '
            );

        foreach ($taxIds as $cnpj) {
            $insertStmt->bindValue(
                ':supplier_id',
                $supplierId,
                PDO::PARAM_INT
            );

            $insertStmt->bindValue(
                ':cnpj',
                $cnpj,
                PDO::PARAM_STR
            );

            $insertStmt->bindValue(
                ':is_primary',
                $cnpj === $primaryCnpj
                    ? 1
                    : 0,
                PDO::PARAM_INT
            );

            $insertStmt->execute();
        }

        $this->relinkPurchaseDocumentsByTaxIds(
            $supplierId,
            $taxIds
        );
    }


    private function relinkPurchaseDocumentsByTaxIds(
        int $supplierId,
        array $taxIds
    ): void {
        if (empty($taxIds)) {
            return;
        }

        $placeholders =
            implode(
                ', ',
                array_fill(
                    0,
                    count($taxIds),
                    '?'
                )
            );

        $sql = "
            UPDATE
                adms_daman_purchase_documents AS purchase_document
            INNER JOIN
                adms_daman_nfes AS nfe
                ON nfe.id =
                    purchase_document.adms_daman_nfe_id
            SET
                purchase_document.adms_daman_supplier_id = ?
            WHERE
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                nfe.issuer_cnpj,
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
                ) IN ({$placeholders})
                AND (
                    purchase_document.adms_daman_supplier_id IS NULL
                    OR purchase_document.adms_daman_supplier_id = ?
                )
        ";

        $params = [
            $supplierId,
            ...$taxIds,
            $supplierId,
        ];

        $stmt =
            $this->getConnection()
                ->prepare(
                    $sql
                );

        $stmt->execute(
            $params
        );
    }


    private function extractSupplierTaxIds(
        array $data
    ): array {
        $primaryCnpj =
            $this->normalizeCnpj(
                (string) (
                    $data['cnpj']
                    ?? ''
                )
            );

        if (strlen($primaryCnpj) !== 14) {
            throw new Exception(
                'Informe um CNPJ principal válido com 14 dígitos.'
            );
        }

        $rawAdditional =
            $data['additional_cnpjs']
            ?? '';

        $additionalItems =
            is_array($rawAdditional)
                ? $rawAdditional
                : (
                    preg_split(
                        '/[\r\n,;]+/',
                        (string) $rawAdditional
                    )
                    ?: []
                );

        $additionalCnpjs = [];

        foreach ($additionalItems as $rawCnpj) {
            $rawCnpj =
                trim(
                    (string) $rawCnpj
                );

            if ($rawCnpj === '') {
                continue;
            }

            $normalizedCnpj =
                $this->normalizeCnpj(
                    $rawCnpj
                );

            if (strlen($normalizedCnpj) !== 14) {
                throw new Exception(
                    'O CNPJ adicional "'
                    . $rawCnpj
                    . '" é inválido.'
                );
            }

            $additionalCnpjs[] =
                $normalizedCnpj;
        }

        $allCnpjs =
            array_values(
                array_unique(
                    [
                        $primaryCnpj,
                        ...$additionalCnpjs,
                    ]
                )
            );

        return [
            'primary' => $primaryCnpj,
            'all' => $allCnpjs,
        ];
    }


    private function normalizeCnpj(
        string $cnpj
    ): string {
        return
            preg_replace(
                '/\D/',
                '',
                $cnpj
            )
            ?? '';
    }


    private function formatCnpj(
        string $cnpj
    ): string {
        $cnpj =
            $this->normalizeCnpj(
                $cnpj
            );

        if (strlen($cnpj) !== 14) {
            return $cnpj;
        }

        return
            substr($cnpj, 0, 2)
            . '.'
            . substr($cnpj, 2, 3)
            . '.'
            . substr($cnpj, 5, 3)
            . '/'
            . substr($cnpj, 8, 4)
            . '-'
            . substr($cnpj, 12, 2);
    }

    /**
     * Deletar Fornecedor pelo ID
     * @param int $id ID do Fornecedor a ser deletado
     * @return bool Sucesso ou falha
     */
    public function deleteSupplier(int $id): bool
    {
        // Usar o try e catch para gerenciar exceção/erro
        try {

            // Query para deletar o fornecedor
            $sql = 'DELETE FROM adms_daman_suppliers WHERE id = :id LIMIT 1';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substiruir os links pelo valor
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();

            // Verificar o número de linhas afetadas
            $affectedRows = $stmt->rowCount();

            if ($affectedRows > 0) {
                return true;
            } else {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Fornecedor não apagado.", ['id' => $id]);
                return false;
            }
        } catch (Exception $e) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Fornecedor não apagado.", ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function getAllSuppliersSelect(): array|bool
    {

        $sql = 'SELECT id, legal_name, cnpj, contact_name, phone
        FROM adms_daman_suppliers
        ORDER BY legal_name ASC';

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a Query
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar fornecedores por tipo.
     *
     * Este método passa a ser a fonte comum para os selects de
     * fornecedores. O tipo é informado explicitamente pelo fluxo
     * chamador, evitando IDs fixos escondidos nas consultas.
     *
     * @param int  $supplierTypeId ID do tipo de fornecedor.
     * @param bool $onlyActive     Quando true, retorna somente ativos.
     *
     * @return array|bool
     */
    public function getAllSuppliersSelectByType(
        int $supplierTypeId,
        bool $onlyActive = true
    ): array|bool {

        $conditions = [
            'adms_daman_suppliers_types_id = :supplier_type_id',
        ];

        if ($onlyActive) {
            $conditions[] = 'supplier_status = :supplier_status';
        }

        $sql = '
            SELECT
                id,
                legal_name,
                trade_name,
                cnpj,
                contact_name,
                phone,
                supplier_status,
                adms_daman_suppliers_types_id
            FROM
                adms_daman_suppliers
            WHERE
                ' . implode(' AND ', $conditions) . '
            ORDER BY
                legal_name ASC
        ';

        $stmt =
            $this->getConnection()
            ->prepare(
                $sql
            );

        $stmt->bindValue(
            ':supplier_type_id',
            $supplierTypeId,
            PDO::PARAM_INT
        );

        if ($onlyActive) {

            $stmt->bindValue(
                ':supplier_status',
                1,
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    /**
     * Recuperar fornecedores ativos utilizados no fluxo de compra.
     *
     * Mantido com o mesmo nome para preservar compatibilidade com
     * os Controllers já existentes em produção.
     */
    public function getAllSuppliersSelectActive(): array|bool
    {
        return $this->getAllSuppliersSelectByType(
            self::TYPE_SALE,
            true
        );
    }


    /**
     * Recuperar todos os fornecedores ativos, independentemente do tipo.
     *
     * Utilizado por despesas diretas, pois esse lançamento pode representar
     * compra, serviço, hospedagem, taxa ou outro desembolso já realizado.
     */
    public function getAllSuppliersSelectAllActive(): array|bool
    {
        $sql = '
            SELECT
                id,
                legal_name,
                trade_name,
                cnpj,
                adms_daman_suppliers_types_id
            FROM
                adms_daman_suppliers
            WHERE
                supplier_status = 1
            ORDER BY
                legal_name ASC
        ';

        $stmt =
            $this->getConnection()
                ->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    public function getAllTypesSuppliersSelect(): array|bool
    {

        $sql = 'SELECT id, name
        FROM adms_daman_suppliers_types
        ORDER BY name ASC';

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a Query
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Recuperar fornecedores utilizados no fluxo de locação.
     *
     * IMPORTANTE:
     * O método legado não filtrava supplier_status.
     * Para não alterar silenciosamente o comportamento atual da
     * produção, esse detalhe foi preservado aqui.
     *
     * @return array|bool Fornecedor recuperado do banco de dados
     */
    public function getSupplierTypeSelect(): array|bool
    {
        return $this->getAllSuppliersSelectByType(
            self::TYPE_RENTAL,
            false
        );
    }
}
