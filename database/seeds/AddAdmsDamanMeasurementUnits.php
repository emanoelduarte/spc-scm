<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanMeasurementUnits extends AbstractSeed
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

        ## 1 Un
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Un'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Un',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 2 Bd
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Bd'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Bd',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 3 Ct
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Ct'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Ct',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 4 Dz
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Dz'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Dz',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 5 Gl
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Gl'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Gl',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 6 Kg
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Kg'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Kg',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 7 Lt
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Lt'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Lt',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 8 Lata
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Lata'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Lata',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 9 M
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'M'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'M',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 10 M²
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'M²'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'M²',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 11 M³
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'M³'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'M³',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 12 Par
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Par'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Par',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 13 Pç
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Pç'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Pç',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 14 Pct
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Pct'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Pct',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 15 Kit
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Kit'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Kit',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 16 Cx
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_measurement_units WHERE name=:name', ['name' => 'Cx'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Cx',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }


        // Obter a tabela 'adms_access_levels' para inserir os registros
        $adms_daman_measurement_units = $this->table('adms_daman_measurement_units');

        // Insere os registros na tabela
        $adms_daman_measurement_units->insert($data)->save();

    }
}
