<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class AddAdmsDamanFinancialPaymentMethodsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $paymentMethods = [
            'PIX',
            'Boleto',
            'Transferência Bancária',
            'Cartão de Crédito',
            'Cartão de Débito',
            'Dinheiro',
            'Débito Automático',
            'Cheque',
            'Outros',
        ];

        foreach ($paymentMethods as $name) {
            $quotedName =
                $this->getAdapter()
                    ->getConnection()
                    ->quote($name);

            $existing = $this->fetchRow(
                "
                SELECT id
                FROM adms_daman_financial_payment_methods
                WHERE name = {$quotedName}
                LIMIT 1
                "
            );

            if ($existing) {
                continue;
            }

            $this->table('adms_daman_financial_payment_methods')
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
