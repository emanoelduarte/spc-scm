<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration responsável por criar a tabela 'adms_daman_material_stock_movements', que registra todos os itens do estoque
 */
final class AdmsDamanMaterialStockMovements extends AbstractMigration
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
        if(!$this->hasTable('adms_daman_material_stock_movements')) {

        //Definir o nome da tabela
        $table = $this->table('adms_daman_material_stock_movements');

        // Define as colunas da tabela
        $table->addColumn('adms_daman_material_stock_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Id do material na tabela de estoque'])
            ->addForeignKey('adms_daman_material_stock_id', 'adms_daman_material_stock', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

            ->addColumn('adms_daman_project_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Obra destino — para rastrear transferência'])
            ->addForeignKey('adms_daman_project_id', 'adms_daman_projects', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

            ->addColumn('adms_daman_user_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Id do usuário que fez a movimentação'])
            ->addForeignKey('adms_daman_user_id', 'adms_daman_users', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

            ->addColumn('type', 'string', ['null' => false, 'comment' => 'entrada/saida/transferencia'])

            ->addColumn('reason', 'string', ['null' => true, 'comment' => 'consumo  | transferencia | devolucao | descarte'])

            ->addColumn('quantity', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'comment' => 'Quantidade Movimentada'])

            ->addColumn('observation', 'text', ['null' => false, 'comment' => 'Informações sobre a movimentação'])

            ->addColumn('created_at', 'timestamp', ['null' => false])

            ->create();
        }
    }

    public function down(): void
    {
        $this->table('adms_daman_material_stock_movements')->drop()->save();
    }
}
