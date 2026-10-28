<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use PDO;
use PDOException;

/**
 * Persistência e consultas da renegociação financeira.
 *
 * A renegociação não substitui fisicamente as parcelas antigas: ela cria um
 * novo lançamento e registra quais parcelas/saldos foram consolidados nele.
 */
class FinancialRenegotiationsRepository extends DbConnection
{
    /**
     * Fornecedores ativos que possuem ao menos uma parcela elegível.
     *
     * Regra funcional atual:
     * - OK nunca aparece;
     * - RN não aparece porque já foi incorporada a outra renegociação;
     * - parcela sem saldo também não aparece;
     * - AV/AT/ON/AP podem aparecer.
     */
    public function getSuppliersWithEligibleInstallments(): array
    {
        try {
            $sql = "
                SELECT DISTINCT
                    supplier.id,
                    supplier.legal_name,
                    supplier.cnpj
                FROM adms_daman_suppliers supplier
                INNER JOIN adms_daman_purchase_documents pd
                    ON pd.adms_daman_supplier_id = supplier.id
                INNER JOIN adms_daman_purchase_installments pi
                    ON pi.adms_daman_purchase_document_id = pd.id
                LEFT JOIN (
                    SELECT
                        adms_daman_purchase_installment_id,
                        SUM(principal_amount) AS principal_paid
                    FROM adms_daman_purchase_installment_payments
                    WHERE status = 'active'
                    GROUP BY adms_daman_purchase_installment_id
                ) payments
                    ON payments.adms_daman_purchase_installment_id = pi.id
                WHERE supplier.supplier_status = 1
                  AND pi.status NOT IN ('OK', 'RN')
                  AND GREATEST(
                        pi.original_amount - COALESCE(payments.principal_paid, 0),
                        0
                      ) > 0
                  AND NOT EXISTS (
                        SELECT 1
                        FROM adms_daman_financial_renegotiation_origins origin
                        INNER JOIN adms_daman_financial_renegotiations renegotiation
                            ON renegotiation.id = origin.adms_daman_financial_renegotiation_id
                        WHERE origin.adms_daman_purchase_installment_id = pi.id
                          AND renegotiation.status = 'active'
                  )
                ORDER BY supplier.legal_name ASC
            ";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $err) {
            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar fornecedores disponíveis para renegociação.',
                ['error' => $err->getMessage()]
            );

            throw $err;
        }
    }

    /**
     * Parcelas de um fornecedor disponíveis para seleção na tela.
     */
    public function getEligibleInstallmentsBySupplier(int $supplierId): array
    {
        if ($supplierId <= 0) {
            return [];
        }

        try {
            $sql = "
                SELECT
                    pi.id,
                    pi.adms_daman_purchase_document_id,
                    pi.installment_number,
                    pi.due_date,
                    pi.original_amount,
                    pi.status AS stored_status,
                    COALESCE(payments.principal_paid, 0) AS principal_paid,
                    GREATEST(
                        pi.original_amount - COALESCE(payments.principal_paid, 0),
                        0
                    ) AS remaining_amount,
                    CASE
                        WHEN pi.status = 'AP' THEN 'AP'
                        WHEN pi.due_date IS NULL THEN pi.status
                        WHEN pi.due_date < CURDATE() THEN 'ON'
                        WHEN pi.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'AT'
                        ELSE 'AV'
                    END AS display_status,
                    pd.adms_daman_supplier_id,
                    pd.adms_daman_project_id,
                    pd.financial_entry_type,
                    pd.document_type,
                    pd.document_number AS manual_document_number,
                    COALESCE(nfe.nfe_number, pd.document_number) AS document_number,
                    CASE
                        WHEN pd.adms_daman_nfe_id IS NOT NULL THEN 'NF-e'
                        WHEN pd.financial_entry_type = 'financial_obligation' THEN 'Obrigação Financeira'
                        WHEN pd.financial_entry_type = 'renegotiation' THEN 'Renegociação'
                        ELSE COALESCE(pd.document_type, 'Documento')
                    END AS document_type_label,
                    COALESCE(nfe.total_value, pd.total_value, 0) AS document_total,
                    GROUP_CONCAT(
                        DISTINCT project.name
                        ORDER BY project.name
                        SEPARATOR ', '
                    ) AS project_names
                FROM adms_daman_purchase_installments pi
                INNER JOIN adms_daman_purchase_documents pd
                    ON pd.id = pi.adms_daman_purchase_document_id
                LEFT JOIN adms_daman_nfes nfe
                    ON nfe.id = pd.adms_daman_nfe_id
                LEFT JOIN (
                    SELECT
                        adms_daman_purchase_installment_id,
                        SUM(principal_amount) AS principal_paid
                    FROM adms_daman_purchase_installment_payments
                    WHERE status = 'active'
                    GROUP BY adms_daman_purchase_installment_id
                ) payments
                    ON payments.adms_daman_purchase_installment_id = pi.id
                LEFT JOIN adms_daman_purchase_document_allocations allocation
                    ON allocation.adms_daman_purchase_document_id = pd.id
                LEFT JOIN adms_daman_projects project
                    ON project.id = allocation.adms_daman_project_id
                WHERE pd.adms_daman_supplier_id = :supplier_id
                  AND pi.status NOT IN ('OK', 'RN')
                  AND GREATEST(
                        pi.original_amount - COALESCE(payments.principal_paid, 0),
                        0
                      ) > 0
                  AND NOT EXISTS (
                        SELECT 1
                        FROM adms_daman_financial_renegotiation_origins origin
                        INNER JOIN adms_daman_financial_renegotiations renegotiation
                            ON renegotiation.id = origin.adms_daman_financial_renegotiation_id
                        WHERE origin.adms_daman_purchase_installment_id = pi.id
                          AND renegotiation.status = 'active'
                  )
                GROUP BY
                    pi.id,
                    pi.adms_daman_purchase_document_id,
                    pi.installment_number,
                    pi.due_date,
                    pi.original_amount,
                    pi.status,
                    payments.principal_paid,
                    pd.adms_daman_supplier_id,
                    pd.adms_daman_project_id,
                    pd.financial_entry_type,
                    pd.document_type,
                    pd.document_number,
                    pd.adms_daman_nfe_id,
                    nfe.nfe_number,
                    nfe.total_value,
                    pd.total_value
                ORDER BY
                    CASE WHEN pi.due_date IS NULL THEN 1 ELSE 0 END,
                    pi.due_date ASC,
                    document_number ASC,
                    pi.installment_number ASC
            ";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':supplier_id', $supplierId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $err) {
            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar parcelas disponíveis para renegociação.',
                [
                    'supplier_id' => $supplierId,
                    'error' => $err->getMessage(),
                ]
            );

            throw $err;
        }
    }

    /**
     * Recupera e bloqueia as parcelas selecionadas para garantir que o saldo
     * não seja alterado entre a validação e a confirmação do acordo.
     */
    public function lockEligibleInstallments(
        array $installmentIds,
        int $supplierId
    ): array {
        $ids = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $installmentIds),
                    static fn(int $id): bool => $id > 0
                )
            )
        );

        if (empty($ids) || $supplierId <= 0) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        try {
            $sql = "
                SELECT
                    pi.id,
                    pi.adms_daman_purchase_document_id,
                    pi.installment_number,
                    pi.due_date,
                    pi.original_amount,
                    pi.status,
                    COALESCE(payments.principal_paid, 0) AS principal_paid,
                    GREATEST(
                        pi.original_amount - COALESCE(payments.principal_paid, 0),
                        0
                    ) AS remaining_amount,
                    pd.adms_daman_supplier_id,
                    pd.adms_daman_project_id,
                    COALESCE(nfe.nfe_number, pd.document_number) AS document_number,
                    COALESCE(nfe.total_value, pd.total_value, 0) AS document_total
                FROM adms_daman_purchase_installments pi
                INNER JOIN adms_daman_purchase_documents pd
                    ON pd.id = pi.adms_daman_purchase_document_id
                LEFT JOIN adms_daman_nfes nfe
                    ON nfe.id = pd.adms_daman_nfe_id
                LEFT JOIN (
                    SELECT
                        adms_daman_purchase_installment_id,
                        SUM(principal_amount) AS principal_paid
                    FROM adms_daman_purchase_installment_payments
                    WHERE status = 'active'
                    GROUP BY adms_daman_purchase_installment_id
                ) payments
                    ON payments.adms_daman_purchase_installment_id = pi.id
                WHERE pi.id IN ({$placeholders})
                  AND pd.adms_daman_supplier_id = ?
                  AND pi.status NOT IN ('OK', 'RN')
                  AND GREATEST(
                        pi.original_amount - COALESCE(payments.principal_paid, 0),
                        0
                      ) > 0
                  AND NOT EXISTS (
                        SELECT 1
                        FROM adms_daman_financial_renegotiation_origins origin
                        INNER JOIN adms_daman_financial_renegotiations renegotiation
                            ON renegotiation.id = origin.adms_daman_financial_renegotiation_id
                        WHERE origin.adms_daman_purchase_installment_id = pi.id
                          AND renegotiation.status = 'active'
                  )
                ORDER BY pi.id ASC
                FOR UPDATE
            ";

            $stmt = $this->getConnection()->prepare($sql);

            $position = 1;
            foreach ($ids as $id) {
                $stmt->bindValue($position++, $id, PDO::PARAM_INT);
            }
            $stmt->bindValue($position, $supplierId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $err) {
            GenerateLog::generateLog(
                'error',
                'Erro ao bloquear parcelas da renegociação.',
                [
                    'supplier_id' => $supplierId,
                    'installment_ids' => $ids,
                    'error' => $err->getMessage(),
                ]
            );

            throw $err;
        }
    }

    /**
     * Rateios dos documentos de origem.
     */
    public function getAllocationsByDocumentIds(array $documentIds): array
    {
        $ids = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $documentIds),
                    static fn(int $id): bool => $id > 0
                )
            )
        );

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "
            SELECT
                allocation.adms_daman_purchase_document_id,
                allocation.adms_daman_project_id,
                allocation.allocated_amount
            FROM adms_daman_purchase_document_allocations allocation
            WHERE allocation.adms_daman_purchase_document_id IN ({$placeholders})
            ORDER BY allocation.adms_daman_purchase_document_id, allocation.id
        ";

        $stmt = $this->getConnection()->prepare($sql);
        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $sql = "
            INSERT INTO adms_daman_financial_renegotiations
            (
                adms_daman_supplier_id,
                adms_daman_purchase_document_id,
                agreement_number,
                agreement_date,
                original_amount,
                interest_amount,
                penalty_amount,
                discount_amount,
                renegotiated_amount,
                status,
                observation,
                created_by,
                created_at,
                updated_at
            )
            VALUES
            (
                :supplier_id,
                :purchase_document_id,
                :agreement_number,
                :agreement_date,
                :original_amount,
                :interest_amount,
                :penalty_amount,
                :discount_amount,
                :renegotiated_amount,
                'active',
                :observation,
                :created_by,
                NOW(),
                NOW()
            )
        ";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':supplier_id', (int) $data['adms_daman_supplier_id'], PDO::PARAM_INT);
        $stmt->bindValue(':purchase_document_id', (int) $data['adms_daman_purchase_document_id'], PDO::PARAM_INT);
        $stmt->bindValue(':agreement_number', (string) $data['agreement_number'], PDO::PARAM_STR);
        $stmt->bindValue(':agreement_date', (string) $data['agreement_date'], PDO::PARAM_STR);
        $stmt->bindValue(':original_amount', (string) $data['original_amount'], PDO::PARAM_STR);
        $stmt->bindValue(':interest_amount', (string) $data['interest_amount'], PDO::PARAM_STR);
        $stmt->bindValue(':penalty_amount', (string) $data['penalty_amount'], PDO::PARAM_STR);
        $stmt->bindValue(':discount_amount', (string) $data['discount_amount'], PDO::PARAM_STR);
        $stmt->bindValue(':renegotiated_amount', (string) $data['renegotiated_amount'], PDO::PARAM_STR);
        $stmt->bindValue(
            ':observation',
            !empty($data['observation']) ? (string) $data['observation'] : null,
            !empty($data['observation']) ? PDO::PARAM_STR : PDO::PARAM_NULL
        );
        $stmt->bindValue(':created_by', (int) $data['created_by'], PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->getConnection()->lastInsertId();
    }

    public function createOrigins(int $renegotiationId, array $origins): bool
    {
        if ($renegotiationId <= 0 || empty($origins)) {
            return false;
        }

        $sql = "
            INSERT INTO adms_daman_financial_renegotiation_origins
            (
                adms_daman_financial_renegotiation_id,
                adms_daman_purchase_installment_id,
                source_original_amount,
                source_paid_principal,
                included_principal_amount,
                previous_status,
                created_at
            )
            VALUES
            (
                :renegotiation_id,
                :installment_id,
                :source_original_amount,
                :source_paid_principal,
                :included_principal_amount,
                :previous_status,
                NOW()
            )
        ";

        $stmt = $this->getConnection()->prepare($sql);

        foreach ($origins as $origin) {
            $stmt->bindValue(':renegotiation_id', $renegotiationId, PDO::PARAM_INT);
            $stmt->bindValue(':installment_id', (int) $origin['id'], PDO::PARAM_INT);
            $stmt->bindValue(':source_original_amount', (string) $origin['original_amount'], PDO::PARAM_STR);
            $stmt->bindValue(':source_paid_principal', (string) $origin['principal_paid'], PDO::PARAM_STR);
            $stmt->bindValue(':included_principal_amount', (string) $origin['remaining_amount'], PDO::PARAM_STR);
            $stmt->bindValue(':previous_status', (string) $origin['status'], PDO::PARAM_STR);
            $stmt->execute();
        }

        return true;
    }

    /**
     * Marca as parcelas antigas como RN sem apagá-las ou registrá-las como pagas.
     */
    public function markInstallmentsRenegotiated(array $installmentIds): int
    {
        $ids = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $installmentIds),
                    static fn(int $id): bool => $id > 0
                )
            )
        );

        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "
            UPDATE adms_daman_purchase_installments
            SET status = 'RN', updated_at = NOW()
            WHERE id IN ({$placeholders})
              AND status NOT IN ('OK', 'RN')
        ";

        $stmt = $this->getConnection()->prepare($sql);
        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Fecha documentos de origem que não possuem mais nenhuma parcela ativa.
     * RN representa dívida transferida para outro lançamento, não pagamento.
     */
    public function closeSourceDocumentsWithoutOpenInstallments(array $documentIds): void
    {
        $ids = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $documentIds),
                    static fn(int $id): bool => $id > 0
                )
            )
        );

        if (empty($ids)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "
            UPDATE adms_daman_purchase_documents pd
            SET pd.status = 'closed', pd.updated_at = NOW()
            WHERE pd.id IN ({$placeholders})
              AND NOT EXISTS (
                    SELECT 1
                    FROM adms_daman_purchase_installments pi
                    WHERE pi.adms_daman_purchase_document_id = pd.id
                      AND pi.status NOT IN ('OK', 'RN')
              )
        ";

        $stmt = $this->getConnection()->prepare($sql);
        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }
        $stmt->execute();
    }

    /**
     * Cabeçalho da renegociação vinculada a um lançamento financeiro.
     */
    public function getByPurchaseDocumentId(int $purchaseDocumentId): array|bool
    {
        $sql = "
            SELECT
                renegotiation.*,
                supplier.legal_name AS supplier_name,
                supplier.cnpj AS supplier_cnpj,
                creator.name AS created_by_name
            FROM adms_daman_financial_renegotiations renegotiation
            INNER JOIN adms_daman_suppliers supplier
                ON supplier.id = renegotiation.adms_daman_supplier_id
            INNER JOIN adms_daman_users creator
                ON creator.id = renegotiation.created_by
            WHERE renegotiation.adms_daman_purchase_document_id = :purchase_document_id
            LIMIT 1
        ";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':purchase_document_id', $purchaseDocumentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar o acordo que absorveu cada parcela de origem.
     *
     * Mantém a rastreabilidade também no sentido inverso: ao abrir a nota
     * original, uma parcela RN consegue apontar para a renegociação que a
     * substituiu financeiramente.
     */
    public function getBySourceInstallmentIds(array $installmentIds): array
    {
        $ids = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $installmentIds),
                    static fn(int $id): bool => $id > 0
                )
            )
        );

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "
            SELECT
                origin.adms_daman_purchase_installment_id,
                renegotiation.id AS renegotiation_id,
                renegotiation.adms_daman_purchase_document_id AS renegotiation_purchase_document_id,
                renegotiation.agreement_number,
                renegotiation.agreement_date,
                renegotiation.status AS renegotiation_status
            FROM adms_daman_financial_renegotiation_origins origin
            INNER JOIN adms_daman_financial_renegotiations renegotiation
                ON renegotiation.id = origin.adms_daman_financial_renegotiation_id
            WHERE origin.adms_daman_purchase_installment_id IN ({$placeholders})
            ORDER BY renegotiation.id DESC
        ";

        $stmt = $this->getConnection()->prepare($sql);

        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }

        $stmt->execute();

        $result = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $installmentId =
                (int) ($row['adms_daman_purchase_installment_id'] ?? 0);

            if ($installmentId > 0 && !isset($result[$installmentId])) {
                $result[$installmentId] = $row;
            }
        }

        return $result;
    }

    public function getOriginsByRenegotiationId(int $renegotiationId): array
    {
        if ($renegotiationId <= 0) {
            return [];
        }

        $sql = "
            SELECT
                origin.id,
                origin.adms_daman_purchase_installment_id,
                origin.source_original_amount,
                origin.source_paid_principal,
                origin.included_principal_amount,
                origin.previous_status,
                pi.installment_number,
                pi.due_date,
                pd.id AS source_purchase_document_id,
                COALESCE(nfe.nfe_number, pd.document_number) AS document_number,
                CASE
                    WHEN pd.adms_daman_nfe_id IS NOT NULL THEN 'NF-e'
                    WHEN pd.financial_entry_type = 'financial_obligation' THEN 'Obrigação Financeira'
                    WHEN pd.financial_entry_type = 'renegotiation' THEN 'Renegociação'
                    ELSE COALESCE(pd.document_type, 'Documento')
                END AS document_type_label
            FROM adms_daman_financial_renegotiation_origins origin
            INNER JOIN adms_daman_purchase_installments pi
                ON pi.id = origin.adms_daman_purchase_installment_id
            INNER JOIN adms_daman_purchase_documents pd
                ON pd.id = pi.adms_daman_purchase_document_id
            LEFT JOIN adms_daman_nfes nfe
                ON nfe.id = pd.adms_daman_nfe_id
            WHERE origin.adms_daman_financial_renegotiation_id = :renegotiation_id
            ORDER BY document_number, pi.installment_number
        ";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':renegotiation_id', $renegotiationId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
