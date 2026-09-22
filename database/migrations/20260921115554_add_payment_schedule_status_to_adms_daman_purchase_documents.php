<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Adicionar o status de confirmação do parcelamento
 * aos lançamentos financeiros de compras.
 *
 * Essa informação permite cadastrar um lançamento
 * antes do recebimento dos boletos ou da confirmação
 * das datas/valores das parcelas.
 *
 * confirmed = parcelamento já confirmado
 * pending   = parcelamento pendente de confirmação
 */
final class AddPaymentScheduleStatusToAdmsDamanPurchaseDocuments
    extends AbstractMigration
{
    public function up(): void
    {
        /*
         * Verificar se a tabela existe antes de alterar.
         */
        if (
            $this->hasTable(
                'adms_daman_purchase_documents'
            )
        ) {

            $table =
                $this->table(
                    'adms_daman_purchase_documents'
                );


            /*
             * Evitar tentar criar a coluna novamente
             * caso a migration seja ajustada durante
             * o desenvolvimento.
             */
            if (
                !$table->hasColumn(
                    'payment_schedule_status'
                )
            ) {

                $table
                    ->addColumn(
                        'payment_schedule_status',
                        'enum',
                        [
                            'values' => [
                                'pending',
                                'confirmed',
                            ],

                            /*
                             * Fluxo normal atual:
                             * parcelas já informadas.
                             */
                            'default' => 'confirmed',

                            'null' => false,

                            'comment' =>
                                'Status da confirmação das parcelas: '
                                . 'pending = falta confirmar; '
                                . 'confirmed = parcelas confirmadas',
                        ]
                    )

                    /*
                     * O índice será útil no filtro rápido
                     * de lançamentos com parcelas pendentes.
                     */
                    ->addIndex(
                        [
                            'payment_schedule_status',
                        ],
                        [
                            'name' =>
                                'idx_purchase_documents_payment_schedule_status',
                        ]
                    )

                    ->update();
            }
        }
    }


    public function down(): void
    {
        if (
            $this->hasTable(
                'adms_daman_purchase_documents'
            )
        ) {

            $table =
                $this->table(
                    'adms_daman_purchase_documents'
                );


            if (
                $table->hasColumn(
                    'payment_schedule_status'
                )
            ) {

                $table
                    ->removeColumn(
                        'payment_schedule_status'
                    )
                    ->update();
            }
        }
    }
}