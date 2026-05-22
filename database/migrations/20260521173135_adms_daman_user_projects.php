<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanUserProjects extends AbstractMigration
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
        // Verificar se a tabela 'adms_daman_user_projects' não existe no banco de dados
        if (!$this->hasTable('adms_daman_user_projects')) {

            // Cria a tabela 'adms_daman_user_projects'
            $table = $this->table('adms_daman_user_projects');

            //Define as colunas da tabela
            /**  
             * Coluna um define a chave estrangeira referente ao id do usuário, logo quando ponho ['delete' => 'RESTRICT'] informo ao banco de dados que nenhum usuário pode ser apagado/deletado (da tabela pai 'adms_daman_users') se ele tiver alguma obra vinculada a tabela aqui criada.
             * Ainda quando for feito alguma update de vinculação de obra na tabela aqui criada 'adms_daman_user_projects', deve fazer atualização em cascata mudando na tabela pai ('adms_daman_users').
             */
            $table->addColumn('adms_daman_user_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_user_id', 'adms_daman_users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])

                /**  
                 * Coluna dois define a chave estrangeira referente ao id da obra, logo quando ponho ['delete' => 'RESTRICT'] informo ao banco de dados que nenhuma obra pode ser apagada/deletada (da tabela pai 'adms_daman_project') se ele tiver alguma vinculação a tabela aqui criada.
                 * Ainda quando for feito alguma update de vinculação na tabela aqui criada 'adms_daman_user_projects', deve fazer atualização em cascata mudando na tabela pai 'adms_daman_project'.
                 */
                ->addColumn('adms_daman_project_id', 'integer', ['null' => false, 'signed' => false])
                ->addForeignKey('adms_daman_project_id', 'adms_daman_projects', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])

                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->create();
        }
    }

    /**
     * Reverte a criação da tabela AdmsDamanUsersProject.
     * 
     * Este método é executado durante a reversão da migração para remover a tabela 'adms_daman_user_projects' do banco de dados.
     * 
     * @return void
     */
    public function down(): void
    {
        // Remover a tabela 'adms_daman_project' do banco de dados
        $this->table('adms_daman_user_projects')->drop()->save();
    }
}