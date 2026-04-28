<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migrations responsável em criar a tabela 'adms_daman_purchasings' para armazenar os pedidos
 */
final class AdmsDamanPurchasings extends AbstractMigration
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
        if (!$this->hasTable('adms_daman_purchasings')) {
            // Define o nome da tabela
            $table = $this->table('adms_daman_purchasings');

            // Define as colunas da tabela
            $table->addColumn('adms_daman_supplier_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Id do fornecedor onde a compra vai ser efetuada'])
                ->addForeignKey('adms_daman_supplier_id', 'adms_daman_suppliers', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('adms_daman_user_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Id do usuário comprador'])
                ->addForeignKey('adms_daman_user_id', 'adms_daman_users', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('adms_daman_acquisition_types_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Id do tipo (compra locação)'])
                ->addForeignKey('adms_daman_acquisition_types_id', 'adms_daman_acquisition_types', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('adms_daman_acquisition_purchasing_status_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Id do tipo (compra locação)'])
                ->addForeignKey('adms_daman_acquisition_purchasing_status_id', 'adms_daman_acquisition_purchasing_status', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('expected_receipt_date', 'timestamp', ['null' => false, 'comment' => 'Data de previsão de recebimento'])

                ->addColumn('adms_daman_order_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_order_id', 'adms_daman_orders', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('adms_daman_project_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_project_id', 'adms_daman_projects', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('service', 'string', ['null' => false, 'comment' => 'Descrição do serviço para aplicação do material'])

                ->addColumn('delivery_address', 'string', ['null' => false, 'comment' => 'Endereço de entrega'])

                ->addColumn('adms_daman_payment_methods_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_payment_methods_id', 'adms_daman_payment_methods', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE']) // criar essa tabela

                ->addColumn('delivery_value', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'comment' => 'Valor do frete'])

                ->addColumn('discount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'comment' => 'Valor do desconto'])

                ->addColumn('created_at', 'timestamp', ['null' => false, 'comment' => 'Data da compra'])
                ->addColumn('updated_at', 'timestamp', ['null' => true])

                ->create();
        }
    }

    /**
     * Metodo down() para reverter a migração (caso necessário)
     */
    public function down(): void
    {
        // Apagar a tabela adms_users
        $this->table('adms_daman_purchasings')->drop()->save();
    }
}