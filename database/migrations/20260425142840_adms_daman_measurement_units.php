<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanMeasurementUnits extends AbstractMigration
{
    public function up(): void
    {
        // Verificar se a tabela 'adms_daman_measurement_units' não existe no banco de dados
        if (!$this->hasTable('adms_daman_measurement_units')) {
            // Cria a tabela 'adms_daman_adms_daman_measurement_units'
            $table = $this->table('adms_daman_measurement_units');

            //Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false, 'comment' => '(M, m², m³ Un etc.)'])
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->addIndex(['name'], ['unique' => true, 'name' => 'idx_unique_name']) // Adiciona o índice único com nome específico
                ->create();
        }
    }

    /**
     * Reverte a criação da tabela AdmsDamanMeasurementUnits
     * 
     * Este método é executado durante a reversão da migração para remover a tabela 'adms_daman_measurement_units' do banco de dados.
     * 
     * @return void
     */
    public function down(): void
    {
        // Remover a tabela 'adms_daman_measurement_units' do banco de dados
        $this->table('adms_daman_measurement_units')->drop()->save();
    }
}
