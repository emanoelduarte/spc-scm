<?php

namespace App\admsDaman\Models\Services;

use App\admsDaman\Models\Repository\PurchaseInstallmentsRepository;
use App\admsDaman\Models\Repository\PurchaseInstallmentPaymentsRepository;
use DateTime;
use InvalidArgumentException;
use Throwable;

class PurchaseInstallmentPaymentService extends DbConnection
{
    /**
     * Registrar o pagamento de uma parcela.
     *
     * Responsabilidades:
     * - validar a parcela;
     * - validar os valores;
     * - calcular o saldo de principal;
     * - impedir pagamento acima do saldo;
     * - calcular o total efetivamente pago;
     * - registrar a movimentação;
     * - marcar a parcela como OK quando totalmente quitada.
     *
     * @param array $data
     * @return int ID do pagamento criado.
     */
    public function createPayment(
        array $data
    ): int {

        /*
         * =====================================================
         * DADOS PRINCIPAIS
         * =====================================================
         */

        $installmentId =
            (int) (
                $data['adms_daman_purchase_installment_id']
                ?? 0
            );

        $createdBy =
            (int) (
                $data['created_by']
                ?? 0
            );

        $financialPaymentMethodId =
            (int) (
                $data['adms_daman_financial_payment_method_id']
                ?? 0
            );

        $paymentDate =
            trim(
                (string) (
                    $data['payment_date']
                    ?? ''
                )
            );


        if ($installmentId <= 0) {

            throw new InvalidArgumentException(
                'Parcela inválida.'
            );
        }


        if ($createdBy <= 0) {

            throw new InvalidArgumentException(
                'Usuário responsável pela baixa não informado.'
            );
        }


        if ($financialPaymentMethodId <= 0) {

            throw new InvalidArgumentException(
                'Selecione a forma de pagamento.'
            );
        }


        /*
         * =====================================================
         * VALIDAR DATA DO PAGAMENTO
         * =====================================================
         */

        if (!$this->isValidDate(
            $paymentDate
        )) {

            throw new InvalidArgumentException(
                'Informe uma data de pagamento válida.'
            );
        }


        /*
         * Não permitir uma baixa com data futura.
         */
        if (
            $paymentDate
            > date('Y-m-d')
        ) {

            throw new InvalidArgumentException(
                'A data do pagamento não pode ser futura.'
            );
        }


        /*
         * =====================================================
         * CONVERTER VALORES PARA CENTAVOS
         * =====================================================
         *
         * Toda regra financeira é realizada utilizando
         * números inteiros para evitar problemas de precisão
         * com ponto flutuante.
         */

        $principalCents =
            $this->moneyToCents(
                $data['principal_amount']
                    ?? 0
            );

        $interestCents =
            $this->moneyToCents(
                $data['interest_amount']
                    ?? 0
            );

        $penaltyCents =
            $this->moneyToCents(
                $data['penalty_amount']
                    ?? 0
            );

        $discountCents =
            $this->moneyToCents(
                $data['discount_amount']
                    ?? 0
            );


        /*
         * A baixa deve liquidar algum valor
         * do principal da obrigação.
         */
        if ($principalCents <= 0) {

            throw new InvalidArgumentException(
                'O valor do principal deve ser maior que zero.'
            );
        }


        if (
            $interestCents < 0
            || $penaltyCents < 0
            || $discountCents < 0
        ) {

            throw new InvalidArgumentException(
                'Juros, multa e desconto não podem possuir valores negativos.'
            );
        }


        /*
         * =====================================================
         * CALCULAR TOTAL EFETIVAMENTE PAGO
         * =====================================================
         *
         * principal
         * + juros
         * + multa
         * - desconto
         */

        $totalPaidCents =
            $principalCents
            + $interestCents
            + $penaltyCents
            - $discountCents;


        if ($totalPaidCents <= 0) {

            throw new InvalidArgumentException(
                'O valor total pago deve ser maior que zero.'
            );
        }


        /*
         * O desconto não pode ultrapassar
         * o valor que está sendo liquidado
         * acrescido dos encargos.
         */
        if (
            $discountCents
            >
            (
                $principalCents
                + $interestCents
                + $penaltyCents
            )
        ) {

            throw new InvalidArgumentException(
                'O desconto informado é maior que o valor da baixa.'
            );
        }


        /*
         * =====================================================
         * INICIAR TRANSAÇÃO
         * =====================================================
         *
         * O pagamento e a alteração do status da parcela
         * precisam acontecer juntos.
         */

        $connection =
            $this->getConnection();

        $transactionStarted =
            false;


        try {

            if (!$connection->inTransaction()) {

                $connection->beginTransaction();

                $transactionStarted =
                    true;
            }


            $installmentsRepository =
                new PurchaseInstallmentsRepository();

            $paymentsRepository =
                new PurchaseInstallmentPaymentsRepository();


            /*
             * =================================================
             * RECUPERAR PARCELA
             * =================================================
             */

            $installment =
                $installmentsRepository
                ->getById(
                    $installmentId
                );


            if (!$installment) {

                throw new InvalidArgumentException(
                    'Parcela não encontrada.'
                );
            }


            /*
             * Não permitir nova baixa em parcela
             * já totalmente quitada.
             */
            if (
                ($installment['status'] ?? '')
                === 'OK'
            ) {

                throw new InvalidArgumentException(
                    'Esta parcela já está quitada.'
                );
            }


            /*
             * =================================================
             * VALOR ORIGINAL DA PARCELA
             * =================================================
             */

            $originalAmountCents =
                $this->moneyToCents(
                    $installment['original_amount']
                        ?? 0
                );


            if ($originalAmountCents <= 0) {

                throw new InvalidArgumentException(
                    'A parcela possui valor original inválido.'
                );
            }


            /*
             * =================================================
             * PRINCIPAL JÁ LIQUIDADO
             * =================================================
             *
             * Somente pagamentos ativos entram no cálculo.
             * Pagamentos estornados são ignorados.
             */

            $alreadyPaidPrincipal =
                $paymentsRepository
                ->getTotalPrincipalPaidByInstallmentId(
                    $installmentId
                );


            $alreadyPaidPrincipalCents =
                $this->moneyToCents(
                    $alreadyPaidPrincipal
                );


            /*
             * Saldo de principal ainda existente.
             */
            $remainingPrincipalCents =
                $originalAmountCents
                - $alreadyPaidPrincipalCents;


            if ($remainingPrincipalCents <= 0) {

                throw new InvalidArgumentException(
                    'Esta parcela não possui saldo de principal em aberto.'
                );
            }


            /*
             * =================================================
             * EVITAR BAIXA ACIMA DO SALDO
             * =================================================
             */

            if (
                $principalCents
                > $remainingPrincipalCents
            ) {

                throw new InvalidArgumentException(
                    'O principal informado é maior que o saldo da parcela. '
                        . 'Saldo disponível: R$ '
                        . $this->formatCentsToBr(
                            $remainingPrincipalCents
                        )
                        . '.'
                );
            }


            /*
             * =================================================
             * PREPARAR DADOS DO PAGAMENTO
             * =================================================
             */

            $paymentData = [

                'adms_daman_purchase_installment_id'
                => $installmentId,

                'payment_date'
                => $paymentDate,

                'adms_daman_financial_payment_method_id'
                => $financialPaymentMethodId,

                /*
                 * principal_amount representa quanto
                 * do principal da dívida está sendo
                 * liquidado nesta baixa.
                 */
                'principal_amount'
                => $this->centsToDecimal(
                    $principalCents
                ),

                'interest_amount'
                => $this->centsToDecimal(
                    $interestCents
                ),

                'penalty_amount'
                => $this->centsToDecimal(
                    $penaltyCents
                ),

                'discount_amount'
                => $this->centsToDecimal(
                    $discountCents
                ),

                /*
                 * Este é o valor efetivamente
                 * desembolsado.
                 */
                'total_paid'
                => $this->centsToDecimal(
                    $totalPaidCents
                ),

                'observation'
                => $data['observation']
                    ?? null,

                'created_by'
                => $createdBy,
            ];


            /*
             * =================================================
             * REGISTRAR PAGAMENTO
             * =================================================
             */

            $paymentId =
                $paymentsRepository
                ->createPayment(
                    $paymentData
                );


            if ($paymentId <= 0) {

                throw new InvalidArgumentException(
                    'Não foi possível registrar o pagamento.'
                );
            }


            /*
             * =================================================
             * VERIFICAR SE A PARCELA FOI QUITADA
             * =================================================
             */

            $newPaidPrincipalCents =
                $alreadyPaidPrincipalCents
                + $principalCents;


            /*
             * Quando o principal liquidado atingir
             * exatamente o valor original da parcela,
             * alterar o status para OK.
             */
            if (
                $newPaidPrincipalCents
                === $originalAmountCents
            ) {

                $updated =
                    $installmentsRepository
                    ->updateStatus(
                        $installmentId,
                        'OK'
                    );


                if (!$updated) {

                    throw new InvalidArgumentException(
                        'Não foi possível atualizar o status da parcela.'
                    );
                }
            }


            /*
             * =================================================
             * CONFIRMAR TRANSAÇÃO
             * =================================================
             */

            if ($transactionStarted) {

                $connection->commit();
            }


            return $paymentId;
        } catch (Throwable $e) {

            /*
             * Se qualquer etapa falhar:
             *
             * - não grava pagamento;
             * - não altera status;
             * - mantém o banco consistente.
             */
            if (
                $transactionStarted
                && $connection->inTransaction()
            ) {

                $connection->rollBack();
            }


            throw $e;
        }
    }


