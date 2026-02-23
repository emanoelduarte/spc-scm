<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Classe para criar migrations para adicionar tabela AdmsUsers
 */
final class AdmsDamanUsers extends AbstractMigration
{
    
/**
     * Cria a tabela AdmsUsers
     * 
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * 
     */
        public function up(): void
    {

        // Acessa o if quando não existir a tabela no banco de dados
        if (!$this->hasTable('adms_daman_users')) {
            // Define o nome da tabela
            $table = $this->table('adms_daman_users');

            // Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false])
                ->addColumn('email', 'string', ['null' => false])
                ->addColumn('username', 'string', ['null' => false])
                ->addColumn('password', 'string', ['null' => false])
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
        $this->table('adms_daman_users')->drop()->save();
    }
}
