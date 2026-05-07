<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEmailToAdmsDamanSuppliers extends AbstractMigration
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

        // Acessa o if se a tabela adms_daman_suppliers existir
        if ($this->hasTable('adms_daman_suppliers')) {

            // Variavel table recebe a dabela e depois usamos a varivável para fazer a adição da coluna email no cadastro na tabela.
            $table = $this->table('adms_daman_suppliers');

            $table->addColumn('email', 'string', [
                'null' => true,
                'after' => 'phone' // Indica que a coluna vai ser criada após a coluna 'phone' que já está na tabela
            ])
                ->update();
        }
    }

    // Método Down para fazer o rollback, caso necessário
    public function down(): void
    {
        // Acessa o if se a tabela adms_daman_suppliers existir
        if ($this->hasTable('adms_daman_suppliers')) {

            // Variavel table recebe a dabela e depois usamos a varivável para fazer as remoções das colunas recover_password e validate_recover_password
            $table = $this->table('adms_daman_suppliers');

            $table->removeColumn('email')
                ->update();
        }
    }
}
