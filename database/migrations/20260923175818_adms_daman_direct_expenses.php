<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanDirectExpenses extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_direct_expenses')) {
            $table = $this->table('adms_daman_direct_expenses');

            $table
                ->addColumn('adms_daman_project_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                ])
                ->addForeignKey(
                    'adms_daman_project_id',
                    'adms_daman_projects',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )

                ->addColumn('adms_daman_expense_category_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                ])
                ->addForeignKey(
                    'adms_daman_expense_category_id',
                    'adms_daman_expense_categories',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )

                ->addColumn('adms_daman_financial_payment_method_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                ])
                ->addForeignKey(
                    'adms_daman_financial_payment_method_id',
                    'adms_daman_financial_payment_methods',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )

                ->addColumn('expense_date', 'date', [
                    'null' => false,
                    'comment' => 'Data em que o desembolso ocorreu',
                ])

                ->addColumn('description', 'string', [
                    'limit' => 255,
                    'null' => false,
                ])

                ->addColumn('amount', 'decimal', [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                ])

                ->addColumn('observation', 'text', [
                    'null' => true,
                ])

                ->addColumn('created_by', 'integer', [
                    'null' => false,
                    'signed' => false,
                ])
                ->addForeignKey(
                    'created_by',
                    'adms_daman_users',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )

                ->addColumn('created_at', 'timestamp', [
                    'null' => false,
                ])

                ->addColumn('updated_at', 'timestamp', [
                    'null' => true,
                ])

                ->addIndex(
                    ['adms_daman_project_id', 'expense_date'],
                    [
                        'name' => 'idx_direct_expense_project_date',
                    ]
                )

                ->addIndex(
                    ['adms_daman_expense_category_id'],
                    [
                        'name' => 'idx_direct_expense_category',
                    ]
                )

                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('adms_daman_direct_expenses')) {
            $this->table('adms_daman_direct_expenses')
                ->drop()
                ->save();
        }
    }
}
