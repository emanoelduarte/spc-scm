<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seed responsável por cadastrar algumas categorias para uso dos pedidos no banco de dados
 */
class AddAdmsDamanCategories extends AbstractSeed
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

        ## 1 CARPINTARIA
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'CARPINTARIA'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'CARPINTARIA',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 2 CIVIL
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'CIVIL'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'CIVIL',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 3 IMPERMEABILIZAÇÃO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'IMPERMEABILIZAÇÃO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'IMPERMEABILIZAÇÃO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 4 ELETRICA/LÓGICA
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'ELETRICA/LOGICA'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'ELETRICA/LOGICA',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 5 EQUIPAMENTO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'EQUIPAMENTO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'EQUIPAMENTO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 6 EPI/EPC
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'EPI/EPC'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'EPI/EPC',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 7 ESG/HID
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'ESG/HID'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'ESG/HID',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 8 FERRAMENTA
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'FERRAMENTA'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'FERRAMENTA',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 9 FORRO/DRYWALL
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'FORRO/DRYWALL'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'FORRO/DRYWALL',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 10 MOBILIÁRIO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'MOBILIÁRIO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'MOBILIÁRIO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 11 PINTURA
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'PINTURA'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'PINTURA',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 12 REFRIGERAÇÃO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'REFRIGERAÇÃO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'REFRIGERAÇÃO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 13 SERRALHERIA
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'SERRALHERIA'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'SERRALHERIA',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 14 ESCRITÓRIO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_categories WHERE name=:name', ['name' => 'ESCRITÓRIO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'ESCRITÓRIO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }


        // Obter a tabela 'adms_access_levels' para inserir os registros
        $adms_daman_categories = $this->table('adms_daman_categories');

        // Insere os registros na tabela
        $adms_daman_categories->insert($data)->save();
    }
}
