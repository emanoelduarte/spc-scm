<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanSuppliersTypes extends AbstractSeed
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

    // Variável para receber os dados a serem inserido
        $data = [];

        ## VENDA
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_suppliers_types WHERE name=:name', ['name' => 'Venda'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Venda',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## LOCAÇÃO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_suppliers_types WHERE name=:name', ['name' => 'Locação'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Locação',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## SERVIÇO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_suppliers_types WHERE name=:name', ['name' => 'Serviço'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Serviço',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Obter a tabela 'adms_access_levels' para inserir os registros
        $adms_daman_suppliers_types = $this->table('adms_daman_suppliers_types');

        // Insere os registros na tabela
        $adms_daman_suppliers_types->insert($data)->save();

    }
}
