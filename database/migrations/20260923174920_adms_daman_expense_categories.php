<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanExpenseCategories extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_expense_categories')) {
            $table = $this->table('adms_daman_expense_categories');

            $table
                ->addColumn('name', 'string', [
                    'limit' => 100,
                    'null' => false,
                    'comment' => 'Categoria da despesa',
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
                    'name' => 'uniq_expense_category_name',
                ])
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('adms_daman_expense_categories')) {
            $this->table('adms_daman_expense_categories')
                ->drop()
                ->save();
        }
    }
}