    /**
     * Converter valor monetário para centavos.
     *
     * Aceita:
     * 2500
     * 2500.00
     * 2500,00
     * 2.500,00
     * R$ 2.500,00
     */
    private function moneyToCents(
        mixed $value
    ): int {

        $value =
            trim(
                (string) $value
            );


        if ($value === '') {

            return 0;
        }


        /*
         * Remover símbolo monetário e espaços.
         */
        $value =
            str_replace(
                [
                    'R$',
                    ' ',
                ],
                '',
                $value
            );


        /*
         * Quando existir vírgula,
         * assumir formato brasileiro.
         *
         * 2.500,25
         * vira
         * 2500.25
         */
        if (
            str_contains(
                $value,
                ','
            )
        ) {

            $value =
                str_replace(
                    '.',
                    '',
                    $value
                );

            $value =
                str_replace(
                    ',',
                    '.',
                    $value
                );
        }


        /*
         * Validar número com no máximo
         * duas casas decimais.
         */
        if (
            !preg_match(
                '/^-?\d+(?:\.\d{1,2})?$/',
                $value
            )
        ) {

            throw new InvalidArgumentException(
                'Valor monetário inválido.'
            );
        }


        $negative =
            str_starts_with(
                $value,
                '-'
            );


        if ($negative) {

            $value =
                substr(
                    $value,
                    1
                );
        }


        $parts =
            explode(
                '.',
                $value,
                2
            );


        $reais =
            (int) (
                $parts[0]
                ?? 0
            );


        $centavos =
            str_pad(
                $parts[1]
                    ?? '0',
                2,
                '0',
                STR_PAD_RIGHT
            );


        $cents =
            ($reais * 100)
            + (int) $centavos;


        return $negative
            ? -$cents
            : $cents;
    }


