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
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Jerônimo Pimentel'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Jerônimo Pimentel',
                'address' => 'R. Cônego Jerônimo Pimentel, 107 - Umarizal, CEP 66055-000 Belém/Pa. -  Entre: Av. Visconde Souza Franco e Tv. Alm. Wandenkolk',
                'description' => 'Construção de Galpão Comercial - TerraZoo',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Cesário Alvim'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Cesário Alvim',
                'address' => 'R. Cesário Alvim, 321 - Cidade Velha, Belém - PA, 66023-170 - Entre: Av. Bernando Sayão e Travessa de Breves',
                'description' => 'Construção de prédio comercial e apartamentos - Mafaro',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Porto Marina'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Porto Marina',
                'address' => 'Sem Endereço',
                'description' => 'Construção de Residência',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Alcindo Cacela'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Alcindo Cacela',
                'address' => 'Av. Alcindo Cacela, 662 - Umarizal, Belém - PA, 66060-000',
                'description' => 'Construção de Galpão Comercial',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Arbre'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Arbre',
                'address' => 'Sem Endereço',
                'description' => 'Sem informações',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Kia - Pós Venda'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Kia - Pós Venda',
                'address' => 'Av. João Paulo II, 579 - Marco, Belém - PA, 66095-492',
                'description' => 'Construção de Galpão Comercial - KIA Soul',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Posto Pariquis'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Posto Pariquis',
                'address' => 'R. dos Pariquis, Esquina com a 14 de Março',
                'description' => 'Reforma em posto de combustível',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Dunlop - Duque'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Dunlop - Duque',
                'address' => 'Av. Duque de Caxias, 937 - Marco, Belém - PA, 66093-027 - Entre: Tv. Mauriti e Barão do Triúnfo',
                'description' => 'Reforma em posto de combustível',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Almirante Barroso'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Almirante Barroso',
                'address' => 'Sem endereço',
                'description' => 'Construção de muro',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Arya Tower'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Arya Tower',
                'address' => 'Av. Alcindo Cacela, 813 - Umarizal, Belém - PA, 66065-267',
                'description' => 'Reforma sala comercial',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Pirajá'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Pirajá',
                'address' => 'Sem endereço',
                'description' => 'Sem informação',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Step By Step'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Step By Step',
                'address' => 'R. Diogo Móia, 1031 - 1069 - Umarizal, Belém - PA, 66055-170',
                'description' => 'Reforma em colégio',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Diogo Móia'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Diogo Móia',
                'address' => 'R. Diogo Móia, 358 - Umarizal, Belém - PA, 66055-171. Entre: Almirante Wanderkolk e Don Romoaldo de Seixas',
                'description' => 'Construção de prédio comercial',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Smile'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Smile',
                'address' => 'Av. Duque de Caxias, 589 - Marco, Belém - PA, 66093-026',
                'description' => 'Pequenas reformas em loja',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Gaspar Viana'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Gaspar Viana',
                'address' => 'Tv. Alferes Costa, S/N - Pedreira, Belém - PA, 66083-106',
                'description' => 'Reforma em hospital',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => "Bob's"])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => "Bob's",
                'address' => 'Sem Endereço',
                'description' => "Construção de uma franquia da Bob's",
                'status' => 0,
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
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => "Castanhal"])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => "Castanhal",
                'address' => 'Av. Pres. Getúlio Vargas, 3187 - Ianetama - Castanhal',
                'description' => "Construção de academia",
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => "Bernal do Couto"])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => "Bernal do Couto",
                'address' => 'R. Bernal do Couto, 126 - Umarizal, Belém - PA, 66055-080',
                'description' => "Reformas no Parazão",
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => "New Planejados"])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => "New Planejados",
                'address' => 'Tv. 14 de Março, 998 - Umarizal, Belém - PA, 66055-490',
                'description' => "Sem informações",
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Posto Montepio'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Posto Montepio',
                'address' => 'R. dos Mundurucus, 4734 - Jurunas, Belém - PA, 66063-023',
                'description' => 'Reforma em posto de combustível',
                'status' => 0,
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
                'description' => 'Construção de prédio comercial',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Canteiro Cosampa Paracuri'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Canteiro Cosampa Paracuri',
                'address' => 'Rua L Um, 708 - Paracuri (Icoaraci), Belém - PA, 66814-005',
                'description' => 'Reforma e construção de vestiários',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Almirante Barroso - 1393'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Almirante Barroso - 1393',
                'address' => 'Avenida Almirante Barroso, nº 1393 - Marco, CEP 66.093-020 Belém/Pa. -  Entre: Travessa Estrela e Mauriti',
                'description' => 'Construção de Galpão Comercial',
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
                'description' => 'Construção de Galpão Comercial',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Bonny Fit Santarém'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Bonny Fit Santarém',
                'address' => 'Av. Mendonça Furtado, 2300 - Aldeia, Santarém - PA, 68040-050 - Entre: Tv. Barjnas de Miranda - Tv. Silva Jardim',
                'description' => 'Construção de Academia',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Posto 25'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Posto 25',
                'address' => 'Av. Rômulo Maiorana - Marco, Belém - PA, 66093-081 - Entre: Tv. Antônio Bahena e Tv. das Mercês',
                'description' => 'Reforma em posto de combustível',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Bonny Fit Altamira'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Bonny Fit Altamira',
                'address' => 'Av. Tancredo Neves, 1441 - Esplanada do Xingu, Altamira - PA, 68372-573 - Entre: Tv. Búfalo e Tv. Batalho',
                'description' => 'Construção de Academia',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'FADESP - CEAMAZON'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'FADESP - CEAMAZON',
                'address' => 'Parque de Ciência e Tecnologia do Guamá - Av. Perimetral, 2651 - Guamá, Belém - PA, 66077-830',
                'description' => 'Reforma na UFPA',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Curso Alfa'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Curso Alfa',
                'address' => 'Av. Alm. Barroso, 2300 - Marco, Belém - PA, 66093-034',
                'description' => 'Reforma Cobertura Bombeiro - Curso Alfa',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Pirajá 2008'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Pirajá 2008',
                'address' => 'Tv. Pirajá, 2008 - Marco, Belém - PA, 66083-563',
                'description' => 'Manutenção Casa',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Tamandaré'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Tamandaré',
                'address' => 'Av. Alm. Tamandaré, 948 - Batista Campos, Belém - PA, 66020-000',
                'description' => 'Manutenção Casa',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL
            ];
        }

        // Verificar se o registro já existe no banco de dados
        $existingRecord = $this->query('SELECT id FROM adms_daman_projects WHERE name=:name', [':name' => 'Emagrecentro'])->fetch();

        // Testa a resposta da query, se o registro não existir ele insere os dados na variável $data para em seguida cadastrar na tabela
        if (!$existingRecord) {
            // Criar o array com os dados do usuário
            $data[] = [
                'name' => 'Emagrecentro',
                'address' => 'R. Eng. Fernando Guilhon, 1467 - Batista Campos, Belém - PA, 66033-454',
                'description' => 'Manutenção em clinica de emagrecimento',
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
