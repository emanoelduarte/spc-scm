<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use PDO;
use PDOException;

class PurchaseInstallmentsRepository extends DbConnection
{
    /**
     * Criar as parcelas de um lançamento.
     */
    public function createMany(int $purchaseDocumentId, array $installments): bool
    {
        try {

            $sql = 'INSERT INTO adms_daman_purchase_installments (
                    adms_daman_purchase_document_id,
                    installment_number,
                    due_date,
                    original_amount,
                    status,
                    observation
                ) VALUES (
                    :adms_daman_purchase_document_id,
                    :installment_number,
                    :due_date,
                    :original_amount,
                    :status,
                    :observation
                )';

            $stmt = $this->getConnection()->prepare($sql);

            foreach ($installments as $installment) {

                $stmt->execute([
                    ':adms_daman_purchase_document_id' =>
                    $purchaseDocumentId,

                    ':installment_number' =>
                    $installment['installment_number'],

                    ':due_date' =>
                    $installment['due_date'] ?? null,

                    ':original_amount' =>
                    $installment['original_amount'],

                    ':status' =>
                    $installment['status'] ?? 'AV',

                    ':observation' =>
                    $installment['observation'] ?? null,
                ]);
            }

            return true;
        } catch (PDOException $err) {

            GenerateLog::generateLog(
                "error",
                "Erro ao criar parcelas do lançamento",
                [
                    'purchase_document_id' => $purchaseDocumentId,
                    'error' => $err->getMessage(),
                ]
            );

            throw $err;
        }
    }

    /**
     * Recuperar todas as parcelas de um lançamento financeiro.
     *
     * Cada lançamento existente em
     * adms_daman_purchase_documents pode possuir uma ou várias
     * parcelas em adms_daman_purchase_installments.
     *
     * As parcelas são retornadas ordenadas pelo número da parcela,
     * garantindo a apresentação na sequência:
     *
     * 1ª parcela
     * 2ª parcela
     * 3ª parcela
     * ...
     *
     * @param int $purchaseDocumentId
     * ID do lançamento financeiro ao qual as parcelas pertencem.
     *
     * @return array
     * Retorna um array contendo todas as parcelas encontradas.
     * Caso não existam parcelas, retorna um array vazio.
     *
     * @throws \PDOException
     * Relança a exceção caso ocorra erro na consulta ao banco.
     */
    public function getByPurchaseDocumentId(
        int $purchaseDocumentId
    ): array {

        try {

            /*
         * Recuperar as parcelas pertencentes
         * ao lançamento financeiro informado.
         */
            $query = "
            SELECT

                /*
                 * ID interno da parcela.
                 */
                id,


                /*
                 * Número sequencial da parcela.
                 *
                 * Exemplo:
                 * 1, 2, 3...
                 */
                installment_number,


                /*
                 * Data de vencimento.
                 *
                 * Pode ser NULL quando o status for AP
                 * (Permuta com vencimento indefinido).
                 */
                due_date,


                /*
                 * Valor original da parcela.
                 */
                original_amount,


                /*
                 * Situação da parcela.
                 *
                 * AV = A vencer
                 * AT = Atenção
                 * ON = Em aberto / vencida
                 * OK = Pago
                 * AP = Permuta
                 */
                status,


                /*
                 * Observação específica da parcela.
                 */
                observation,


                /*
                 * Datas de controle do registro.
                 */
                created_at,
                updated_at


            FROM adms_daman_purchase_installments


            /*
             * Filtrar somente as parcelas pertencentes
             * ao lançamento solicitado.
             */
            WHERE adms_daman_purchase_document_id =
                :purchase_document_id


            /*
             * Garantir que as parcelas sejam retornadas
             * sempre na ordem correta.
             */
            ORDER BY installment_number ASC
        ";


            /*
         * Preparar a consulta.
         */
            $stmt = $this->getConnection()->prepare(
                $query
            );


            /*
         * Vincular o ID do lançamento financeiro.
         */
            $stmt->bindValue(
                ':purchase_document_id',
                $purchaseDocumentId,
                \PDO::PARAM_INT
            );


            /*
         * Executar a consulta.
         */
            $stmt->execute();


            /*
         * Como um lançamento pode possuir várias parcelas,
         * utilizamos fetchAll().
         *
         * Caso não exista nenhuma parcela,
         * o retorno será um array vazio.
         */
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $err) {

            /*
         * Registrar o erro para diagnóstico.
         */
            GenerateLog::generateLog(
                "error",
                'Erro ao recuperar parcelas do lançamento: ',
                ['Error' => $err->getMessage()]
            );

            /*
         * Relançar para que a Controller/Service
         * possa tratar o erro adequadamente.
         */
            throw $err;
        }
    }

    /**
     * Recuperar as parcelas de vários lançamentos financeiros.
     *
     * Este método é utilizado principalmente na listagem geral
     * de compras, evitando executar uma consulta separada para
     * cada lançamento financeiro.
     *
     * Exemplo:
     *
     * Lançamentos solicitados:
     * [1, 3, 8]
     *
     * O método retorna todas as parcelas pertencentes
     * a esses lançamentos em uma única consulta.
     *
     * @param array $purchaseDocumentIds
     * IDs dos lançamentos financeiros.
     *
     * @return array
     * Retorna todas as parcelas encontradas.
     *
     * @throws PDOException
     * Relança a exceção caso ocorra erro na consulta.
     */
    public function getByPurchaseDocumentIds(array $purchaseDocumentIds): array
    {

        /*
        * Caso nenhum lançamento tenha sido informado,
        * não há necessidade de consultar o banco.
        */
        if (empty($purchaseDocumentIds)) {
            return [];
        }

        try {

            /*
         * Garantir que todos os IDs sejam inteiros
         * válidos antes de montar a consulta.
         */
            $purchaseDocumentIds = array_values(
                array_filter(
                    array_map(
                        'intval',
                        $purchaseDocumentIds
                    ),
                    fn($id) => $id > 0
                )
            );


            if (empty($purchaseDocumentIds)) {
                return [];
            }


            /*
         * Criar os parâmetros dinamicamente.
         *
         * Exemplo:
         *
         * [1, 3]
         *
         * vira:
         *
         * :id0, :id1
         */
            $placeholders = [];

            foreach (
                $purchaseDocumentIds
                as $index => $id
            ) {

                $placeholders[] =
                    ':id' . $index;
            }


            /*
         * Recuperar todas as parcelas dos
         * lançamentos informados.
         */
            $query = "
                SELECT

                    pi.id,

                    /*
                    * ID do lançamento ao qual
                    * a parcela pertence.
                    */
                    pi.adms_daman_purchase_document_id,

                    pi.installment_number,
                    pi.due_date,
                    pi.original_amount,
                    pi.status,
                    pi.observation,
                    pi.created_at,
                    pi.updated_at,


                    /*
                    * =====================================================
                    * VALOR PRINCIPAL JÁ PAGO
                    * =====================================================
                    *
                    * Considerar somente pagamentos ativos.
                    *
                    * Pagamentos estornados não entram
                    * no cálculo.
                    */
                    COALESCE(
                        payments.principal_paid,
                        0
                    ) AS principal_paid,


                    /*
                    * =====================================================
                    * SALDO PRINCIPAL DA PARCELA
                    * =====================================================
                    *
                    * Valor original
                    * menos
                    * principal já pago.
                    *
                    * GREATEST evita retornar valor negativo.
                    */
                    GREATEST(
                        pi.original_amount
                        - COALESCE(
                            payments.principal_paid,
                            0
                        ),
                        0
                    ) AS remaining_principal


                FROM
                    adms_daman_purchase_installments pi


                /*
                * =========================================================
                * TOTAL DE PAGAMENTOS ATIVOS POR PARCELA
                * =========================================================
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

                    ON payments.adms_daman_purchase_installment_id
                        = pi.id


                WHERE
                    pi.adms_daman_purchase_document_id IN (
                        " . implode(',', $placeholders) . "
                    )


                ORDER BY
                    pi.adms_daman_purchase_document_id ASC,
                    pi.installment_number ASC
            ";


            /*
            * Preparar a consulta.
            */
            $stmt =
                $this->getConnection()
                ->prepare($query);


            /*
            * Vincular cada ID ao seu respectivo
            * parâmetro da consulta.
            */
            foreach (
                $purchaseDocumentIds
                as $index => $id
            ) {

                $stmt->bindValue(
                    ':id' . $index,
                    $id,
                    \PDO::PARAM_INT
                );
            }


            /*
            * Executar a consulta.
            */
            $stmt->execute();


            return $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
        } catch (PDOException $err) {

            GenerateLog::generateLog(
                "error",
                'Erro ao recuperar parcelas dos lançamentos: ',
                ["error" => $err->getMessage()]

            );

            throw $err;
        }
    }

    /**
     * Recuperar uma parcela pelo ID.
     *
     * @param int $id ID da parcela.
     * @return array|bool Dados da parcela ou false.
     */
    public function getById(int $id): array|bool
    {
        try {

            $sql = "
                SELECT
                    id,
                    adms_daman_purchase_document_id,
                    installment_number,
                    due_date,
                    original_amount,
                    status,
                    observation

                FROM
                    adms_daman_purchase_installments

                WHERE
                    id = :id

                LIMIT 1
            ";

            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );

            $stmt->bindValue(
                ':id',
                $id,
                PDO::PARAM_INT
            );

            $stmt->execute();

            return $stmt->fetch(
                PDO::FETCH_ASSOC
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar parcela da compra.',
                [
                    'installment_id' => $id,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Recuperar o saldo total de principal ainda em aberto
     * de todas as parcelas de um lançamento financeiro.
     *
     * São considerados somente pagamentos ativos.
     * Pagamentos estornados não reduzem o saldo.
     *
     * @param int $purchaseDocumentId ID do lançamento financeiro.
     *
     * @return float Saldo principal ainda existente.
     *
     * @throws PDOException
     */
    public function getRemainingPrincipalByDocumentId(
        int $purchaseDocumentId
    ): float {

        try {

            $sql = "
            SELECT
                COALESCE(
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
                ) AS remaining_principal

            FROM
                adms_daman_purchase_installments pi

            LEFT JOIN (

                /*
                 * Somar somente pagamentos ativos.
                 *
                 * Pagamentos estornados permanecem no histórico,
                 * mas não liquidam mais a obrigação.
                 */
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

                ON payments.adms_daman_purchase_installment_id
                    = pi.id

            WHERE
                pi.adms_daman_purchase_document_id
                    = :purchase_document_id
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


            $result =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );


            return (float) (
                $result['remaining_principal']
                ?? 0
            );
        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar saldo do lançamento financeiro.',
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


    /**
     * Atualizar o status de uma parcela.
     *
     * @param int $installmentId ID da parcela.
     * @param string $status Novo status.
     * @return bool
     */
    public function updateStatus(
        int $installmentId,
        string $status
    ): bool {

        try {

            $sql = "
                UPDATE
                    adms_daman_purchase_installments

                SET
                    status = :status,
                    updated_at = :updated_at

                WHERE
                    id = :id
            ";

            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );

            $stmt->bindValue(
                ':status',
                $status,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':updated_at',
                date('Y-m-d H:i:s'),
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':id',
                $installmentId,
                PDO::PARAM_INT
            );

            return $stmt->execute();
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao atualizar status da parcela.',
                [
                    'installment_id' => $installmentId,
                    'status' => $status,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }


    /**
     * Atualizar os dados financeiros originais de uma parcela
     * que ainda não possui histórico de pagamento.
     *
     * A decisão sobre a parcela poder ou não ser alterada
     * é responsabilidade do PurchaseDocumentService.
     *
     * Este Repository apenas persiste os valores já validados.
     */
    public function updateEditableData(
        int $installmentId,
        ?string $dueDate,
        string $originalAmount,
        string $status
    ): bool {

        try {

            $sql = "
                UPDATE
                    adms_daman_purchase_installments

                SET
                    due_date =
                        :due_date,

                    original_amount =
                        :original_amount,

                    status =
                        :status,

                    updated_at =
                        NOW()

                WHERE
                    id =
                        :installment_id
            ";


            $stmt =
                $this->getConnection()
                    ->prepare(
                        $sql
                    );


            if ($dueDate === null) {

                $stmt->bindValue(
                    ':due_date',
                    null,
                    PDO::PARAM_NULL
                );

            } else {

                $stmt->bindValue(
                    ':due_date',
                    $dueDate,
                    PDO::PARAM_STR
                );
            }


            $stmt->bindValue(
                ':original_amount',
                $originalAmount,
                PDO::PARAM_STR
            );


            $stmt->bindValue(
                ':status',
                $status,
                PDO::PARAM_STR
            );


            $stmt->bindValue(
                ':installment_id',
                $installmentId,
                PDO::PARAM_INT
            );


            return $stmt->execute();

        } catch (PDOException $err) {

            GenerateLog::generateLog(
                'error',
                'Erro ao atualizar dados editáveis da parcela.',
                [
                    'installment_id' =>
                        $installmentId,

                    'due_date' =>
                        $dueDate,

                    'original_amount' =>
                        $originalAmount,

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
     * Excluir uma parcela ainda não movimentada.
     *
     * O Service valida previamente:
     *
     * - se a parcela pertence ao lançamento;
     * - se não possui histórico financeiro;
     * - se não está protegida como OK.
     *
     * O ID do lançamento também entra no WHERE como
     * proteção adicional contra exclusão indevida.
     */
    public function deleteById(
        int $installmentId,
        int $purchaseDocumentId
    ): bool {

        try {

            $sql = "
                DELETE FROM
                    adms_daman_purchase_installments

                WHERE
                    id =
                        :installment_id

                    AND
                    adms_daman_purchase_document_id =
                        :purchase_document_id
            ";


            $stmt =
                $this->getConnection()
                    ->prepare(
                        $sql
                    );


            $stmt->bindValue(
                ':installment_id',
                $installmentId,
                PDO::PARAM_INT
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
                'Erro ao excluir parcela do lançamento financeiro.',
                [
                    'installment_id' =>
                        $installmentId,

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
