<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Adicionar a forma do pagamento efetivamente realizado
 * às baixas das parcelas de compras.
 *
 * O campo é nullable para manter compatibilidade com
 * pagamentos históricos já existentes em produção.
 */
final class AddFinancialPaymentMethodToPurchaseInstallmentPayments extends AbstractMigration
{
    public function up(): void
    {
        if (
            !$this->hasTable(
                'adms_daman_purchase_installment_payments'
            )
        ) {
            return;
        }


        $table =
            $this->table(
                'adms_daman_purchase_installment_payments'
            );


        if (
            $table->hasColumn(
                'adms_daman_financial_payment_method_id'
            )
        ) {
            return;
        }


        $table
            ->addColumn(
                'adms_daman_financial_payment_method_id',
                'integer',
                [
                    'null' => true,
                    'signed' => false,
                    'after' => 'payment_date',
                    'comment' =>
                        'Forma utilizada no pagamento efetivo da parcela.',
                ]
            )
            ->addForeignKey(
                'adms_daman_financial_payment_method_id',
                'adms_daman_financial_payment_methods',
                'id',
                [
                    'delete' => 'RESTRICT',
                    'update' => 'CASCADE',
                ]
            )
            ->addIndex(
                [
                    'adms_daman_financial_payment_method_id',
                ],
                [
                    'name' =>
                        'idx_purchase_installment_payment_financial_method',
                ]
            )
            ->update();
    }


    public function down(): void
    {
        if (
            !$this->hasTable(
                'adms_daman_purchase_installment_payments'
            )
        ) {
            return;
        }


        $table =
            $this->table(
                'adms_daman_purchase_installment_payments'
            );


        if (
            !$table->hasColumn(
                'adms_daman_financial_payment_method_id'
            )
        ) {
            return;
        }


        $table
            ->removeForeignKey(
                'adms_daman_financial_payment_method_id'
            )
            ->removeIndex(
                [
                    'adms_daman_financial_payment_method_id',
                ],
                [
                    'name' =>
                        'idx_purchase_installment_payment_financial_method',
                ]
            )
            ->removeColumn(
                'adms_daman_financial_payment_method_id'
            )
            ->update();
    }
}
