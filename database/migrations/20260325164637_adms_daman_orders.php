<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migrations responsável em criar a tabela 'adms_daman_orders' para armazenar os pedidos
 */
final class AdmsDamanOrders extends AbstractMigration
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
        if (!$this->hasTable('adms_daman_orders')) {
            // Define o nome da tabela
            $table = $this->table('adms_daman_orders');

            // Define as colunas da tabela
            // Coluna de Compra ou Locação
            $table->addColumn('adms_daman_order_types_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'compra ou locação'])
                ->addForeignKey('adms_daman_order_types_id', 'adms_daman_order_types', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                // categoria do pedido
                ->addColumn('adms_daman_category_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'civil, eletrica, carpintaria e etc.'])
                ->addForeignKey('adms_daman_category_id', 'adms_daman_categories', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                // Colunas Gerais
                ->addColumn('adms_daman_user_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_user_id', 'adms_daman_users', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('adms_daman_project_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_project_id', 'adms_daman_projects', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('service', 'string', ['null' => false, 'comment' => 'Descrição do serviço para aplicação do material'])

                ->addColumn('expected_receipt_date', 'timestamp', ['null' => false, 'comment' => 'Data de previsão de recebimento'])

                ->addColumn('observation', 'text', ['null' => true, 'comment' => 'Observações do pedido'])                

                // Status
                ->addColumn('adms_daman_order_status_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_order_status_id', 'adms_daman_order_status', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
                
                ->addColumn('status_date', 'timestamp')

                ->addColumn('created_at', 'timestamp', ['null' => false, 'comment' => 'Data do pedido'])
                ->addColumn('updated_at', 'timestamp', ['null' => true])

                // Colunas de Locação
                ->addColumn('rental_contract', 'string', ['null' => true, 'comment' => 'Numero do contrato'])
                
                ->addColumn('rental_period', 'integer', ['null' => true, 'comment' => 'dias'])
            
                
                ->create();
        }
    }

    /**
     * Metodo down() para reverter a migração (caso necessário)
     */
    public function down():void
    {
        // Apagar a tabela adms_users
        $this->table('adms_daman_orders')->drop()->save();
    }
}

