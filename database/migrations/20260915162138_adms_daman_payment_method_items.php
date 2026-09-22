<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration para criar a tabela adms_daman_payment_method_items, responsável por guardar os itens de cada condição de pagamento vinculada a um método de pagamento. Ou seja, cada item representado por uma linha se torna uma parcela para pagamento da compa.
 */
final class AdmsDamanPaymentMethodItems extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_payment_method_items')) {

            $table = $this->table('adms_daman_payment_method_items');

            $table
                ->addColumn('adms_daman_payment_method_id', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'Condição de pagamento vinculada',
                ])

                ->addColumn('installment_number', 'integer', [
                    'null' => false,
                    'comment' => 'Número da parcela',
                ])

                ->addColumn('days_after_purchase', 'integer', [
                    'null' => false,
                    'comment' => 'Quantidade de dias após a data da compra',
                ])

                ->addColumn('percentage', 'decimal', [
                    'precision' => 7,
                    'scale' => 4,
                    'null' => true,
                    'comment' => 'Percentual do valor total destinado à parcela',
                ])

                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')

                ->addIndex(
                    [
                        'adms_daman_payment_method_id',
                        'installment_number',
                    ],
                    [
                        'unique' => true,
                        'name' => 'idx_unique_payment_method_installment',
                    ]
                )

                ->addForeignKey(
                    'adms_daman_payment_method_id',
                    'adms_daman_payment_methods',
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
        if ($this->hasTable('adms_daman_payment_method_items')) {
            $this->table('adms_daman_payment_method_items')
                ->drop()
                ->save();
        }
    }
}