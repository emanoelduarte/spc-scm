<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanAcquisitionTypes extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        // Verificar se a tabela 'adms_daman_acquisition_types' não existe no banco de dados
        if (!$this->hasTable('adms_daman_acquisition_types')) {
            // Cria a tabela 'adms_daman_adms_daman_acquisitions_type'
            $table = $this->table('adms_daman_acquisition_types');

            //Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false, 'comment' => '(Compra ou Locação)'])
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->addIndex(['name'], ['unique' => true, 'name' => 'idx_unique_name']) // Adiciona o índice único com nome específico
                ->create();
        }
    }
}
