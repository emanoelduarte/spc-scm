<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUniqueContraintToAdmsDamanUsers extends AbstractMigration
{
    /**
     * Alterar as colunas email e username para serem únicas
     */
    public function up(): void
    {
         // Acessar o if quando a tabela existir no banco de dados
        if ($this->hasTable('adms_daman_users')) {
            // Alterar a tabela para adicionar incices unicos
            $table = $this->table('adms_daman_users');

            // Adicionar indíces únicos às colunas email e username
            // 'name' => 'idx_unique_email' - nomear o índice único     
            $table->addIndex(['email'], ['unique' => true, 'name' => 'idx_unique_email'])
                    ->addIndex(['username'], ['unique' => true, 'name' => 'idx_unique_username'])->update();
        }
    }

        /**
     * Metodo down() para reverter a migração (caso necessário)
     */
    public function down(): void
    {
        // Acessar o if quando a tabela existir no banco de dados
        if ($this->hasTable('adms_daman_users')) {
            // Indicar a tabela para remover os índices únicos das coliunas email e usarname

            $table = $this->table('adms_daman_users');

            // Remover os indíces únicos
            $table->removeIndexByName('idx_unique_email')
                    ->removeIndexByName('idx_unique_username')->update();
        }

    }
}
