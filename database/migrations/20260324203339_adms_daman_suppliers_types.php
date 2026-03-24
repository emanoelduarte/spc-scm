<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Esta classe cria a tavela que armazena a nartureza de em que cada fornecedor pode se encaixar (locação, serviço e venda).
 */
final class AdmsDamanSuppliersTypes extends AbstractMigration
{
    public function up(): void
    {
        // Verificar se a tabela 'adms_daman_adms_daman_suppliers_types' não existe no banco de dados
        if (!$this->hasTable('adms_daman_suppliers_types')) {
            // Cria a tabela 'adms_daman_adms_daman_suppliers_types'
            $table = $this->table('adms_daman_suppliers_types');

            //Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false, 'comment' => '(locação, serviço, venda e etc.)'])
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->addIndex(['name'], ['unique' => true, 'name' => 'idx_unique_name']) // Adiciona o índice único com nome específico
                ->create();
        }
    }

    /**
     * Reverte a criação da tabela AdmsDamanSuppliersTypes
     * 
     * Este método é executado durante a reversão da migração para remover a tabela 'adms_daman_suppliers_types' do banco de dados.
     * 
     * @return void
     */
    public function down(): void
    {
        // Remover a tabela 'adms_daman_suppliers_types' do banco de dados
        $this->table('adms_daman_suppliers_types')->drop()->save();
    }
}
