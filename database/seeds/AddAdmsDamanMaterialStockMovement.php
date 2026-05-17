<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanMaterialStockMovement extends AbstractSeed
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

        $moviments = [
            [
                'adms_daman_material_stock_id' => 1,
                'adms_daman_project_id' => 1,
                'adms_daman_user_id' => 4,
                'type' => 'output',
                'reason' => 'transfer',
                'observation' => 'Solicitado para usar na obra de destino',
                'quantity' => 20,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'adms_daman_material_stock_id' => 1,
                'adms_daman_project_id' => 1,
                'adms_daman_user_id' => 4,
                'type' => 'input',
                'reason' => null,
                'observation' => null,
                'quantity' => 10,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'adms_daman_material_stock_id' => 4,
                'adms_daman_project_id' => 1,
                'adms_daman_user_id' => 4,
                'type' => 'output',
                'reason' => 'consumption',
                'observation' => null,
                'quantity' => 10,
                'created_at' => date("Y-m-d H:i:s"),
            ],
            [
                'adms_daman_material_stock_id' => 4,
                'adms_daman_project_id' => 1,
                'adms_daman_user_id' => 4,
                'type' => 'input',
                'reason' => null,
                'observation' => 'Uso em obra',
                'quantity' => 10,
                'created_at' => date("Y-m-d H:i:s"),
            ],

        ];

        foreach ($moviments as $moviment) {
            $data[] = $moviment;
        }

        $table = $this->table('adms_daman_material_stock_movements');
        $table->insert($data)->save();
    }
}