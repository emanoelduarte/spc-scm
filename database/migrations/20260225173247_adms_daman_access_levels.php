<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanAccessLevels extends AbstractMigration
{
    /**
     * Cria a tabela AdmsDamanAcessLevels
     * 
     * Este método é executado durante a aplicação da migração para criar a tablea `adms_daman_access_levels` no banco de dados.
     * Tabela é criada apenas se ela não existir, com as seguintes colunas
     * `name`: Nome do nível de acesso (não pode ser nulo)
     * `order_levels`: Ordem do nível de acesso (não pode ser nulo)
     * `created_at`: Timestamp da criação do registro
     * `Update_at`: Timestamp da ultima atualização do registro
     * 
     * Referência: 
     * - http://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * 
     * @return void
     */
    public function up(): void
    {
        // Verificar se a tabela 'adms_daman_access_levels' não existe no banco de dados
        if (!$this->hasTable('adms_daman_access_levels')) {
            // Cria a tabela 'adms_daman_access_levels'
            $table = $this->table('adms_daman_access_levels');

            //Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false])
                ->addColumn('order_levels', 'integer', ['null' => false])
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->addIndex(['name'], ['unique' => true, 'name' => 'idx_unique_name']) // Adiciona o índice único com nome específico
                ->create();
        }
    }

    /**
     * Reverte a criação da tabela AdmsDamanAcessLevel
     * 
     * Este método é executado durante a reversão da migração para remover a tabela 'adms_daman_access_levels' do banco de dados.
     * 
     * @return void
     */
    public function down(): void
    {
        // Remover a tabela 'adms_daman_access_levels' do banco de dados
        $this->table('adms_daman_access_levels')->drop()->save();
    }
}
