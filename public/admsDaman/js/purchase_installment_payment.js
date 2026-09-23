(function () {

    'use strict';


    const modal =
        document.getElementById(
            'installmentPaymentModal'
        );


    if (!modal) {
        return;
    }


    const installmentId =
        document.getElementById(
            'payment_installment_id'
        );

    const installmentNumber =
        document.getElementById(
            'payment_installment_number'
        );

    const dueDate =
        document.getElementById(
            'payment_due_date'
        );

    const originalAmount =
        document.getElementById(
            'payment_original_amount'
        );

    const remainingPrincipal =
        document.getElementById(
            'payment_remaining_principal'
        );

    const principalAmount =
        document.getElementById(
            'principal_amount'
        );

    const interestAmount =
        document.getElementById(
            'interest_amount'
        );

    const penaltyAmount =
        document.getElementById(
            'penalty_amount'
        );

    const discountAmount =
        document.getElementById(
            'discount_amount'
        );

    const totalPaid =
        document.getElementById(
            'payment_total_paid'
        );

    const financialPaymentMethod =
        document.getElementById(
            'payment_financial_payment_method_id'
        );


    /*
     * Converter valor para centavos.
     */
    function moneyToCents(value) {

        value =
            String(value ?? '')
                .trim()
                .replace('R$', '')
                .replace(/\s/g, '');


        if (value === '') {
            return 0;
        }


        if (value.includes(',')) {

            value =
                value
                    .replace(/\./g, '')
                    .replace(',', '.');
        }


        const number =
            Number(value);


        if (Number.isNaN(number)) {
            return 0;
        }


        return Math.round(
            number * 100
        );
    }


    /*
     * Formatar BRL.
     */
    function formatMoney(cents) {

        return (
            cents / 100
        ).toLocaleString(
            'pt-BR',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );
    }


    /*
     * Recalcular total pago.
     */
    function recalculateTotal() {

        const principal =
            moneyToCents(
                principalAmount.value
            );

        const interest =
            moneyToCents(
                interestAmount.value
            );

        const penalty =
            moneyToCents(
                penaltyAmount.value
            );

        const discount =
            moneyToCents(
                discountAmount.value
            );


        const total =
            principal
            + interest
            + penalty
            - discount;


        totalPaid.value =
            'R$ '
            + formatMoney(
                Math.max(
                    total,
                    0
                )
            );
    }


    /*
     * Preencher modal.
     */
    modal.addEventListener(
        'show.bs.modal',
        function (event) {

            const button =
                event.relatedTarget;


            if (!button) {
                return;
            }


            const id =
                button.dataset.installmentId
                ?? '';

            const number =
                button.dataset.installmentNumber
                ?? '';

            const date =
                button.dataset.dueDate
                ?? '';

            const original =
                moneyToCents(
                    button.dataset.originalAmount
                    ?? 0
                );

            const remaining =
                moneyToCents(
                    button.dataset.remainingPrincipal
                    ?? 0
                );


            installmentId.value =
                id;

            installmentNumber.value =
                number + 'ª Parcela';

            dueDate.value =
                date;

            originalAmount.value =
                'R$ '
                + formatMoney(
                    original
                );

            remainingPrincipal.value =
                'R$ '
                + formatMoney(
                    remaining
                );


            /*
             * Por padrão sugerimos a quitação
             * integral do saldo existente.
             */
            principalAmount.value =
                formatMoney(
                    remaining
                );


            interestAmount.value =
                '0,00';

            penaltyAmount.value =
                '0,00';

            discountAmount.value =
                '0,00';


            /*
             * Evitar reaproveitar a forma selecionada
             * ao abrir o modal para outra parcela.
             */
            if (financialPaymentMethod) {
                financialPaymentMethod.value = '';
            }


            recalculateTotal();
        }
    );


    /*
     * Atualizar total em tempo real.
     */
    [
        principalAmount,
        interestAmount,
        penaltyAmount,
        discountAmount

    ].forEach(
        input => {

            input.addEventListener(
                'input',
                recalculateTotal
            );
        }
    );

})();