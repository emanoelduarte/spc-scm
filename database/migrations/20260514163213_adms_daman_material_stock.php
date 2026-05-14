<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration responsável por criar a tabela 'adms_daman_material_stock', que registra todos os itens do estoque
 */
final class AdmsDamanMaterialStock extends AbstractMigration
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
    public function up(): void
    {
        // Acessa o if quando não existir a tabela no banco de dados
        if(!$this->hasTable('adms_daman_material_stock')) {

        //Definir o nome da tabela
        $table = $this->table('adms_daman_material_stock');

        // Define as colunas da tabela
        $table->addColumn('name', 'string', ['null' => false, 'comment' => 'Nome/Descrição do item'])
            ->addColumn('adms_daman_measurement_units_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Id da unidade de medida'])
            ->addForeignKey('adms_daman_measurement_units_id', 'adms_daman_measurement_units', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

            ->addColumn('adms_daman_project_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Id da Obra de alocamento'])
            ->addForeignKey('adms_daman_project_id', 'adms_daman_projects', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

            ->addColumn('current_quantity', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'comment' => 'Quantidade atual no estoque'])

            ->addColumn('min_quantity', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'comment' => 'Quantidade mínima no estoque'])

            ->addColumn('created_at', 'timestamp', ['null' => false])
            ->addColumn('updated_at', 'timestamp', ['null' => true])

            ->addIndex(['name', 'adms_daman_project_id'], ['unique' => true, 'name' => 'uq_name'])

            ->create();

        }
    }

    public function down(): void
    {
        $this->table('adms_daman_material_stock')->drop()->save();
    }
}
