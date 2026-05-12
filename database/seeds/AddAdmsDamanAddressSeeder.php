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
                'zip_code'      => '66055-200',
                'street'        => 'Travessa Don Romoaldo de Seixas',
                'number'        => '23',
                'complement'    => null,
                'neighborhood'  => 'Umarizal',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66080-000',
                'street'        => 'Av. Pedro Miranda',
                'number'        => '2566',
                'complement'    => 'Esquina com a Alferes Costa',
                'neighborhood'  => 'Pedreira',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66050-350',
                'street'        => 'Rua Municipalidade',
                'number'        => '1157',
                'complement'    => null,
                'neighborhood'  => 'Umarizal',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66050-350',
                'street'        => 'ua Municipalidade',
                'number'        => '1634',
                'complement'    => null,
                'neighborhood'  => 'Umarizal',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '67110-000',
                'street'        => 'Rod. BR-316',
                'number'        => '04',
                'complement'    => 'Casa',
                'neighborhood'  => 'Coqueiro',
                'city'          => 'Ananideua',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66093-026',
                'street'        => 'Av. Duque de Caxias',
                'number'        => '500',
                'complement'    => null,
                'neighborhood'  => 'Marco',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66085-024',
                'street'        => 'Av. Pedro Miranda',
                'number'        => '2037',
                'complement'    => null,
                'neighborhood'  => 'Pedreira',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '08579-000',
                'street'        => 'Estrada do Bonsucesso',
                'number'        => '6001',
                'complement'    => null,
                'neighborhood'  => 'Rio Abaixo',
                'city'          => 'Itaquaquecetuba',
                'state'         => 'SP',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'zip_code'      => '66093-682',
                'street'        => 'Tv. Mauriti',
                'number'        => '4834 C',
                'complement'    => null,
                'neighborhood'  => 'Marco',
                'city'          => 'Belém',
                'state'         => 'PA',
                'created_at'    => date('Y-m-d H:i:s'),
            ],

            [
                'zip_code'      => '66055-000',
                'street'        => 'Rua Conego Jeronimo Pimentel',
                'number'        => '183',
                'complement'    => null,
                'neighborhood'  => 'Umarizal',
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
