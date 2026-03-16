<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanPages extends AbstractMigration
{
    /**
     * Cria a tabela AdmsDamanPages.
     *
     * Este método é executado durante a aplicação da migração para criar a tabela `adms_access_levels` no banco de dados.
     * A tabela é criada apenas se ela não existir, com as seguintes colunas:
     * - `name`: Nome da página (não pode ser nulo)
     * - `controller`: Nome da classe na controller (não pode ser nulo)
     * - `created_at`: Timestamp da criação do registro
     * - `updated_at`: Timestamp da última atualização do registro
     *
     * Referência:
     * - https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * 
     * @return void
     */
    public function up(): void
    {

        // Verifica se a tabela 'adms_daman_pages' não existe no banco de dados
        if (!$this->hasTable('adms_daman_pages')) {
            // Cria a tabela 'adms_daman_pages'
            $table = $this->table('adms_daman_pages');

            // Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false])
                ->addColumn('controller', 'string', ['null' => false])
                ->addColumn('controller_url', 'string', ['null' => false])
                ->addColumn('directory', 'string', ['null' => false])

                ->addColumn('obs', 'text', ['null' => true])

                ->addColumn('page_status', 'boolean', ['null' => false, 'default' => 0])

                ->addColumn('public_page', 'boolean', ['null' => false, 'default' => 0])

                ->addColumn('adms_daman_packages_page_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_packages_page_id', 'adms_daman_packages_pages', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('adms_daman_groups_page_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_groups_page_id', 'adms_daman_groups_pages', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->create();
        }
    }

    /**
     * Reverte a criação da tabela AdmsDamanPages.
     *
     * Este método é executado durante a reversão da migração para remover a tabela `adms_daman_pages` do banco de dados.
     * 
     * @return void
     */
    public function down(): void
    {
        // Remove a tabela 'adms_daman_pages' do banco de dados
        $this->table('adms_daman_pages')->drop()->save();
    }
}
