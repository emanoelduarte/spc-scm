<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanAddressSeeder extends AbstractSeed
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
        // Variável para receber os dados a serem inseridos
        $data = [];

        // Variável para receber os dados que devem ser validados antes de cadastrar
        $addresses = [
            [
                'zip_code'      => '66010-000',
                'street'        => 'Avenida Presidente Vargas',
                'number'        => '800',
                'complement'    => 'Sala 201',
                'neighborhood'  => 'Campina',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66055-260',
                'street'        => 'Travessa Benjamim Constant',
                'number'        => '1150',
                'complement'    => null,
                'neighborhood'  => 'Nazaré',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66093-005',
                'street'        => 'Rodovia BR-316',
                'number'        => '2500',
                'complement'    => 'Galpão A',
                'neighborhood'  => 'Águas Lindas',
                'city'          => 'Ananindeua',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '68040-020',
                'street'        => 'Avenida Marechal Rondon',
                'number'        => '450',
                'complement'    => null,
                'neighborhood'  => 'Centro',
                'city'          => 'Santarém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66613-080',
                'street'        => 'Passagem Mariana',
                'number'        => '23',
                'complement'    => 'Casa',
                'neighborhood'  => 'Sacramenta',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66820-080',
                'street'        => 'Avenida Independência',
                'number'        => 'S/N',
                'complement'    => null,
                'neighborhood'  => 'Icoaraci',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '68005-010',
                'street'        => 'Rua Siqueira Mendes',
                'number'        => '310',
                'complement'    => 'Bloco B',
                'neighborhood'  => 'Centro',
                'city'          => 'Castanhal',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66115-000',
                'street'        => 'Avenida Augusto Montenegro',
                'number'        => '4000',
                'complement'    => 'Loja 5',
                'neighborhood'  => 'Parque Verde',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        // Percorrer o array com dados que devem ser validados antes de cadastrar
        foreach ($addresses as $address) {

            $data[] = [
                'zip_code' => $address['zip_code'],
                'street' => $address['street'],
                'number' => $address['number'],
                'complement' => $address['complement'],
                'neighborhood' => $address['neighborhood'],
                'city' => $address['city'],
                'state' => $address['state'],
                'created_at' => $address['created_at'],
            ];
        }

        // Obtém a tabela 'adms_daman_addresses' para inserir os registros
        $adms_daman_addresses = $this->table('adms_daman_addresses');

        // Insere os registros na tabela
        $adms_daman_addresses->insert($data)->save();
    }
}
