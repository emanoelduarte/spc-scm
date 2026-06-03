<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddColumnToAdmsDamanMaterialStock extends AbstractMigration
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
    // Acessa o if se a tabela adms_daman_material_stock existir
        public function up(): void
    {

        // Acessa o if se a tabela adms_daman_material_stock existir
        if ($this->hasTable('adms_daman_material_stock')) {

            // Variavel table recebe a dabela e depois usamos a varivável para fazer a adição da coluna obs no cadastro na tabela.
            $table = $this->table('adms_daman_material_stock');

            $table->addColumn('obs', 'text', [
                'null' => true,
                'after' => 'min_quantity' // Indica que a coluna vai ser criada após a coluna 'min_quantity' que já está na tabela
            ])
                ->update();
        }
    }

    // Método Down para fazer o rollback, caso necessário
    public function down(): void
    {
        // Acessa o if se a tabela adms_daman_material_stock existir
        if ($this->hasTable('adms_daman_material_stock')) {

            // Variavel table recebe a dabela e depois usamos a varivável para fazer as remoções das colunas recover_password e validate_recover_password
            $table = $this->table('adms_daman_material_stock');

            $table->removeColumn('obs')
                ->update();
        }
    }
}
