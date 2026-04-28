<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanOrderComments extends AbstractMigration
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
        if (!$this->hasTable('adms_daman_order_comments')) {
            // Define o nome da tabela
            $table = $this->table('adms_daman_order_comments');

            $table->addColumn('adms_daman_order_id', 'integer', ['null' => true, 'signed' => false])
                ->addForeignKey('adms_daman_order_id', 'adms_daman_orders', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])

                ->addColumn('adms_daman_user_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_user_id', 'adms_daman_users', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('type', 'enum', [
                    'values' => ['auto', 'manual'],
                    'default' => 'auto',
                    'null' => false
                ])
                ->addColumn('action', 'string', ['null' => false, 'comment' => '(update_item | update_status | create | delete | comment)'])
                ->addColumn('field', 'string', ['null' => true, 'comment' => '(price, description, quantity, status, etc)'])

                ->addColumn('old_value', 'text', ['null' => true, 'comment' => 'xxxxxxx'])
                ->addColumn('new_value', 'text', ['null' => true, 'comment' => 'xxxxxxx'])
                ->addColumn('adms_daman_order_item_id', 'integer', ['null' => true, 'signed' => false])
                ->addForeignKey('adms_daman_order_item_id', 'adms_daman_order_items', 'id', [
                    'delete' => 'SET_NULL',
                    'update' => 'CASCADE'
                ])
                ->addColumn('comment', 'text', ['null' => true, 'comment' => 'xxxxxxx'])
                ->addColumn('created_at', 'timestamp', ['null' => false, 'comment' => 'Data do comentário'])

                ->create();
        }
    }

    /**
     * Metodo down() para reverter a migração (caso necessário)
     */
    public function down(): void
    {
        // Apagar a tabela adms_users
        $this->table('adms_daman_orders')->drop()->save();
    }
}
