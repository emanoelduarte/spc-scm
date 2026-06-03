<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddColumnAdmsDamanOrderComments extends AbstractMigration
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
    // Acessa o if se a tabela adms_daman_order_comments existir
        public function up(): void
    {

        // Acessa o if se a tabela adms_daman_order_comments existir
        if ($this->hasTable('adms_daman_order_comments')) {

            // Variavel table recebe a dabela e depois usamos a varivável para fazer a adição da coluna obs no cadastro na tabela.
            $table = $this->table('adms_daman_order_comments');

            $table->addColumn('adms_daman_approved_by', 'integer', [
                'null' => true,
                'signed' => false, 
                'comment' => 'Adm que aprovou ou rejeitou',
                'after' => 'adms_daman_user_id', // Indica que a coluna vai ser criada após a coluna 'adms_daman_user_id' que já está na tabela
            ])
            ->addForeignKey('adms_daman_approved_by', 'adms_daman_users', 'id', [
                'delete' => 'RESTRICT', 
                'update' => 'CASCADE'
                ])
            ->update();
        }
    }

    // Método Down para fazer o rollback, caso necessário
    public function down(): void
    {
        // Acessa o if se a tabela adms_daman_order_comments existir
        if ($this->hasTable('adms_daman_order_comments')) {

            // Variavel table recebe a dabela e depois usamos a varivável para fazer as remoção da coluna 'adms_daman_approved_by' na tabela.
            $table = $this->table('adms_daman_order_comments');

            $table->removeColumn('adms_daman_approved_by')
                ->update();
        }
    }
}
