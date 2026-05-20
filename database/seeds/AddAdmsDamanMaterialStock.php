<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanMaterialStock extends AbstractSeed
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

        $items = [
            [
                'name' => 'Cabo elétrico 2.5mm',
                'adms_daman_measurement_units_id' => 9,
                'adms_daman_project_id' => 2,
                'adms_daman_category_id' => 4,
                'current_quantity' => 100,
                'min_quantity' => 50,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Luminárias Preta 45W 6000k',
                'adms_daman_measurement_units_id' => 1,
                'adms_daman_project_id' => 1,
                'adms_daman_category_id' => 4,
                'current_quantity' => 25,
                'min_quantity' => 35,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Luminárias Branca 30W 6000k',
                'adms_daman_measurement_units_id' => 1,
                'adms_daman_project_id' => 3,
                'adms_daman_category_id' => 4,
                'current_quantity' => 25,
                'min_quantity' => 35,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Terminal Olha 6mm Azul',
                'adms_daman_measurement_units_id' => 1,
                'adms_daman_project_id' => 1,
                'adms_daman_category_id' => 4,
                'current_quantity' => 50,
                'min_quantity' => 20,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Tomada de Sobrepor 220V 20A Branca',
                'adms_daman_measurement_units_id' => 1,
                'adms_daman_project_id' => 1,
                'adms_daman_category_id' => 4,
                'current_quantity' => 3,
                'min_quantity' => 2,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Tomada de Sobrepor 220V 20A Branca',
                'adms_daman_measurement_units_id' => 1,
                'adms_daman_project_id' => 2,
                'adms_daman_category_id' => 4,
                'current_quantity' => 3,
                'min_quantity' => 2,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Tomada de Sobrepor 220V 20A Branca',
                'adms_daman_measurement_units_id' => 1,
                'adms_daman_project_id' => 3,
                'adms_daman_category_id' => 4,
                'current_quantity' => 3,
                'min_quantity' => 2,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Notebook ACER Aspire 5',
                'adms_daman_measurement_units_id' => 1,
                'adms_daman_project_id' => 1,
                'adms_daman_category_id' => 5,
                'current_quantity' => 25,
                'min_quantity' => 35,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Mouse Multilaser',
                'adms_daman_measurement_units_id' => 1,
                'adms_daman_project_id' => 1,
                'adms_daman_category_id' => 14,
                'current_quantity' => 50,
                'min_quantity' => 20,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Caneta Esferográfica Zul 0.3mm',
                'adms_daman_measurement_units_id' => 1,
                'adms_daman_project_id' => 1,
                'adms_daman_category_id' => 14,
                'current_quantity' => 5,
                'min_quantity' => 10,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Fonte Chaveada 400w',
                'adms_daman_measurement_units_id' => 1,
                'adms_daman_project_id' => 1,
                'adms_daman_category_id' => 4,
                'current_quantity' => 10,
                'min_quantity' => 20,
                'created_at' => date("Y-m-d H:i:s"),
            ],
        ];

        foreach ($items as $item) {

            $name        = $this->getAdapter()->getConnection()->quote($item['name']);
            $project_id = (int) ($item['adms_daman_project_id'] ?? 0);

            $rows = $this->fetchAll(
                "SELECT id FROM adms_daman_material_stock
                    WHERE name = $name
                    AND COALESCE(adms_daman_project_id, 0) = COALESCE($project_id, 0)"
            );

            if (empty($rows)) {
                $data[] = $item;
            }
        }

        if (!empty($data)) {
            $this->table('adms_daman_material_stock')->insert($data)->save();
        }
    }
}
