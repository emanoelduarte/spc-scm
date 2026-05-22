<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanUserProjects extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Variável para receber os dados para cadastro
        $data = [];

        ## 5 - INGRYD - SOLICITANTE DE COMPRA E ALMOXARIFE - 33 EMAGRECENTRO
        // Verificar se o usuário e nível de acesso especificado já existe no banco de dados
        $existingRecord = $this->query(
            'SELECT id 
            FROM adms_daman_user_projects 
            WHERE adms_daman_user_id=:adms_daman_user_id
            AND adms_daman_project_id=:adms_daman_project_id',
            [':adms_daman_user_id' => 4, 'adms_daman_project_id' => 33]
        )->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'adms_daman_user_id' => 4,
                'adms_daman_project_id' => 33,
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Indicar em qual tabela deve adicionar/salvar o registro
        $adms_daman_users_access_levels = $this->table('adms_daman_user_projects');

        // Inserir registros na tabela
        $adms_daman_users_access_levels->insert($data)->save();
    }
}
