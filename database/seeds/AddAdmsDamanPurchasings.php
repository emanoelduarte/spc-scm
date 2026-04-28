<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanPurchasings extends AbstractSeed
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
        $purchasings = [
            [
                'adms_daman_supplier_id' => 1, //Id do fornecedor
                'adms_daman_user_id' => 3, // Id do usuário comprador
                'adms_daman_acquisition_types_id' => 1, // Id do tipo de aquisição
                'adms_daman_acquisition_purchasing_status_id' => 1, // Id do status da compra
                'expected_receipt_date' => date('Y-m-d H:i:s', strtotime('+3 days')), // Previsão de recebimento
                'adms_daman_order_id' => 1,  // Id do pedido de compra
                'adms_daman_project_id' => 4, // Id da Obra Autozelio
                'service' => "Tomadas do térreo",
                'delivery_address' => "Passagem Lindolfo Collor, 68 - Marco, Belém - PA, 66095-310 - Entre: Av. Almirante Barroso e Passagem Getúlio Vargas",
                'delivery_value' => null,
                'discount' => null,
                'adms_daman_payment_methods_id' => 1, // À vista
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'adms_daman_supplier_id' => 1, //Id do fornecedor
                'adms_daman_user_id' => 3, // Id do usuário comprador
                'adms_daman_acquisition_types_id' => 1, // Id do tipo de aquisição
                'adms_daman_acquisition_purchasing_status_id' => 2, // Id do status da compra
                'expected_receipt_date' => date('Y-m-d H:i:s', strtotime('+3 days')), // Previsão de recebimento
                'adms_daman_order_id' => 2,  // Id do pedido de compra
                'adms_daman_project_id' => 2, // Id da Obra João Paulo
                'service' => "Calçada",
                'delivery_address' => "Av. João Paulo II, 1758 - Marco - Belém/PA - Entre: Travessa Dr. Enéias Pinheiros e Travessa Lomas Valentinas",
                'delivery_value' => null,
                'discount' => null,
                'adms_daman_payment_methods_id' => 1, // À vista
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];


        // Percorrer o array com dados que devem ser validados antes de cadastrar
        foreach ($purchasings as $purchasing) {

            $data[] = [
                'adms_daman_supplier_id' => $purchasing['adms_daman_supplier_id'],
                'adms_daman_user_id' => $purchasing['adms_daman_user_id'],
                'adms_daman_acquisition_types_id' => $purchasing['adms_daman_acquisition_types_id'],
                'adms_daman_acquisition_purchasing_status_id' => $purchasing['adms_daman_acquisition_purchasing_status_id'],
                'expected_receipt_date' => $purchasing['expected_receipt_date'],
                'adms_daman_order_id' => $purchasing['adms_daman_order_id'],
                'adms_daman_project_id' => $purchasing['adms_daman_project_id'],
                'service' => $purchasing['service'],
                'delivery_address' => $purchasing['delivery_address'],
                'delivery_value' => $purchasing['delivery_value'],
                'discount' => $purchasing['discount'],
                'adms_daman_payment_methods_id' => $purchasing['adms_daman_payment_methods_id'],
                'created_at' => $purchasing['created_at'],
            ];
        }

        // Obtém a tabela 'adms_daman_purchasings' para inserir os registros
        $adms_daman_purchasings = $this->table('adms_daman_purchasings');

        // Insere os registros na tabela
        $adms_daman_purchasings->insert($data)->save();
    }
}