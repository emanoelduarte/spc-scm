<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanUsersAccessLevels extends AbstractSeed
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

        ## 1- EMANOEL - SUPER ADMINISTRADOR
        // Verificar se o usuário e nível de acesso especificado já existe no banco de dados
        $existingRecord = $this->query(
            'SELECT id 
            FROM adms_daman_users_access_levels 
            WHERE adms_daman_user_id=:adms_daman_user_id
            AND adms_daman_access_level_id=:adms_daman_access_level_id',
            [':adms_daman_user_id' => 1, 'adms_daman_access_level_id' => 1]
        )->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'adms_daman_user_id' => 1,
                'adms_daman_access_level_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 2- DAVI - ADMINISTRADOR
        // Verificar se o usuário e nível de acesso especificado já existe no banco de dados
        $existingRecord = $this->query(
            'SELECT id 
            FROM adms_daman_users_access_levels 
            WHERE adms_daman_user_id=:adms_daman_user_id
            AND adms_daman_access_level_id=:adms_daman_access_level_id',
            [':adms_daman_user_id' => 2, 'adms_daman_access_level_id' => 2]
        )->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'adms_daman_user_id' => 2,
                'adms_daman_access_level_id' => 2,
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 3- DAVI - FINANCEIRO
        // Verificar se o usuário e nível de acesso especificado já existe no banco de dados
        $existingRecord = $this->query(
            'SELECT id 
            FROM adms_daman_users_access_levels 
            WHERE adms_daman_user_id=:adms_daman_user_id
            AND adms_daman_access_level_id=:adms_daman_access_level_id',
            [':adms_daman_user_id' => 2, 'adms_daman_access_level_id' => 3]
        )->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'adms_daman_user_id' => 2,
                'adms_daman_access_level_id' => 3,
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 4- MARCOS - COMPRADOR 
        // Verificar se o usuário e nível de acesso especificado já existe no banco de dados
        $existingRecord = $this->query(
            'SELECT id 
            FROM adms_daman_users_access_levels 
            WHERE adms_daman_user_id=:adms_daman_user_id
            AND adms_daman_access_level_id=:adms_daman_access_level_id',
            [':adms_daman_user_id' => 3, 'adms_daman_access_level_id' => 4]
        )->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'adms_daman_user_id' => 3,
                'adms_daman_access_level_id' => 5,
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 5 - INGRYD - ALMOXARIFE
        // Verificar se o usuário e nível de acesso especificado já existe no banco de dados
        $existingRecord = $this->query(
            'SELECT id 
            FROM adms_daman_users_access_levels 
            WHERE adms_daman_user_id=:adms_daman_user_id
            AND adms_daman_access_level_id=:adms_daman_access_level_id',
            [':adms_daman_user_id' => 4, 'adms_daman_access_level_id' => 6]
        )->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'adms_daman_user_id' => 4,
                'adms_daman_access_level_id' => 6,
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 7 - INGRYD - SOLICITANTE DE COMPRA
        // Verificar se o usuário e nível de acesso especificado já existe no banco de dados
        $existingRecord = $this->query(
            'SELECT id 
            FROM adms_daman_users_access_levels 
            WHERE adms_daman_user_id=:adms_daman_user_id
            AND adms_daman_access_level_id=:adms_daman_access_level_id',
            [':adms_daman_user_id' => 4, 'adms_daman_access_level_id' => 7]
        )->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'adms_daman_user_id' => 4,
                'adms_daman_access_level_id' => 7,
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Indicar em qual tabela deve adicionar/salvar o registro
        $adms_daman_users_access_levels = $this->table('adms_daman_users_access_levels');

        // Inserir registros na tabela
        $adms_daman_users_access_levels->insert($data)->save();
    }
}
