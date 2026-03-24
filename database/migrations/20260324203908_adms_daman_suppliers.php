<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Esta classe cria a tabela que armazena os cadastros de fornecedores da empresa
 */
final class AdmsDamanSuppliers extends AbstractMigration
{
    public function up() {
        // Acessa o if quando não existir a tabela no banco de dados
        if (!$this->hasTable('adms_daman_suppliers')) {
            // Define o nome da tabela
            $table = $this->table('adms_daman_suppliers');

            // Define as colunas da tabela
            $table->addColumn('legal_name', 'string', ['null' => false, 'comment' => 'Razão Social'])
                ->addColumn('trade_name', 'string', ['null' => false, 'comment' => 'Nome fantasia'])
                ->addColumn('cnpj', 'string', ['null' => false, 'comment' => 'Registro de Pesso jurídica'])
                ->addColumn('contact_name', 'string', ['null' => false, 'comment' => 'Contato (nome da pessoa)'])
                ->addColumn('phone', 'text', ['null' => false])

                ->addColumn('adms_daman_suppliers_types_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_suppliers_types_id', 'adms_daman_suppliers_types', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
                
                ->addColumn('supplier_status', 'boolean', ['null' => false, 'default' => 1])
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->create();
        }
    }

    /**
     * Metodo down() para reverter a migração (caso necessário)
     */
    public function down():void
    {
        // Apagar a tabela adms_users
        $this->table('adms_daman_suppliers')->drop()->save();
    }
}
