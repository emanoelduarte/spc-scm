<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Classe respnsável por criar a tabela 'adms_daman_projects' que receberá todas as obras cadastradas
 */
final class AdmsDamanProjects extends AbstractMigration
{
    public function up() {
        // Acessa o if quando não existir a tabela no banco de dados
        if (!$this->hasTable('adms_daman_projects')) {
            // Define o nome da tabela
            $table = $this->table('adms_daman_projects');

            // Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false])
                ->addColumn('address', 'string', ['null' => false])
                ->addColumn('description', 'string', ['null' => false])
                ->addColumn('status', 'boolean', ['null' => false, 'default' => 1])
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
        $this->table('adms_daman_projects')->drop()->save();
    }
}
