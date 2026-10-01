<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCancellationToAdmsDamanDirectExpenses extends AbstractMigration
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


        if (!$table->hasColumn('status')) {

            $table->addColumn(
                'status',
                'boolean',
                [
                    'null' => false,
                    'default' => true,
                    'comment' => '1 = Ativa / 0 = Cancelada',
                    'after' => 'observation',
                ]
            );
        }


        if (!$table->hasColumn('cancelled_by')) {

            $table->addColumn(
                'cancelled_by',
                'integer',
                [
                    'null' => true,
                    'signed' => false,
                    'after' => 'updated_at',
                ]
            );
        }


        if (!$table->hasColumn('cancelled_at')) {

            $table->addColumn(
                'cancelled_at',
                'timestamp',
                [
                    'null' => true,
                    'after' => 'cancelled_by',
                ]
            );
        }


        if (!$table->hasColumn('cancellation_reason')) {

            $table->addColumn(
                'cancellation_reason',
                'string',
                [
                    'limit' => 500,
                    'null' => true,
                    'after' => 'cancelled_at',
                    'comment' => 'Motivo informado no cancelamento da despesa',
                ]
            );
        }


        $table->update();


        /*
         * Foreign key do usuário responsável
         * pelo cancelamento.
         */
        $table =
            $this->table(
                'adms_daman_direct_expenses'
            );


        $table
            ->addForeignKey(
                'cancelled_by',
                'adms_daman_users',
                'id',
                [
                    'delete' => 'RESTRICT',
                    'update' => 'CASCADE',
                    'constraint' =>
                        'fk_direct_expense_cancelled_by',
                ]
            )
            ->update();
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


        /*
         * Primeiro remover a FK.
         */
        if ($table->hasColumn('cancelled_by')) {

            $table->dropForeignKey(
                'cancelled_by',
                'fk_direct_expense_cancelled_by'
            );
        }


        $table->update();


        $table =
            $this->table(
                'adms_daman_direct_expenses'
            );


        if ($table->hasColumn('cancellation_reason')) {

            $table->removeColumn(
                'cancellation_reason'
            );
        }


        if ($table->hasColumn('cancelled_at')) {

            $table->removeColumn(
                'cancelled_at'
            );
        }


        if ($table->hasColumn('cancelled_by')) {

            $table->removeColumn(
                'cancelled_by'
            );
        }


        if ($table->hasColumn('status')) {

            $table->removeColumn(
                'status'
            );
        }


        $table->update();
    }
}