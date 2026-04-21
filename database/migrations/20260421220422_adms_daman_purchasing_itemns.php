<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanPurchasingItemns extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_purchasing_items')) {

            $table = $this->table('adms_daman_purchasing_items');

            $table
                // FK Compra
                ->addColumn('adms_daman_purchasing_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_purchasing_id', 'adms_daman_purchasings', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])

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
                 | CONTROLE
                 |========================
                */
                ->addColumn('created_at', 'timestamp', ['null' => false])

                ->addColumn('updated_at', 'timestamp', ['null' => true])

                // Índice
                ->addIndex(['adms_daman_purchasing_id'])

                ->create();
        }
    }

    public function down(): void
    {
        $this->table('adms_daman_purchasing_items')->drop()->save();
    }
}
