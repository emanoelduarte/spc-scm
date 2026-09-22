<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class AddAdmsDamanPaymentMethodItemsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $conditions = [
            1  => [0],                           // À VISTA/PIX
            2  => [5],                           // BOL. 5 DIAS
            3  => [7],                           // BOL. 7 DIAS
            4  => [10],                          // BOL. 10 DIAS
            5  => [15],                          // BOL. 15 DIAS
            6  => [20],                          // BOL. 20 DIAS
            7  => [21],                          // BOL. 21 DIAS
            8  => [28],                          // BOL. 28 DIAS
            9  => [30],                          // BOL. 30 DIAS
            10 => [30, 45],                      // BOL. 30/45 DIAS
            11 => [30, 45, 60],                  // BOL. 30/45/60 DIAS
            12 => [30, 60],                      // BOL. 30/60 DIAS
            13 => [30, 60, 90],                  // BOL. 30/60/90 DIAS
            14 => [30, 60, 90, 120],             // BOL. 30/60/90/120 DIAS
            15 => [30, 60, 90, 120, 150],        // .../150
            16 => [30, 60, 90, 120, 150, 180],   // .../180
            17 => [30, 60, 90, 120, 150, 180, 210],
            18 => [30, 60, 90, 120, 150, 180, 210, 240],
        ];

        $data = [];

        foreach ($conditions as $paymentMethodId => $days) {

            foreach ($days as $index => $day) {

                $data[] = [
                    'adms_daman_payment_method_id' => $paymentMethodId,
                    'installment_number' => $index + 1,
                    'days_after_purchase' => $day,
                    'percentage' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->table('adms_daman_payment_method_items')
            ->insert($data)
            ->saveData();
    }
}