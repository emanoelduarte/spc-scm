<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanDirectExpenseAllocations extends AbstractMigration
{
    /**
     * Criar a tabela responsável pelo rateio das despesas diretas
     * entre uma ou mais obras.
     *
     * Esta migration altera apenas a estrutura do banco.
     * Nenhum dado inicial é inserido aqui.
     */
    public function up(): void
    {
        if ($this->hasTable('adms_daman_direct_expense_allocations')) {
            return;
        }

        $table = $this->table(
            'adms_daman_direct_expense_allocations'
        );

        $table
            ->addColumn(
                'adms_daman_direct_expense_id',
                'integer',
                [
                    'null' => false,
                    'signed' => false,
                    'comment' => 'Despesa direta vinculada ao rateio',
                ]
            )
            ->addColumn(
                'adms_daman_project_id',
                'integer',
                [
                    'null' => false,
                    'signed' => false,
                    'comment' => 'Obra que recebe parte da despesa',
                ]
            )
            ->addColumn(
                'allocated_amount',
                'decimal',
                [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'comment' => 'Valor da despesa apropriado para a obra',
                ]
            )
            ->addColumn(
                'created_by',
                'integer',
                [
                    'null' => false,
                    'signed' => false,
                ]
            )
            ->addColumn(
                'created_at',
                'timestamp',
                [
                    'null' => false,
                ]
            )
            ->addColumn(
                'updated_at',
                'timestamp',
                [
                    'null' => true,
                ]
            )
            ->addIndex(
                [
                    'adms_daman_direct_expense_id',
                    'adms_daman_project_id',
                ],
                [
                    'unique' => true,
                    'name' => 'uniq_direct_expense_project_allocation',
                ]
            )
            ->addIndex(
                ['adms_daman_project_id'],
                [
                    'name' => 'idx_direct_expense_allocation_project',
                ]
            )
            ->addForeignKey(
                'adms_daman_direct_expense_id',
                'adms_daman_direct_expenses',
                'id',
                [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                    'constraint' => 'fk_direct_expense_allocation_expense',
                ]
            )
            ->addForeignKey(
                'adms_daman_project_id',
                'adms_daman_projects',
                'id',
                [
                    'delete' => 'RESTRICT',
                    'update' => 'CASCADE',
                    'constraint' => 'fk_direct_expense_allocation_project',
                ]
            )
            ->addForeignKey(
                'created_by',
                'adms_daman_users',
                'id',
                [
                    'delete' => 'RESTRICT',
                    'update' => 'CASCADE',
                    'constraint' => 'fk_direct_expense_allocation_created_by',
                ]
            )
            ->create();
    }

    /**
     * Remover apenas a estrutura criada por esta migration.
     */
    public function down(): void
    {
        if ($this->hasTable('adms_daman_direct_expense_allocations')) {
            $this->table('adms_daman_direct_expense_allocations')
                ->drop()
                ->save();
        }
    }
}
