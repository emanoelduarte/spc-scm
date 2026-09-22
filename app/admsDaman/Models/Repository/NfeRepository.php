<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use PDO;
use PDOException;

/**
 * Repository responsável pelas operações
 * relacionadas às NF-e no banco de dados.
 */
class NfeRepository extends DbConnection
{

    /**
     * Listar as NF-e cadastradas.
     *
     * @return array
     */
    public function getAllNfes(): array
    {
        $conn = $this->getConnection();

        $sql = "
        SELECT
            nfe.id,
            nfe.nsu,
            nfe.access_key,
            nfe.nfe_number,
            nfe.series,
            nfe.issuer_cnpj,
            nfe.issuer_name,
            nfe.issue_date,
            nfe.total_value,
            nfe.status,
            nfe.cancelled_at,
            nfe.is_checked,

            pd.id AS purchase_document_id

        FROM adms_daman_nfes AS nfe

        LEFT JOIN adms_daman_purchase_documents pd
        ON pd.adms_daman_nfe_id = nfe.id

        ORDER BY nfe.issue_date DESC, nfe.id DESC
    ";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar os dados atuais de sincronização do CNPJ.
     *
     * @param string $cnpj
     * @return array|null
     */
    public function getSyncData(string $cnpj): ?array
    {
        $conn = $this->getConnection();

        $sql = "
            SELECT
                ult_nsu,
                max_nsu,
                last_cstat,
                last_message,
                last_sync_at
            FROM adms_daman_nfe_sync
            WHERE cnpj = :cnpj
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':cnpj' => $cnpj
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Atualizar os dados de sincronização do CNPJ.
     */
    public function updateSyncData(
        string $cnpj,
        string $ultNsu,
        string $maxNsu,
        ?string $cStat,
        ?string $message
    ): bool {
        $conn = $this->getConnection();

        $sql = "
        UPDATE adms_daman_nfe_sync
        SET
            ult_nsu = :ult_nsu,
            max_nsu = :max_nsu,
            last_cstat = :last_cstat,
            last_message = :last_message,
            last_sync_at = NOW()
        WHERE cnpj = :cnpj
    ";

        $stmt = $conn->prepare($sql);

        return $stmt->execute([
            ':ult_nsu' => $ultNsu,
            ':max_nsu' => $maxNsu,
            ':last_cstat' => $cStat,
            ':last_message' => $message,
            ':cnpj' => $cnpj,
        ]);
    }

    /**
     * Verificar se uma nova consulta à SEFAZ pode ser realizada.
     */
    public function canSynchronize(string $cnpj): bool
    {
        $conn = $this->getConnection();

        $sql = "
        SELECT
            CASE

                -- Nunca consultou.
                WHEN last_sync_at IS NULL THEN 1

                -- Ainda existem NSUs pendentes.
                WHEN last_cstat = '138'
                    AND ult_nsu <> max_nsu
                THEN 1

                -- Caso contrário, aguardar 1 hora.
                WHEN TIMESTAMPDIFF(
                    SECOND,
                    last_sync_at,
                    NOW()
                ) >= 3600
                THEN 1

                ELSE 0

            END AS can_sync

        FROM adms_daman_nfe_sync

        WHERE cnpj = :cnpj

        LIMIT 1
    ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':cnpj' => $cnpj
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($result['can_sync'])
            && (int) $result['can_sync'] === 1;
    }

    /**
     * Cadastrar uma NF-e localizada na distribuição DF-e.
     *
     * @param array $data Dados da NF-e
     * @return bool
     */
    public function createNfe(array $data): bool
    {
        $conn = $this->getConnection();

        $sql = "
        INSERT INTO adms_daman_nfes (
            nsu,
            access_key,
            nfe_number,
            series,
            issuer_cnpj,
            issuer_name,
            issue_date,
            total_value,
            schema_name,
            status
        ) VALUES (
            :nsu,
            :access_key,
            :nfe_number,
            :series,
            :issuer_cnpj,
            :issuer_name,
            :issue_date,
            :total_value,
            :schema_name,
            :status
        )
        ON DUPLICATE KEY UPDATE
            issuer_name = VALUES(issuer_name),
            issue_date = VALUES(issue_date),
            total_value = VALUES(total_value),
            status = VALUES(status)
    ";

        $stmt = $conn->prepare($sql);

        return $stmt->execute([
            ':nsu'          => $data['nsu'],
            ':access_key'   => $data['access_key'],
            ':nfe_number'   => $data['nfe_number'],
            ':series'       => $data['series'],
            ':issuer_cnpj'  => $data['issuer_cnpj'],
            ':issuer_name'  => $data['issuer_name'],
            ':issue_date'   => $data['issue_date'],
            ':total_value'  => $data['total_value'],
            ':schema_name'  => $data['schema_name'],
            ':status'       => $data['status'],
        ]);
    }

    /**
     * Marcar uma NF-e como conferida.
     *
     * @param int $id ID da NF-e
     * @return bool
     */
    public function markAsChecked(int $id): bool
    {
        $conn = $this->getConnection();

        $sql = "
        UPDATE adms_daman_nfes
        SET is_checked = 1
        WHERE id = :id
    ";

        $stmt = $conn->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    /**
     * Marcar uma NF-e como não conferida.
     *
     * @param int $id ID da NF-e
     * @return bool
     */
    public function markAsUnchecked(int $id): bool
    {
        $conn = $this->getConnection();

        $sql = "
        UPDATE adms_daman_nfes
        SET is_checked = 0
        WHERE id = :id
    ";

        $stmt = $conn->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    /**
     * Atualizar o status de uma NF-e pela chave de acesso.
     *
     * @param string $accessKey Chave de acesso da NF-e
     * @param string $status Novo status
     * @param string|null $cancelledAt Data/hora do cancelamento
     * @return bool
     */
    public function updateNfeStatus(
        string $accessKey,
        string $status,
        ?string $cancelledAt = null
    ): bool {
        $conn = $this->getConnection();

        $sql = "
        UPDATE adms_daman_nfes
        SET
            status = :status,
            cancelled_at = :cancelled_at
        WHERE access_key = :access_key
    ";

        $stmt = $conn->prepare($sql);

        return $stmt->execute([
            ':status' => $status,
            ':cancelled_at' => $cancelledAt,
            ':access_key' => $accessKey,
        ]);
    }

    /**
     * Recuperar uma NF-e pelo ID.
     *
     * @param int $id ID da NF-e
     * @return array|bool
     */
    public function getNfeById(int $id): array|bool
    {
        try {

            $sql = 'SELECT
                    id,
                    nsu,
                    access_key,
                    nfe_number,
                    series,
                    issuer_cnpj,
                    issuer_name,
                    issue_date,
                    total_value,
                    status,
                    cancelled_at,
                    is_checked
                FROM adms_daman_nfes
                WHERE id = :id
                LIMIT 1';

            $stmt = $this->getConnection()->prepare($sql);

            $stmt->bindValue(
                ':id',
                $id,
                PDO::PARAM_INT
            );

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar NF-e.',
                [
                    'nfe_id' => $id,
                    'error' => $err->getMessage(),
                ]
            );

            throw $err;
        }
    }
}
