<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanFinancialPaymentMethods extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_financial_payment_methods')) {
            $table = $this->table('adms_daman_financial_payment_methods');

            $table
                ->addColumn('name', 'string', [
                    'limit' => 100,
                    'null' => false,
                    'comment' => 'Forma do pagamento efetivamente realizado',
                ])
                ->addColumn('status', 'boolean', [
                    'null' => false,
                    'default' => 1,
                ])
                ->addColumn('created_at', 'timestamp', [
                    'null' => false,
                ])
                ->addColumn('updated_at', 'timestamp', [
                    'null' => true,
                ])
                ->addIndex(['name'], [
                    'unique' => true,
                    'name' => 'uniq_financial_payment_method_name',
                ])
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('adms_daman_financial_payment_methods')) {
            $this->table('adms_daman_financial_payment_methods')
                ->drop()
                ->save();
        }
    }
}
