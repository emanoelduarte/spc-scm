<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use PDO;
use PDOException;

class PurchaseInstallmentPaymentsRepository extends DbConnection
{
    /**
     * Registrar um pagamento/baixa de uma parcela.
     *
     * O Repository apenas persiste os dados.
     * Validações e cálculos financeiros serão
     * realizados posteriormente pelo Service.
     *
     * @param array $data Dados do pagamento.
     * @return int ID do pagamento criado.
     *
     * @throws PDOException
     */
    public function createPayment(array $data): int
    {
        try {

            $sql = "
                INSERT INTO
                    adms_daman_purchase_installment_payments
                (
                    adms_daman_purchase_installment_id,
                    payment_date,
                    principal_amount,
                    interest_amount,
                    penalty_amount,
                    discount_amount,
                    total_paid,
                    status,
                    observation,
                    created_by,
                    created_at
                )
                VALUES
                (
                    :adms_daman_purchase_installment_id,
                    :payment_date,
                    :principal_amount,
                    :interest_amount,
                    :penalty_amount,
                    :discount_amount,
                    :total_paid,
                    :status,
                    :observation,
                    :created_by,
                    :created_at
                )
            ";

            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );


            /*
             * Parcela relacionada ao pagamento.
             */
            $stmt->bindValue(
                ':adms_daman_purchase_installment_id',
                (int) $data['adms_daman_purchase_installment_id'],
                PDO::PARAM_INT
            );


            /*
             * Data efetiva do pagamento.
             */
            $stmt->bindValue(
                ':payment_date',
                $data['payment_date'],
                PDO::PARAM_STR
            );


            /*
             * Valores financeiros.
             *
             * Utilizamos PARAM_STR para campos DECIMAL
             * evitando conversões desnecessárias de precisão.
             */
            $stmt->bindValue(
                ':principal_amount',
                $data['principal_amount'],
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':interest_amount',
                $data['interest_amount'],
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':penalty_amount',
                $data['penalty_amount'],
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':discount_amount',
                $data['discount_amount'],
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':total_paid',
                $data['total_paid'],
                PDO::PARAM_STR
            );


            /*
             * Todo novo pagamento começa ativo.
             */
            $stmt->bindValue(
                ':status',
                'active',
                PDO::PARAM_STR
            );


            /*
             * Observação opcional.
             */
            $observation =
                !empty($data['observation'])
                ? trim(
                    (string) $data['observation']
                )
                : null;

            $stmt->bindValue(
                ':observation',
                $observation,
                $observation === null
                    ? PDO::PARAM_NULL
                    : PDO::PARAM_STR
            );


            /*
             * Usuário responsável pelo registro.
             */
            $stmt->bindValue(
                ':created_by',
                (int) $data['created_by'],
                PDO::PARAM_INT
            );


            /*
             * Momento em que a baixa foi registrada
             * no sistema.
             */
            $stmt->bindValue(
                ':created_at',
                date('Y-m-d H:i:s'),
                PDO::PARAM_STR
            );


            $stmt->execute();


            /*
             * Retornar o ID da movimentação criada.
             */
            return (int)
            $this->getConnection()
                ->lastInsertId();
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao registrar pagamento de parcela.',
                [
                    'installment_id' =>
                    $data['adms_daman_purchase_installment_id']
                        ?? null,

                    'error' =>
                    $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Recuperar uma parcela pelo ID.
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
     * Atualizar o status da parcela.
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
     * Recuperar o total de principal já baixado
     * para determinada parcela.
     *
     * Pagamentos estornados não entram no cálculo.
     */
    public function getTotalPrincipalPaidByInstallmentId(
        int $installmentId
    ): float {

        try {

            $sql = "
            SELECT
                COALESCE(
                    SUM(principal_amount),
                    0
                ) AS total_principal_paid

            FROM
                adms_daman_purchase_installment_payments

            WHERE
                adms_daman_purchase_installment_id =
                    :installment_id

                AND status = 'active'
        ";

            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );

            $stmt->bindValue(
                ':installment_id',
                $installmentId,
                PDO::PARAM_INT
            );

            $stmt->execute();

            $result =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );

            return (float) (
                $result['total_principal_paid']
                ?? 0
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao calcular principal pago da parcela.',
                [
                    'installment_id' => $installmentId,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Recuperar um pagamento pelo ID.
     */
    public function getPaymentById(
        int $paymentId
    ): array|bool {

        try {

            $sql = "
                SELECT
                    id,
                    adms_daman_purchase_installment_id,
                    payment_date,
                    principal_amount,
                    interest_amount,
                    penalty_amount,
                    discount_amount,
                    total_paid,
                    status,
                    observation,
                    created_by,
                    created_at,
                    reversed_by,
                    reversed_at,
                    reversal_reason

                FROM
                    adms_daman_purchase_installment_payments

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
                $paymentId,
                PDO::PARAM_INT
            );

            $stmt->execute();

            return $stmt->fetch(
                PDO::FETCH_ASSOC
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar pagamento da parcela.',
                [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Estornar um pagamento.
     *
     * O registro financeiro não é excluído.
     * Apenas deixa de ser considerado ativo.
     */
    public function reversePayment(
        int $paymentId,
        int $reversedBy,
        string $reason
    ): bool {

        try {

            $sql = "
                UPDATE
                    adms_daman_purchase_installment_payments

                SET
                    status = 'reversed',
                    reversed_by = :reversed_by,
                    reversed_at = :reversed_at,
                    reversal_reason = :reversal_reason

                WHERE
                    id = :id

                    AND status = 'active'
            ";

            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );

            $stmt->bindValue(
                ':reversed_by',
                $reversedBy,
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':reversed_at',
                date('Y-m-d H:i:s'),
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':reversal_reason',
                trim($reason),
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':id',
                $paymentId,
                PDO::PARAM_INT
            );

            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao estornar pagamento da parcela.',
                [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Recuperar os pagamentos vinculados a várias parcelas.
     *
     * Utilizado principalmente na listagem de contas a pagar para
     * exibir o histórico de baixas sem executar uma consulta para
     * cada parcela individualmente.
     *
     * São retornados pagamentos ativos e estornados, pois ambos
     * fazem parte do histórico financeiro da parcela.
     */
    public function getByInstallmentIds(
        array $installmentIds
    ): array {

        /*
        * Remover valores inválidos e duplicados.
        */
        $installmentIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $installmentIds
                    ),
                    fn($id) => $id > 0
                )
            )
        );


        if (empty($installmentIds)) {
            return [];
        }


        try {

            /*
            * Criar os placeholders dinamicamente:
            *
            * :installment_id_0,
            * :installment_id_1,
            * :installment_id_2...
            */
            $placeholders = [];

            foreach ($installmentIds as $index => $id) {
                $placeholders[] =
                    ':installment_id_' . $index;
            }


            $sql = "
                SELECT
                    id,
                    adms_daman_purchase_installment_id,
                    payment_date,
                    principal_amount,
                    interest_amount,
                    penalty_amount,
                    discount_amount,
                    total_paid,
                    status,
                    observation,
                    created_by,
                    created_at,
                    reversed_by,
                    reversed_at,
                    reversal_reason

                FROM
                    adms_daman_purchase_installment_payments

                WHERE
                    adms_daman_purchase_installment_id
                    IN (" . implode(',', $placeholders) . ")

                ORDER BY
                    adms_daman_purchase_installment_id ASC,
                    payment_date ASC,
                    id ASC
            ";


            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );


            foreach (
                $installmentIds as $index => $id
            ) {

                $stmt->bindValue(
                    ':installment_id_' . $index,
                    $id,
                    \PDO::PARAM_INT
                );
            }


            $stmt->execute();

            return $stmt->fetchAll(
                \PDO::FETCH_ASSOC
            );
        } catch (\PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar histórico de pagamentos das parcelas.',
                [
                    'installment_ids' =>
                    $installmentIds,

                    'error' =>
                    $e->getMessage(),
                ]
            );

            throw $e;
        }
    }
}
