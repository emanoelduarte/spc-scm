<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migrations responsável por criar a tabela 'adms_daman_order_status' de status de pedido
 */
final class AdmsDamanOrderStatus extends AbstractMigration
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
    public function up() {
        // Acessa o if quando não existir a tabela no banco de dados
        // Verificar se a tabela 'adms_daman_order_status' não existe no banco de dados
        if (!$this->hasTable('adms_daman_order_status')) {
            // Cria a tabela 'adms_daman_order_status'
            $table = $this->table('adms_daman_order_status');

            //Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false, 'comment' => '(Análise, Comprado, Entrege e etc.)'])
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->addIndex(['name'], ['unique' => true, 'name' => 'idx_unique_name']) // Adiciona o índice único com nome específico
                ->create();
        }
    }

    /**
     * Metodo down() para reverter a migração (caso necessário)
     */
    public function down():void
    {
        // Apagar a tabela adms_daman_order_status
        $this->table('adms_daman_order_status')->drop()->save();
    }
}
