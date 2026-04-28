<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanOrderItems extends AbstractSeed
{
    public function run(): void
    {
        $data = [];

        /*
        |=======================================
        | PEDIDO 1 → COMPRA
        |=======================================
        */
        $items = [
            [
                'adms_daman_order_id' => 1,
                'description' => 'Cabo elétrico 2.5mm',
                'adms_daman_measurement_units_id' => 1,
                'quantity' => 100,
                'purchased_quantity' => 100,
                'unit_price' => 3.50,

                // locação (NULL)
                'rented_quantity' => null,
                'returned_quantity' => null,
                'rental_start_date' => null,
                'rental_end_date' => null,

                'adms_daman_acquisition_status_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],
            [
                'adms_daman_order_id' => 1,
                'description' => 'Tomada dupla 10A',
                'adms_daman_measurement_units_id' => 1,
                'quantity' => 20,
                'purchased_quantity' => 20,
                'unit_price' => 12.90,

                // locação (NULL)
                'rented_quantity' => null,
                'returned_quantity' => null,
                'rental_start_date' => null,
                'rental_end_date' => null,

                'adms_daman_acquisition_status_id' => 2,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],
            [
                'adms_daman_order_id' => 1,
                'description' => 'Luminária Preta 45W',
                'adms_daman_measurement_units_id' => 1,
                'quantity' => 20,
                'purchased_quantity' => 20,
                'unit_price' => 70.50,

                // locação (NULL)
                'rented_quantity' => null,
                'returned_quantity' => null,
                'rental_start_date' => null,
                'rental_end_date' => null,

                'adms_daman_acquisition_status_id' => 2,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],
            [
                'adms_daman_order_id' => 1,
                'description' => 'Tomada 2P+T 110/220V Tramontina Ária',
                'adms_daman_measurement_units_id' => 1,
                'quantity' => 5,
                'purchased_quantity' => 2,
                'unit_price' => 18.90,

                // locação (NULL)
                'rented_quantity' => null,
                'returned_quantity' => null,
                'rental_start_date' => null,
                'rental_end_date' => null,

                'adms_daman_acquisition_status_id' => 2,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],

            /*
            |=======================================
            | PEDIDO 2 → LOCAÇÃO
            |=======================================
            */
            [
                'adms_daman_order_id' => 2,
                'description' => 'Andaime metálico',
                'adms_daman_measurement_units_id' => 13,
                'quantity' => 13,

                // compra (NULL)
                'purchased_quantity' => null,
                'unit_price' => null,

                // locação
                'rented_quantity' => 10,
                'returned_quantity' => 2,
                'rental_start_date' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'rental_end_date' => date('Y-m-d H:i:s', strtotime('+2 days')),

                'adms_daman_acquisition_status_id' => 3,
                'created_at' => date("Y-m-d H:i:s H:i:s"),
                'updated_at' => null
            ],
            [
                'adms_daman_order_id' => 2,
                'description' => 'Betoneira 400L',
                'adms_daman_measurement_units_id' => 13,
                'quantity' => 2,

                // compra (NULL)
                'purchased_quantity' => null,
                'unit_price' => null,

                // locação
                'rented_quantity' => 2,
                'returned_quantity' => 0,
                'rental_start_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'rental_end_date' => date('Y-m-d H:i:s', strtotime('+5 days')),


                'adms_daman_acquisition_status_id' => 4,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],
            /*
            |=======================================
            | PEDIDO 3 → COMPTRA
            |=======================================
            */
            [
                'adms_daman_order_id' => 3,
                'description' => 'Tomada 2P+T 110/220V Tramontina Ária',
                'adms_daman_measurement_units_id' => 1,
                'quantity' => 5,
                'purchased_quantity' => 2,
                'unit_price' => 18.90,

                // locação (NULL)
                'rented_quantity' => null,
                'returned_quantity' => null,
                'rental_start_date' => null,
                'rental_end_date' => null,

                'adms_daman_acquisition_status_id' => 2,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],
            /*
            |=======================================
            | PEDIDO 4 → COMPTRA
            |=======================================
            */
            [
                'adms_daman_order_id' => 4,
                'description' => 'Argamassa Auto Colante',
                'adms_daman_measurement_units_id' => 1,
                'quantity' => 5,
                'purchased_quantity' => 2,
                'unit_price' => 18.90,

                // locação (NULL)
                'rented_quantity' => null,
                'returned_quantity' => null,
                'rental_start_date' => null,
                'rental_end_date' => null,

                'adms_daman_acquisition_status_id' => 2,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],
        ];

        foreach ($items as $item) {
            $data[] = $item;
        }

        $table = $this->table('adms_daman_order_items');
        $table->insert($data)->save();
    }
}