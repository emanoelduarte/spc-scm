<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration responsável por adicionar duas colunas Recuperar senha e validação da recuperação. 
 * 
 * Adicionar colunas recover_password e validate_recover_password
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package Phinx\Migration\AbstractMigration
 */
final class AddRecoverPasswordToAdmsUsers extends AbstractMigration
{
    /**
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     */
    public function up(): void
    {

        // Acessa o if se a tabela adms_users existir
        if ($this->hasTable('adms_daman_users')) {

            // Variavel table recebe a dabela e depois usamos a varivável para fazer as adições das colunas recover_password e validate_recover_password
            $table = $this->table('adms_daman_users');

            $table->addColumn('recover_password', 'string', [
                'null' => true,
                'after' => 'password' // Indica que a coluna vai ser criada após a colunas 'password' que já está na tabela
            ])
                ->addColumn('validate_recover_password', 'datetime', [
                    'null' => true,
                    'after' => 'recover_password' // Indica que a coluna vai ser criada após a colunas 'recover_password' que já está na tabela
                ])
                ->update();
        }
    }

    // Método Down para fazer o rollback, caso necessário
    public function down(): void
    {
        // Acessa o if se a tabela adms_users existir
        if ($this->hasTable('adms_daman_users')) {

            // Variavel table recebe a dabela e depois usamos a varivável para fazer as remoções das colunas recover_password e validate_recover_password
            $table = $this->table('adms_daman_users');

            $table->removeColumn('recover_password')
                ->removeColumn('validate_recover_password')
                ->update();
        }
    }
}
