<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanAcquisitionPurchasingStatus extends AbstractSeed
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

        ## 1 COMPRADO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_purchasing_status WHERE name=:name', ['name' => 'COMPRADO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'COMPRADO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 2 CANCELADO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_acquisition_purchasing_status WHERE name=:name', ['name' => 'CANCELADO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'CANCELADO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Obter a tabela 'adms_daman_acquisition_purchasing_status' para inserir os registros
        $adms_daman_acquisition_purchasing_status = $this->table('adms_daman_acquisition_purchasing_status');

        // Insere os registros na tabela
        $adms_daman_acquisition_purchasing_status->insert($data)->save();
    }
}
