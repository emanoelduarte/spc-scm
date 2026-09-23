<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;
use RuntimeException;

final class AddAdmsDamanPaymentMethodItemsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        /*
         * =========================================================
         * REGRAS DAS CONDIÇÕES DE PAGAMENTO
         * =========================================================
         *
         * IMPORTANTE:
         *
         * Não usamos mais o ID da condição como chave.
         *
         * O ID pode variar entre:
         * - desenvolvimento;
         * - homologação;
         * - produção.
         *
         * O vínculo é feito pelo nome da condição.
         */
        $conditions = [

            'À VISTA/PIX' => [
                0,
            ],

            'BOL. 5 DIAS' => [
                5,
            ],

            'BOL. 7 DIAS' => [
                7,
            ],

            'BOL. 10 DIAS' => [
                10,
            ],

            'BOL. 15 DIAS' => [
                15,
            ],

            'BOL. 20 DIAS' => [
                20,
            ],

            'BOL. 21 DIAS' => [
                21,
            ],

            'BOL. 28 DIAS' => [
                28,
            ],

            'BOL. 30 DIAS' => [
                30,
            ],

            'BOL. 30/45 DIAS' => [
                30,
                45,
            ],

            'BOL. 30/45/60 DIAS' => [
                30,
                45,
                60,
            ],

            'BOL. 30/60 DIAS' => [
                30,
                60,
            ],

            'BOL. 30/60/90 DIAS' => [
                30,
                60,
                90,
            ],

            'BOL. 30/60/90/120 DIAS' => [
                30,
                60,
                90,
                120,
            ],

            'BOL. 30/60/90/120/150 DIAS' => [
                30,
                60,
                90,
                120,
                150,
            ],

            'BOL. 30/60/90/120/150/180 DIAS' => [
                30,
                60,
                90,
                120,
                150,
                180,
            ],

            'BOL. 30/60/90/120/150/180/210 DIAS' => [
                30,
                60,
                90,
                120,
                150,
                180,
                210,
            ],

            'BOL. 30/60/90/120/150/180/210/240 DIAS' => [
                30,
                60,
                90,
                120,
                150,
                180,
                210,
                240,
            ],
        ];


        foreach (
            $conditions
            as $paymentMethodName => $days
        ) {

            /*
             * =====================================================
             * LOCALIZAR A CONDIÇÃO NO AMBIENTE ATUAL
             * =====================================================
             */
            $paymentMethod =
                $this->query(
                    '
                        SELECT
                            id

                        FROM
                            adms_daman_payment_methods

                        WHERE
                            name = :name

                        LIMIT 1
                    ',
                    [
                        'name' =>
                            $paymentMethodName,
                    ]
                )
                ->fetch();


            /*
             * Não queremos uma implantação aparentemente concluída
             * com uma condição ausente.
             */
            if (!$paymentMethod) {

                throw new RuntimeException(
                    'Condição de pagamento não encontrada: '
                    . $paymentMethodName
                );
            }


            $paymentMethodId =
                (int) $paymentMethod['id'];


            /*
             * =====================================================
             * CRIAR / ATUALIZAR AS PARCELAS DA CONDIÇÃO
             * =====================================================
             */
            foreach (
                $days
                as $index => $day
            ) {

                $installmentNumber =
                    $index + 1;


                /*
                 * Verificar se a parcela já existe.
                 *
                 * Isso permite executar novamente o seed
                 * sem duplicar os registros.
                 */
                $existingItem =
                    $this->query(
                        '
                            SELECT
                                id

                            FROM
                                adms_daman_payment_method_items

                            WHERE
                                adms_daman_payment_method_id =
                                    :payment_method_id

                                AND installment_number =
                                    :installment_number

                            LIMIT 1
                        ',
                        [
                            'payment_method_id' =>
                                $paymentMethodId,

                            'installment_number' =>
                                $installmentNumber,
                        ]
                    )
                    ->fetch();


                if ($existingItem) {

                    /*
                     * Se já existir, manter a configuração
                     * sincronizada com este seed.
                     */
                    $this->execute(
                        '
                            UPDATE
                                adms_daman_payment_method_items

                            SET
                                days_after_purchase =
                                    :days_after_purchase,

                                percentage =
                                    NULL,

                                updated_at =
                                    :updated_at

                            WHERE
                                id =
                                    :id
                        ',
                        [
                            'days_after_purchase' =>
                                $day,

                            'updated_at' =>
                                $now,

                            'id' =>
                                (int) $existingItem['id'],
                        ]
                    );

                    continue;
                }


                /*
                 * Criar a configuração quando ainda não existir.
                 */
                $this->table(
                    'adms_daman_payment_method_items'
                )
                ->insert([
                    [
                        'adms_daman_payment_method_id' =>
                            $paymentMethodId,

                        'installment_number' =>
                            $installmentNumber,

                        'days_after_purchase' =>
                            $day,

                        'percentage' =>
                            null,

                        'created_at' =>
                            $now,

                        'updated_at' =>
                            $now,
                    ],
                ])
                ->saveData();
            }
        }
    }
}