<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Tabela de itens dos pedidos (compra e locação)
 */
final class AdmsDamanOrderItems extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_order_items')) {

            $table = $this->table('adms_daman_order_items');

            $table
                // FK Pedido
                ->addColumn('adms_daman_order_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_order_id', 'adms_daman_orders', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])

                // Descrição do item
                ->addColumn('description', 'string', ['null' => false])

                // Unidade (ex: UN, M, KG)
                ->addColumn('adms_daman_measurement_units_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_measurement_units_id', 'adms_daman_measurement_units', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])

                /*
                 |========================
                 | CAMPOS COMUNS
                 |========================
                */
                ->addColumn('quantity', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])

                /*
                 |========================
                 | COMPRA
                 |========================
                */
                ->addColumn('purchased_quantity', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])

                ->addColumn('unit_price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])

                /*
                 |========================
                 | LOCAÇÃO
                 |========================
                */
                ->addColumn('rented_quantity', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])

                ->addColumn('returned_quantity', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])

                ->addColumn('rental_start_date', 'timestamp', ['null' => true])

                ->addColumn('rental_end_date', 'timestamp', ['null' => true])

                /*
                 |========================
                 | STATUS GERAL
                 |========================
                */
                ->addColumn('adms_daman_acquisition_status_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_acquisition_status_id', 'adms_daman_acquisition_status', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])

                /*
                 |========================
                 | CONTROLE
                 |========================
                */
                ->addColumn('created_at', 'timestamp', ['null' => false])

                ->addColumn('updated_at', 'timestamp', ['null' => true])

                // Índice
                ->addIndex(['adms_daman_order_id'])

                ->create();
        }
    }

    public function down(): void
    {
        $this->table('adms_daman_order_items')->drop()->save();
    }
}
