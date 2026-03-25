<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanOrders extends AbstractSeed
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
        $orders = [
            ['adms_daman_order_types_id' => 1, //Compra
            'adms_daman_category_id' => 4, // Eletrica/Logica
            'adms_daman_user_id' => 4, 
            'adms_daman_project_id' => 4, // Autozelio
            'service' => "Tomadas do térreo", 
            'expected_receipt_date' => date('Y-m-d H:i:s', strtotime('+3 days')), // Previsão de recebimento
            'observation' => 'Solicitado por Cleyton', 
            'adms_daman_order_status_id' => 1, // Analise
            'status_date' => date('Y-m-d H:i:s'),
            'rental_contract' => NULL, 
            'rental_period' => NULL, 
            'created_at' => date('Y-m-d H:i:s'),],

            ['adms_daman_order_types_id' => 2, //Locação
            'adms_daman_category_id' => 4, // Eletrica/Logica
            'adms_daman_user_id' => 4, 
            'adms_daman_project_id' => 2, // João Paulo
            'service' => "Tomadas aéreas do segundo pavimento", 
            'expected_receipt_date' => date('Y-m-d H:i:s', strtotime('+3 days')), // Previsão de recebimento
            'observation' => 'Solicitado por Cleyton', 
            'adms_daman_order_status_id' => 1, // Analise
            'status_date' => date('Y-m-d H:i:s'),
            'rental_contract' => NULL, 
            'rental_period' => '7', 
            'created_at' => date('Y-m-d H:i:s'),]
        ];


        // Percorrer o array com dados que devem ser validados antes de cadastrar
        foreach ($orders as $order) {

                $data[] = [
                    'adms_daman_order_types_id' => $order['adms_daman_order_types_id'],
                    'adms_daman_category_id' => $order['adms_daman_category_id'],
                    'adms_daman_user_id' => $order['adms_daman_user_id'],
                    'adms_daman_project_id' => $order['adms_daman_project_id'],
                    'service' => $order['service'],
                    'expected_receipt_date' => $order['expected_receipt_date'],
                    'observation' => $order['observation'],
                    'adms_daman_order_status_id' => $order['adms_daman_order_status_id'],
                    'status_date' => date("Y-m-d H:i:s"),
                    'rental_contract' => $order['rental_contract'],
                    'rental_period' => $order['rental_period'],
                    'created_at' => date("Y-m-d H:i:s"),
                ];
        }

        // Obtém a tabela 'adms_daman_orders' para inserir os registros
        $adms_daman_orders = $this->table('adms_daman_orders');

        // Insere os registros na tabela
        $adms_daman_orders->insert($data)->save();

    }
}
