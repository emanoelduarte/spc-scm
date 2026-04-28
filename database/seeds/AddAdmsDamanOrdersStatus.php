<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanOrdersStatus extends AbstractSeed
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
        $data = [];

        ## 1 ANALISE
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_status WHERE name=:name', ['name' => 'ANALISE'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'ANALISE',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 2 ORÇAMENTO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_status WHERE name=:name', ['name' => 'ORÇAMENTO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'ORÇAMENTO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 3 COMPRADO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_status WHERE name=:name', ['name' => 'COMPRADO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'COMPRADO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 4 COMPRA PARCIAL
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_status WHERE name=:name', ['name' => 'COMPRA PARCIAL'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'COMPRA PARCIAL',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 5 ENTREGE
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_status WHERE name=:name', ['name' => 'ENTREGE'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'ENTREGE',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 6 ENTREGA PARCIAL
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_status WHERE name=:name', ['name' => 'ENTREGA PARCIAL'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'ENTREGA PARCIAL',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 7 LOCADO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_status WHERE name=:name', ['name' => 'LOCADO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'LOCADO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 8 DEVOLVIDO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_status WHERE name=:name', ['name' => 'DEVOLVIDO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'DEVOLVIDO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 9 DEV. PARCIAL
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_status WHERE name=:name', ['name' => 'DEV. PARCIAL'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'DEV. PARCIAL',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 9 CANCELADO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_status WHERE name=:name', ['name' => 'CANCELADO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'CANCELADO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }


        // Obter a tabela 'adms_daman_acquisition_status' para inserir os registros
        $adms_daman_acquisition_status = $this->table('adms_daman_acquisition_status');

        // Insere os registros na tabela
        $adms_daman_acquisition_status->insert($data)->save();

    }
}
