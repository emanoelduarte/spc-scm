<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanOrdersTypes extends AbstractSeed
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

        ## 1 COMPRA
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_order_types WHERE name=:name', ['name' => 'COMPRA'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'COMPRA',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 2 LOCAÇÃO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_order_types WHERE name=:name', ['name' => 'LOCAÇÃO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'LOCAÇÃO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }


        // Obter a tabela 'adms_daman_orders_type' para inserir os registros
        $adms_daman_order_types = $this->table('adms_daman_order_types');

        // Insere os registros na tabela
        $adms_daman_order_types->insert($data)->save();

    }
}
