<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanUsers extends AbstractSeed
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

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_users WHERE email=:email', [':email' => 'emanoel@damanarqeng.com.br'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Emanoel Duarte',
                'email' => 'emanoel@damanarqeng.com.br',
                'username' => 'emanoel@damanarqeng.com.br',
                'password' => password_hash('123456A#', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_users WHERE email=:email', [':email' => 'davi@damanarqeng.com.br'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Davi Costa',
                'email' => 'davi@damanarqeng.com.br',
                'username' => 'davi@damanarqeng.com.br',
                'password' => password_hash('123456A#', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_users WHERE email=:email', [':email' => 'marcos@damanarqeng.com.br'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Marcos Andrade',
                'email' => 'marcos@damanarqeng.com.br',
                'username' => 'marcos@damanarqeng.com.br',
                'password' => password_hash('123456A#', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_users WHERE email=:email', [':email' => 'ingryd@damanarqeng.com.br'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Ingryd Sousa',
                'email' => 'ingryd@damanarqeng.com.br',
                'username' => 'ingryd@damanarqeng.com.br',
                'password' => password_hash('123456A#', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_users WHERE email=:email', [':email' => 'cleyton@damanarqeng.com.br'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Cleyton Luis',
                'email' => 'cleyton@damanarqeng.com.br',
                'username' => 'cleyton@damanarqeng.com.br',
                'password' => password_hash('123456A#', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Indicar emq ual tabela deve adicionar/salvar o registro
        $adms_daman_users = $this->table('adms_daman_users');

        // Inserir registros na tabela
        $adms_daman_users->insert($data)->save();
    }
}
