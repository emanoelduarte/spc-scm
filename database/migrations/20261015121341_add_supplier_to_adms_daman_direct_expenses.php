<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSupplierToAdmsDamanDirectExpenses extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_direct_expenses')) {
            return;
        }

        $table =
            $this->table(
                'adms_daman_direct_expenses'
            );

        if (
            !$table->hasColumn(
                'adms_daman_supplier_id'
            )
        ) {
            $table
                ->addColumn(
                    'adms_daman_supplier_id',
                    'integer',
                    [
                        'null' => true,
                        'signed' => false,
                        'after' => 'adms_daman_project_id',
                        'comment' => 'Fornecedor opcional vinculado à despesa direta',
                    ]
                )
                ->addIndex(
                    [
                        'adms_daman_supplier_id',
                    ],
                    [
                        'name' =>
                            'idx_direct_expense_supplier',
                    ]
                )
                ->addForeignKey(
                    'adms_daman_supplier_id',
                    'adms_daman_suppliers',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )
                ->update();
        }
    }


    public function down(): void
    {
        if (!$this->hasTable('adms_daman_direct_expenses')) {
            return;
        }

        $table =
            $this->table(
                'adms_daman_direct_expenses'
            );

        if (
            $table->hasColumn(
                'adms_daman_supplier_id'
            )
        ) {
            $table
                ->dropForeignKey(
                    'adms_daman_supplier_id'
                )
                ->removeIndexByName(
                    'idx_direct_expense_supplier'
                )
                ->removeColumn(
                    'adms_daman_supplier_id'
                )
                ->update();
        }
    }
}
