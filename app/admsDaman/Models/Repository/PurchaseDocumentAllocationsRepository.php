<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use PDO;
use PDOException;

class PurchaseDocumentAllocationsRepository extends DbConnection
{
    /**
     * Criar todas as alocações/rateios de um lançamento financeiro.
     *
     * Este método não valida se a soma dos rateios corresponde
     * ao valor total do documento. Essa responsabilidade ficará
     * no Service.
     *
     * Também não inicia transação própria, pois o lançamento
     * financeiro e seus rateios deverão ser gravados dentro
     * da mesma transação controlada pelo Service.
     */
    public function createMany(
        int $purchaseDocumentId,
        array $allocations,
        int $createdBy
    ): bool {

        /*
         * O Service não deverá chamar este método sem rateios.
         *
         * Mesmo assim, protegemos o Repository contra uma
         * chamada vazia.
         */
        if (empty($allocations)) {
            return false;
        }

        try {

            $sql = "
                INSERT INTO
                    adms_daman_purchase_document_allocations
                    (
                        adms_daman_purchase_document_id,
                        adms_daman_project_id,
                        allocated_amount,
                        observation,
                        created_by,
                        created_at,
                        updated_at
                    )

                VALUES
                    (
                        :purchase_document_id,
                        :project_id,
                        :allocated_amount,
                        :observation,
                        :created_by,
                        :created_at,
                        :updated_at
                    )
            ";


            /*
             * Preparar uma única vez e reutilizar o statement
             * para todas as obras participantes do rateio.
             */
            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );


            $now =
                date('Y-m-d H:i:s');


            foreach ($allocations as $allocation) {

                $stmt->bindValue(
                    ':purchase_document_id',
                    $purchaseDocumentId,
                    PDO::PARAM_INT
                );


                $stmt->bindValue(
                    ':project_id',
                    (int) (
                        $allocation['adms_daman_project_id'] ?? 0
                    ),
                    PDO::PARAM_INT
                );


                /*
                 * DECIMAL deve ser enviado como string para
                 * evitar conversões desnecessárias para float.
                 *
                 * O valor já chegará normalizado pelo Service,
                 * por exemplo:
                 *
                 * 600.00
                 */
                $stmt->bindValue(
                    ':allocated_amount',
                    (string) (
                        $allocation['allocated_amount'] ?? '0.00'
                    ),
                    PDO::PARAM_STR
                );


                $observation =
                    trim(
                        (string) (
                            $allocation['observation'] ?? ''
                        )
                    );


                if ($observation === '') {

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
                    ':created_by',
                    $createdBy,
                    PDO::PARAM_INT
                );


                $stmt->bindValue(
                    ':created_at',
                    $now,
                    PDO::PARAM_STR
                );


                $stmt->bindValue(
                    ':updated_at',
                    $now,
                    PDO::PARAM_STR
                );


                if (!$stmt->execute()) {
                    return false;
                }
            }


            return true;
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao criar rateios do lançamento financeiro.',
                [
                    'purchase_document_id' =>
                    $purchaseDocumentId,

                    'created_by' =>
                    $createdBy,

                    'allocations' =>
                    $allocations,

                    'error' =>
                    $e->getMessage(),
                ]
            );


            throw $e;
        }
    }


    /**
     * Recuperar todos os rateios pertencentes
     * a um lançamento financeiro.
     *
     * O nome da obra também é recuperado para facilitar
     * a utilização em telas, relatórios e consultas futuras.
     */
    public function getByPurchaseDocumentId(
        int $purchaseDocumentId
    ): array {

        try {

            $sql = "
                SELECT
                    allocation.id,

                    allocation.adms_daman_purchase_document_id,

                    allocation.adms_daman_project_id,

                    project.name
                        AS project_name,

                    allocation.allocated_amount,

                    allocation.observation,

                    allocation.created_by,

                    allocation.created_at,

                    allocation.updated_at

                FROM
                    adms_daman_purchase_document_allocations
                        AS allocation

                INNER JOIN
                    adms_daman_projects
                        AS project
                    ON project.id =
                        allocation.adms_daman_project_id

                WHERE
                    allocation.adms_daman_purchase_document_id =
                        :purchase_document_id

                ORDER BY
                    allocation.id ASC
            ";


            $stmt =
                $this->getConnection()->prepare(
                    $sql
                );


            $stmt->bindValue(
                ':purchase_document_id',
                $purchaseDocumentId,
                PDO::PARAM_INT
            );


            $stmt->execute();


            return $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar rateios do lançamento financeiro.',
                [
                    'purchase_document_id' =>
                    $purchaseDocumentId,

                    'error' =>
                    $e->getMessage(),
                ]
            );


            throw $e;
        }
    }

    /**
     * Recuperar os rateios de vários lançamentos financeiros
     * em uma única consulta.
     *
     * Utilizado principalmente nas listagens para evitar
     * executar uma consulta para cada lançamento.
     */
    public function getByPurchaseDocumentIds(
        array $purchaseDocumentIds
    ): array {

        /*
     * Normalizar IDs e eliminar valores inválidos
     * ou repetidos.
     */
        $purchaseDocumentIds =
            array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'intval',
                            $purchaseDocumentIds
                        ),
                        fn(int $id): bool => $id > 0
                    )
                )
            );


        if (empty($purchaseDocumentIds)) {
            return [];
        }


        try {

            /*
         * Criar placeholders dinamicamente:
         *
         * :document_id_0,
         * :document_id_1,
         * :document_id_2...
         */
            $placeholders = [];

            foreach (
                $purchaseDocumentIds
                as $index => $id
            ) {

                $placeholders[] =
                    ':document_id_' . $index;
            }


            $sql = "
            SELECT

                allocation.id,

                allocation.adms_daman_purchase_document_id,

                allocation.adms_daman_project_id,

                project.name
                    AS project_name,

                allocation.allocated_amount,

                allocation.observation,

                allocation.created_by,

                allocation.created_at,

                allocation.updated_at

            FROM
                adms_daman_purchase_document_allocations
                    AS allocation

            INNER JOIN
                adms_daman_projects
                    AS project
                ON project.id =
                    allocation.adms_daman_project_id

            WHERE
                allocation.adms_daman_purchase_document_id
                IN (" . implode(',', $placeholders) . ")

            ORDER BY
                allocation.adms_daman_purchase_document_id ASC,
                allocation.id ASC
        ";


            $stmt =
                $this->getConnection()
                ->prepare(
                    $sql
                );


            foreach (
                $purchaseDocumentIds
                as $index => $id
            ) {

                $stmt->bindValue(
                    ':document_id_' . $index,
                    $id,
                    PDO::PARAM_INT
                );
            }


            $stmt->execute();


            return $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
        } catch (PDOException $e) {

            GenerateLog::generateLog(
                'error',
                'Erro ao recuperar rateios dos lançamentos financeiros.',
                [
                    'purchase_document_ids' =>
                    $purchaseDocumentIds,

                    'error' =>
                    $e->getMessage(),
                ]
            );


            throw $e;
        }
    }
}