    /**
     * Converter centavos para formato decimal
     * utilizado pelo banco.
     *
     * 250000 => 2500.00
     */
    private function centsToDecimal(
        int $cents
    ): string {

        $reais =
            intdiv(
                $cents,
                100
            );

        $centavos =
            $cents % 100;


        return sprintf(
            '%d.%02d',
            $reais,
            $centavos
        );
    }


    /**
     * Formatar centavos para exibição em BRL.
     */
    private function formatCentsToBr(
        int $cents
    ): string {

        return number_format(
            $cents / 100,
            2,
            ',',
            '.'
        );
    }


    /**
     * Validar data no formato Y-m-d.
     */
    private function isValidDate(
        string $date
    ): bool {

        $dateObject =
            DateTime::createFromFormat(
                'Y-m-d',
                $date
            );


        return
            $dateObject !== false
            && $dateObject->format(
                'Y-m-d'
            ) === $date;
    }

    /**
     * Estornar um pagamento realizado em uma parcela.
     *
     * O pagamento não é excluído. Ele passa para o status "reversed",
     * preservando todo o histórico financeiro.
     *
     * Após o estorno, o saldo da parcela é recalculado e, caso volte
     * a existir valor em aberto, o status da parcela também é reaberto.
     */
    public function reversePayment(
        int $paymentId,
        int $reversedBy,
        string $reason
    ): void {

        $reason = trim($reason);

        if ($paymentId <= 0) {
            throw new InvalidArgumentException(
                'Pagamento inválido.'
            );
        }

        if ($reversedBy <= 0) {
            throw new InvalidArgumentException(
                'Usuário responsável pelo estorno inválido.'
            );
        }

        if ($reason === '') {
            throw new InvalidArgumentException(
                'Informe o motivo do estorno.'
            );
        }

        $connection = $this->getConnection();

        $transactionStarted = false;

        try {

            /*
            * Iniciar uma transação para garantir que o estorno
            * do pagamento e a atualização da parcela ocorram
            * como uma única operação financeira.
            */
            if (!$connection->inTransaction()) {

                $connection->beginTransaction();

                $transactionStarted = true;
            }


            /*
            * Recuperar o pagamento que será estornado.
            */
            $paymentRepository =
                new PurchaseInstallmentPaymentsRepository();

            $payment =
                $paymentRepository->getPaymentById(
                    $paymentId
                );


            if (!$payment) {
                throw new InvalidArgumentException(
                    'Pagamento não encontrado.'
                );
            }


            /*
            * Um pagamento já estornado não pode ser
            * estornado novamente.
            */
            if (
                ($payment['status'] ?? '') !== 'active'
            ) {

                throw new InvalidArgumentException(
                    'Este pagamento já foi estornado.'
                );
            }


            $installmentId =
                (int) $payment['adms_daman_purchase_installment_id'];


            /*
            * Recuperar os dados da parcela relacionada
            * ao pagamento.
            */
            $installmentRepository =
                new PurchaseInstallmentsRepository();

            $installment =
                $installmentRepository->getById(
                    $installmentId
                );


            if (!$installment) {
                throw new InvalidArgumentException(
                    'Parcela relacionada ao pagamento não encontrada.'
                );
            }


            /*
            * Marcar o pagamento como estornado.
            *
            * O registro permanece no banco para preservar
            * o histórico da movimentação financeira.
            */
            $reversed =
                $paymentRepository->reversePayment(
                    $paymentId,
                    $reversedBy,
                    $reason
                );


            if (!$reversed) {
                throw new InvalidArgumentException(
                    'Não foi possível estornar o pagamento.'
                );
            }


            /*
            * Depois do estorno, calcular novamente quanto
            * de principal continua efetivamente pago.
            *
            * O repository considera somente pagamentos
            * com status "active".
            */
            $principalPaid =
                $paymentRepository
                ->getTotalPrincipalPaidByInstallmentId(
                    $installmentId
                );


            $originalAmount =
                (float) $installment['original_amount'];


            /*
            * Se o principal pago ficou abaixo do valor
            * original, a parcela voltou a possuir saldo.
            */
            if ($principalPaid < $originalAmount) {

                $newStatus =
                    $this->getOpenInstallmentStatus(
                        $installment['due_date']
                            ?? null
                    );


                $installmentRepository->updateStatus(
                    $installmentId,
                    $newStatus
                );
            }


            if ($transactionStarted) {
                $connection->commit();
            }
        } catch (Throwable $e) {

            if (
                $transactionStarted
                && $connection->inTransaction()
            ) {
                $connection->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Definir o status atual de uma parcela que possui saldo em aberto.
     *
     * AP = Permuta / parcela sem vencimento financeiro.
     * ON = Vencida.
     * AT = Vence hoje ou nos próximos 7 dias.
     * AV = Vencimento superior a 7 dias.
     */
    private function getOpenInstallmentStatus(
        ?string $dueDate
    ): string {

        /*
        * Parcelas sem vencimento são tratadas como AP.
        */
        if (
            empty($dueDate)
            || $dueDate === '0000-00-00'
        ) {
            return 'AP';
        }


        $today = new DateTime('today');

        $due =
            DateTime::createFromFormat(
                'Y-m-d',
                $dueDate
            );


        if (!$due) {
            throw new InvalidArgumentException(
                'Data de vencimento da parcela inválida.'
            );
        }


        $due->setTime(0, 0, 0);


        /*
        * Parcela vencida.
        */
        if ($due < $today) {
            return 'ON';
        }


        /*
        * Data limite para o status "Atenção".
        */
        $attentionLimit =
            (clone $today)->modify('+7 days');


        if ($due <= $attentionLimit) {
            return 'AT';
        }


        return 'AV';
    }
}
