<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanPackagePages extends AbstractMigration
{
    /**
     * Cria a tabela AdmsDamanPackagesPages.
     *
     * Este método é executado durante a aplicação da migração para criar a tabela `adms_daman_packages_pages` no banco de dados.
     * A tabela é criada apenas se ela não existir, com as seguintes colunas:
     * - `name`: Nome do pacote (não pode ser nulo)
     * - `obs`: Observação sobre o pacote
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

        // Verifica se a tabela 'adms_daman_packages_pages' não existe no banco de dados
        if (!$this->hasTable('adms_daman_packages_pages')) {
            // Cria a tabela 'adms_daman_packages_pages'
            $table = $this->table('adms_daman_packages_pages');

            // Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false])
                ->addColumn('obs', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->create();
        }
    }

    /**
     * Reverte a criação da tabela AdmsPackagesPages.
     *
     * Este método é executado durante a reversão da migração para remover a tabela `adms_daman_packages_pages` do banco de dados.
     * 
     * @return void
     */
    public function down(): void
    {
        // Remove a tabela 'adms_daman_packages_pages' do banco de dados
        $this->table('adms_daman_packages_pages')->drop()->save();
    }
}
