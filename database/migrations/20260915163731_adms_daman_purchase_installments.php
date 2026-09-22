<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration para criar a tabela `adms_daman_purchase_installments`, que armazena informações sobre as parcelas de compras realizadas. Armazena o número da parcela, data de vencimento, valor original, status e observações. Além disso, estabelece relacionamentos com a tabela `adms_daman_purchase_documents` para vincular cada parcela ao respectivo lançamento de compra.
 */
final class AdmsDamanPurchaseInstallments extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_purchase_installments')) {

            $table = $this->table('adms_daman_purchase_installments');

            $table
                ->addColumn('adms_daman_purchase_document_id', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'Lançamento de compra ao qual a parcela pertence',
                ])

                ->addColumn('installment_number', 'integer', [
                    'null' => false,
                    'comment' => 'Número da parcela',
                ])

                ->addColumn('due_date', 'date', [
                    'null' => true,
                    'comment' => 'Data de vencimento da parcela; pode ser indefinida em caso de permuta',
                ])

                ->addColumn('original_amount', 'decimal', [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'comment' => 'Valor original da parcela',
                ])

                ->addColumn('status', 'string', [
                    'limit' => 10,
                    'null' => false,
                    'default' => 'AV',
                    'comment' => 'AV=A vencer, AT=Atenção, OK=Pago, AP=Permuta',
                ])

                ->addColumn('observation', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'comment' => 'Observação da parcela',
                ])

                ->addColumn('created_at', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                ])

                ->addColumn('updated_at', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP',
                ])

                ->addIndex(
                    [
                        'adms_daman_purchase_document_id',
                        'installment_number',
                    ],
                    [
                        'unique' => true,
                        'name' => 'idx_unique_purchase_installment',
                    ]
                )

                ->addIndex(
                    ['due_date'],
                    [
                        'name' => 'idx_purchase_installments_due_date',
                    ]
                )

                ->addIndex(
                    ['status'],
                    [
                        'name' => 'idx_purchase_installments_status',
                    ]
                )

                ->addForeignKey(
                    'adms_daman_purchase_document_id',
                    'adms_daman_purchase_documents',
                    'id',
                    [
                        'delete' => 'CASCADE',
                        'update' => 'CASCADE',
                    ]
                )

                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('adms_daman_purchase_installments')) {
            $this->table('adms_daman_purchase_installments')
                ->drop()
                ->save();
        }
    }
}
