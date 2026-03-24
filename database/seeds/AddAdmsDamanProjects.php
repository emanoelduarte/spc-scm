<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanProjects extends AbstractSeed
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
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Almirante Barroso'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Almirante Barroso',
                'address' => 'Avenida Almirante Barroso, nº 1393 - Marco, CEP 66.093-020 Belém/Pa. -  Entre: Travessa Estrela e Mauriti',
                'description' => 'Construção de pédio comercial',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'João Paulo II, 1758'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'João Paulo II, 1758',
                'address' => 'Av. João Paulo II, 1758 - Marco - Belém/PA - Entre: Travessa Dr. Enéias Pinheiros e Travessa Lomas Valentinas',
                'description' => 'Construção de pédio comercial',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Administração'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Administração',
                'address' => 'Tv. Quintino Bocaiúva, 2301 - Reduto, Belém - PA, 66045-315',
                'description' => 'Escritório comercial',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Autozélio'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Autozélio',
                'address' => 'Passagem Lindolfo Collor, 68 - Marco, Belém - PA, 66095-310 - Entre: Av. Almirante Barroso e Passagem Getúlio Vargas',
                'description' => 'Escritório comercial',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Indicar emq ual tabela deve adicionar/salvar o registro
        $adms_daman_projects = $this->table('adms_daman_projects');

        // Inserir registros na tabela
        $adms_daman_projects->insert($data)->save();

    }
}
