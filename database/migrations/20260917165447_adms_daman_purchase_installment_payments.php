<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/*
 * Migration responsável por criar a estrutura de pagamentos/baixas das parcelas de compras.
 * Cada registro representa uma movimentação financeira realizada sobre uma parcela, mantendo
 * separado o valor original da obrigação e o valor efetivamente pago. A estrutura permite
 * registrar principal, juros, multa, desconto, pagamentos parciais e estornos, preservando
 * o histórico financeiro e possibilitando futuros relatórios de valores pagos, encargos,
 * descontos e fluxo de caixa.
 */
final class AdmsDamanPurchaseInstallmentPayments extends AbstractMigration
{
    public function up(): void
    {
        /*
         * =====================================================
         * PAGAMENTOS / BAIXAS DAS PARCELAS DE COMPRAS
         * =====================================================
         *
         * Esta tabela registra cada pagamento realizado
         * sobre uma parcela.
         *
         * Uma parcela pode possuir mais de um pagamento,
         * permitindo futuramente pagamentos parciais.
         *
         * O valor original da parcela permanece armazenado
         * em adms_daman_purchase_installments.
         *
         * Esta tabela representa o que efetivamente foi pago.
         */
        if (!$this->hasTable(
            'adms_daman_purchase_installment_payments'
        )) {

            $table = $this->table(
                'adms_daman_purchase_installment_payments'
            );

            $table

                /*
                 * Parcela à qual este pagamento pertence.
                 *
                 * RESTRICT evita apagar uma parcela que já
                 * possui movimentação financeira registrada.
                 */
                ->addColumn(
                    'adms_daman_purchase_installment_id',
                    'integer',
                    [
                        'null' => false,
                        'signed' => false,
                        'comment' =>
                            'ID da parcela da compra à qual este pagamento pertence.',
                    ]
                )

                ->addForeignKey(
                    'adms_daman_purchase_installment_id',
                    'adms_daman_purchase_installments',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )


                /*
                 * Data efetiva em que o pagamento ocorreu.
                 *
                 * Não utilizar created_at como data do pagamento,
                 * pois o lançamento pode ser realizado posteriormente.
                 */
                ->addColumn(
                    'payment_date',
                    'date',
                    [
                        'null' => false,
                        'comment' =>
                            'Data efetiva em que o pagamento da parcela foi realizado.',
                    ]
                )


                /*
                 * Valor da dívida principal que está sendo quitado.
                 *
                 * Exemplo:
                 * Parcela = R$ 2.500,00
                 * Principal pago = R$ 2.500,00
                 */
                ->addColumn(
                    'principal_amount',
                    'decimal',
                    [
                        'precision' => 15,
                        'scale' => 2,
                        'null' => false,
                        'default' => 0,
                        'comment' =>
                            'Valor pago referente ao principal da parcela, sem juros ou acréscimos.',
                    ]
                )


                /*
                 * Juros pagos por atraso ou outra condição.
                 */
                ->addColumn(
                    'interest_amount',
                    'decimal',
                    [
                        'precision' => 15,
                        'scale' => 2,
                        'null' => false,
                        'default' => 0,
                        'comment' =>
                            'Valor de juros acrescido ao pagamento da parcela.',
                    ]
                )


                /*
                 * Multa aplicada ao pagamento.
                 *
                 * Mantemos separado dos juros para permitir
                 * relatórios financeiros futuramente.
                 */
                ->addColumn(
                    'penalty_amount',
                    'decimal',
                    [
                        'precision' => 15,
                        'scale' => 2,
                        'null' => false,
                        'default' => 0,
                        'comment' =>
                            'Valor de multa acrescido ao pagamento da parcela.',
                    ]
                )


                /*
                 * Desconto concedido no momento da quitação.
                 *
                 * O desconto reduz o valor efetivamente pago.
                 */
                ->addColumn(
                    'discount_amount',
                    'decimal',
                    [
                        'precision' => 15,
                        'scale' => 2,
                        'null' => false,
                        'default' => 0,
                        'comment' =>
                            'Valor de desconto concedido no pagamento da parcela.',
                    ]
                )


                /*
                 * Valor total efetivamente desembolsado.
                 *
                 * Fórmula esperada:
                 *
                 * principal
                 * + juros
                 * + multa
                 * - desconto
                 */
                ->addColumn(
                    'total_paid',
                    'decimal',
                    [
                        'precision' => 15,
                        'scale' => 2,
                        'null' => false,
                        'default' => 0,
                        'comment' =>
                            'Valor total efetivamente pago após juros, multa e desconto.',
                    ]
                )


                /*
                 * Situação deste pagamento.
                 *
                 * active   = pagamento válido
                 * reversed = pagamento estornado
                 *
                 * Não apagaremos registros financeiros.
                 */
                ->addColumn(
                    'status',
                    'enum',
                    [
                        'values' => [
                            'active',
                            'reversed',
                        ],
                        'default' => 'active',
                        'null' => false,
                        'comment' =>
                            'Situação do pagamento: active para válido ou reversed para estornado.',
                    ]
                )


                /*
                 * Observações opcionais.
                 *
                 * Exemplos:
                 * - pagamento via PIX
                 * - juros negociados
                 * - boleto atualizado
                 */
                ->addColumn(
                    'observation',
                    'text',
                    [
                        'null' => true,
                        'comment' =>
                            'Observações relacionadas ao pagamento ou à baixa da parcela.',
                    ]
                )


                /*
                 * Usuário responsável por registrar a baixa.
                 */
                ->addColumn(
                    'created_by',
                    'integer',
                    [
                        'null' => false,
                        'signed' => false,
                        'comment' =>
                            'ID do usuário que registrou o pagamento da parcela.',
                    ]
                )

                ->addForeignKey(
                    'created_by',
                    'adms_daman_users',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )


                /*
                 * Data e hora em que a baixa foi registrada
                 * no sistema.
                 */
                ->addColumn(
                    'created_at',
                    'timestamp',
                    [
                        'null' => false,
                        'comment' =>
                            'Data e hora em que o pagamento foi registrado no sistema.',
                    ]
                )


                /*
                 * =================================================
                 * CAMPOS DE ESTORNO
                 * =================================================
                 */

                /*
                 * Usuário responsável pelo estorno.
                 */
                ->addColumn(
                    'reversed_by',
                    'integer',
                    [
                        'null' => true,
                        'signed' => false,
                        'comment' =>
                            'ID do usuário que realizou o estorno do pagamento.',
                    ]
                )

                ->addForeignKey(
                    'reversed_by',
                    'adms_daman_users',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )


                /*
                 * Momento em que o pagamento foi estornado.
                 */
                ->addColumn(
                    'reversed_at',
                    'timestamp',
                    [
                        'null' => true,
                        'comment' =>
                            'Data e hora em que o pagamento foi estornado.',
                    ]
                )


                /*
                 * Motivo obrigatório futuramente pelo Service
                 * quando houver estorno.
                 */
                ->addColumn(
                    'reversal_reason',
                    'text',
                    [
                        'null' => true,
                        'comment' =>
                            'Motivo informado para o estorno do pagamento.',
                    ]
                )


                /*
                 * Índices para consultas financeiras.
                 */
                ->addIndex(
                    [
                        'adms_daman_purchase_installment_id',
                    ],
                    [
                        'name' =>
                            'idx_purchase_installment_payment_installment',
                    ]
                )

                ->addIndex(
                    [
                        'payment_date',
                    ],
                    [
                        'name' =>
                            'idx_purchase_installment_payment_date',
                    ]
                )

                ->addIndex(
                    [
                        'status',
                    ],
                    [
                        'name' =>
                            'idx_purchase_installment_payment_status',
                    ]
                )

                ->create();
        }
    }


    public function down(): void
    {
        if ($this->hasTable(
            'adms_daman_purchase_installment_payments'
        )) {

            $this
                ->table(
                    'adms_daman_purchase_installment_payments'
                )
                ->drop()
                ->save();
        }
    }
}