<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
/**
 * Migration responsável em cirar a tabela 'adms_daman_categories' que armazenará as categoria em que os pedidos poderam ser encaixar aos ser aplicado.
 */
final class AdmsDamanCategories extends AbstractMigration
{
    public function up(): void
    {
        // Verificar se a tabela 'adms_daman_categories' não existe no banco de dados
        if (!$this->hasTable('adms_daman_categories')) {
            // Cria a tabela 'adms_daman_adms_daman_categories'
            $table = $this->table('adms_daman_categories');

            //Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false, 'comment' => '(Carpintaria, Civil, Eletrica/Lógica, Impermeabilização e etc)'])
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->addIndex(['name'], ['unique' => true, 'name' => 'idx_unique_name']) // Adiciona o índice único com nome específico
                ->create();
        }
    }

    /**
     * Reverte a criação da tabela AdmsDamanCategories
     * 
     * Este método é executado durante a reversão da migração para remover a tabela 'adms_daman_categories' do banco de dados.
     * 
     * @return void
     */
    public function down(): void
    {
        // Remover a tabela 'adms_daman_categories' do banco de dados
        $this->table('adms_daman_categories')->drop()->save();
    }
}
