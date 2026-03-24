<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUniqueContraintToAdmsDamanProjects extends AbstractMigration
{
    /**
     * Alterar a colunas name para ser única
     */
    public function up(): void
    {
         // Acessar o if quando a tabela existir no banco de dados
        if ($this->hasTable('adms_daman_projects')) {
            // Alterar a tabela para adicionar incices unicos
            $table = $this->table('adms_daman_projects');

            // Adicionar indíces únicos às colunas name
            // 'name' => 'idx_unique_name' - nomear o índice único     
            $table->addIndex(['name'], ['unique' => true, 'name' => 'idx_unique_name'])->update();
        }
    }

        /**
     * Metodo down() para reverter a migração (caso necessário)
     */
    public function down(): void
    {
        // Acessar o if quando a tabela existir no banco de dados
        if ($this->hasTable('adms_daman_projects')) {
            // Indicar a tabela para remover o índice único da coluna name

            $table = $this->table('adms_daman_projects');

            // Remover os indíces únicos
            $table->removeIndexByName('idx_unique_name')->update();
        }

    }
}
