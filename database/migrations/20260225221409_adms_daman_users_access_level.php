<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Cria a tabela AdmsDamanUsersAccessLevel.
 *
 * Este método é executado durante a aplicação da migração para criar a tabela `adms_daman_users_access_levels` no banco de dados.
 * A tabela é criada apenas se ela não existir, com as seguintes colunas:
 * - `adms_daman_user_id`: Chave estrangeira como referencia a chave primaria da tabela 'adms_daman_users'
 * - `adms_daman_acess_level_id`: Chave estrangeira como referencia a chave primaria da tabela 'adms_daman_acess_levels'
 * - `created_at`: Timestamp da criação do registro
 * - `updated_at`: Timestamp da última atualização do registro
 *
 * Referência:
 * - https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
 * 
 * @return void
 */
final class AdmsDamanUsersAccessLevel extends AbstractMigration
{
    public function up(): void
    {
        // Verificar se a tabela 'adms_daman_access_levels' não existe no banco de dados
        if (!$this->hasTable('adms_daman_users_access_levels')) {

            // Cria a tabela 'adms_daman_access_levels'
            $table = $this->table('adms_daman_users_access_levels');

            //Define as colunas da tabela
            /**  
             * Coluna um define a chave estrangeira referente ao id do usuário, logo quando ponho ['delete' => 'RESTRICT'] informo ao banco de dados que nenhum usuário pode ser apagado/deletado (da tabela pai 'adms_daman_users') se ele tiver algum nível de acesso vinculado a tabela aqui criada.
             * Ainda quando for feito alguma update de nível de acesso na tabela aqui criada 'adms_daman_users_access_levels', deve fazer atualização em cascata mudando na tabela pai ('adms_daman_users').
             */
            $table->addColumn('adms_daman_user_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_user_id', 'adms_daman_users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])

                /**  
                 * Coluna um define a chave estrangeira referente ao id do nível de acesso, logo quando ponho ['delete' => 'RESTRICT'] informo ao banco de dados que nenhum nível de acesso pode ser apagado/deletado (da tabela pai 'adms_daman_access_levels') se ele tiver algum nível de acesso vinculado a tabela aqui criada.
                 * Ainda quando for feito alguma update de nível de acesso na tabela aqui criada 'adms_daman_users_access_levels', deve fazer atualização em cascata mudando na tabela pai 'adms_daman_access_levels'.
                 */
                ->addColumn('adms_daman_access_level_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_access_level_id', 'adms_daman_access_levels', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->create();
        }
    }

    /**
     * Reverte a criação da tabela AdmsDamanUsersAccessLevels.
     * 
     * Este método é executado durante a reversão da migração para remover a tabela 'adms_daman_users_access_levels' do banco de dados.
     * 
     * @return void
     */
    public function down(): void
    {
        // Remover a tabela 'adms_access_levels' do banco de dados
        $this->table('adms_daman_users_access_levels')->drop()->save();
    }
}
