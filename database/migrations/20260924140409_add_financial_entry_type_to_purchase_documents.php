<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFinancialEntryTypeToPurchaseDocuments extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_purchase_documents')) {
            return;
        }

        $table = $this->table('adms_daman_purchase_documents');

        if (!$table->hasColumn('financial_entry_type')) {
            $table
                ->addColumn('financial_entry_type', 'string', [
                    'limit' => 40,
                    'null' => false,
                    'default' => 'purchase',
                    'after' => 'adms_daman_nfe_id',
                    'comment' => 'Natureza do lançamento financeiro: purchase ou financial_obligation',
                ])
                ->update();
        }

        if (!$table->hasIndex(['financial_entry_type'])) {
            $table
                ->addIndex(['financial_entry_type'], [
                    'name' => 'idx_purchase_documents_financial_entry_type',
                ])
                ->update();
        }
    }

    public function down(): void
    {
        if (!$this->hasTable('adms_daman_purchase_documents')) {
            return;
        }

        $table = $this->table('adms_daman_purchase_documents');

        if ($table->hasIndex(['financial_entry_type'])) {
            $table
                ->removeIndex(['financial_entry_type'])
                ->update();
        }

        if ($table->hasColumn('financial_entry_type')) {
            $table
                ->removeColumn('financial_entry_type')
                ->update();
        }
    }
}