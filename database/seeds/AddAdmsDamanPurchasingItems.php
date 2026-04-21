<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanPurchasingItems extends AbstractSeed
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

        /*
        |=======================================
        | PEDIDO 1 → COMPRA
        |=======================================
        */
        $items = [
            [
                'adms_daman_purchasing_id' => 1,
                'description' => 'Cabo elétrico 2.5mm',
                'adms_daman_measurement_units_id' => 1,
                'quantity' => 100,
                'purchased_quantity' => 100,
                'unit_price' => 3.50,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],
            [
                'adms_daman_purchasing_id' => 1,
                'description' => 'Luminária Preta 45W',
                'adms_daman_measurement_units_id' => 1,
                'quantity' => 20,
                'purchased_quantity' => 20,
                'unit_price' => 70.50,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],
            [
                'adms_daman_purchasing_id' => 1,
                'description' => 'Tomada 2P+T 110/220V Tramontina Ária',
                'adms_daman_measurement_units_id' => 1,
                'quantity' => 5,
                'purchased_quantity' => 2,
                'unit_price' => 18.90,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],

            /*
            |=======================================
            | PEDIDO 2 → COMPRA
            |=======================================
            */
            [
                'adms_daman_purchasing_id' => 2,
                'description' => 'Argamassa Auto Colante',
                'adms_daman_measurement_units_id' => 1,
                'quantity' => 5,
                'purchased_quantity' => 2,
                'unit_price' => 18.90,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => null
            ],
        ];

        foreach ($items as $item) {
            $data[] = $item;
        }

        $table = $this->table('adms_daman_purchasing_items');
        $table->insert($data)->save();
    }
}
