<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanSupplierAddressesSeeder extends AbstractSeed
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

        // Se o usuário não existir, adiciona seus dados ao array $data

        for ($i = 1; $i <= 47; $i++) {
            $data[] = [
                'adms_daman_supplier_id' => $i,
                'adms_daman_address_id' => $i,
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Indicar em qual tabela deve adicionar/salvar o registro
        $adms_daman_supplier_addresses = $this->table('adms_daman_supplier_addresses');

        // Inserir registros na tabela
        $adms_daman_supplier_addresses->insert($data)->save();
    }
}