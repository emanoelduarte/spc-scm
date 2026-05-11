<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanSupplierAddresses extends AbstractMigration
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
        if (!$this->hasTable('adms_daman_supplier_addresses')) {
            // Define o nome da tabela
            $table = $this->table('adms_daman_supplier_addresses');

            // Define as colunas da tabela
            $table->addColumn('adms_daman_supplier_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('adms_daman_address_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('created_at', 'datetime', ['null' => true])
                ->addForeignKey('adms_daman_supplier_id', 'adms_daman_suppliers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('adms_daman_address_id', 'adms_daman_addresses', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }
    }

    public function down(): void
    {
        $this->table('adms_daman_supplier_addresses')->drop()->save();
    }
}
