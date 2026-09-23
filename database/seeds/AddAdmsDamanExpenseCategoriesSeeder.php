<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class AddAdmsDamanExpenseCategoriesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $categories = [
            'Material',
            'Mão de Obra',
            'Locação',
            'Serviços',
            'ART / Taxas Técnicas',
            'Impostos',
            'Combustível',
            'Energia',
            'Cartório',
            'Frete',
            'Reembolso',
            'Outros',
        ];

        foreach ($categories as $name) {
            $quotedName =
                $this->getAdapter()
                    ->getConnection()
                    ->quote($name);

            $existing = $this->fetchRow(
                "
                SELECT id
                FROM adms_daman_expense_categories
                WHERE name = {$quotedName}
                LIMIT 1
                "
            );

            if ($existing) {
                continue;
            }

            $this->table('adms_daman_expense_categories')
                ->insert([
                    [
                        'name' => $name,
                        'status' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ])
                ->saveData();
        }
    }
}
