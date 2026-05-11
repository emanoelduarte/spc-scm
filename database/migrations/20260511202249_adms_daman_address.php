<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanAddress extends AbstractMigration
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

        // Acessa o if quando não existir a tabela no banco de dados
        if (!$this->hasTable('adms_daman_addresses')) {
            // Define o nome da tabela
            $table = $this->table('adms_daman_addresses');

            // Define as colunas da tabela
            $table->addColumn('zip_code', 'string', ['limit' => 9, 'null' => false, 'comment' => 'CEP'])
                ->addColumn('street', 'string', ['limit' => 150, 'null' => false, 'comment' => 'Logradouro'])
                ->addColumn('number', 'string', ['limit' => 10, 'null' => true, 'comment' => 'Número'])
                ->addColumn('complement', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Complemento'])
                ->addColumn('neighborhood', 'string', ['limit' => 100, 'null' => false, 'comment' => 'Bairro'])
                ->addColumn('city', 'string', ['limit' => 100, 'null' => false, 'comment' => 'Cidade'])
                ->addColumn('state', 'string', ['limit' => 2, 'null' => false, 'comment' => 'UF (ex: PA, SP)'])
                ->addColumn('created_at', 'datetime', ['null' => true])
                ->addColumn('updated_at', 'datetime', ['null' => true])
                ->create();
        }

        if (!$this->hasTable('adms_daman_user_addresses')) {
            // Define o nome da tabela
            $table = $this->table('adms_daman_user_addresses');

            // Define as colunas da tabela
            $table->addColumn('adms_daman_user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('adms_daman_address_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('created_at', 'datetime', ['null' => true])
                ->addForeignKey('adms_daman_user_id', 'adms_daman_users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('adms_daman_address_id', 'adms_daman_addresses', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

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
        $this->table('adms_daman_user_addresses')->drop()->save();
        $this->table('adms_daman_addresses')->drop()->save();
    }
}
