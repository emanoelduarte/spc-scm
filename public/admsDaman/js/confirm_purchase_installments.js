(() => {

    const btnGenerate =
        document.getElementById(
            'btnGenerateConfirmedInstallments'
        );


    /*
     * JS carregado globalmente.
     * Se não estivermos na tela correta,
     * encerrar sem fazer nada.
     */
    if (!btnGenerate) {
        return;
    }


    const paymentMethod =
        document.getElementById(
            'confirm_payment_method_id'
        );

    const container =
        document.getElementById(
            'confirmPaymentScheduleInstallments'
        );

    const form =
        document.getElementById(
            'confirmPaymentScheduleForm'
        );

    const btnConfirm =
        document.getElementById(
            'btnConfirmPaymentSchedule'
        );


    /*
     * ==========================================================
     * GERAR PARCELAS
     * ==========================================================
     */
    btnGenerate.addEventListener(
        'click',
        async function () {

            if (!paymentMethod.value) {

                alert(
                    'Selecione uma condição de pagamento.'
                );

                paymentMethod.focus();

                return;
            }


            const endpoint =
                btnGenerate.dataset.endpoint;

            const purchaseDate =
                btnGenerate.dataset.purchaseDate;

            const totalCents =
                parseMoneyToCents(
                    btnGenerate.dataset.totalValue
                );


            if (!purchaseDate) {

                alert(
                    'Data da compra não encontrada.'
                );

                return;
            }


            if (totalCents <= 0) {

                alert(
                    'Valor do documento inválido.'
                );

                return;
            }


            try {

                btnGenerate.disabled = true;

                btnGenerate.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm me-1"
                        aria-hidden="true">
                    </span>

                    Gerando...
                `;


                const response =
                    await fetch(
                        `${endpoint}/${encodeURIComponent(
                            paymentMethod.value
                        )}`
                    );


                const result =
                    await response.json();


                if (
                    !response.ok
                    ||
                    !result.success
                ) {

                    throw new Error(
                        result.message
                        ?? 'Erro ao gerar parcelas.'
                    );
                }


                const items =
                    Array.isArray(result.items)
                        ? result.items
                        : [];


                if (!items.length) {

                    throw new Error(
                        'Nenhuma parcela configurada '
                        + 'para esta condição de pagamento.'
                    );
                }


                /*
                 * Divisão determinística em centavos.
                 *
                 * Qualquer diferença de arredondamento
                 * fica na última parcela.
                 */
                const quantity =
                    items.length;

                const baseAmount =
                    Math.floor(
                        totalCents / quantity
                    );

                const remainder =
                    totalCents
                    - (
                        baseAmount
                        * quantity
                    );


                container.innerHTML = '';


                const row =
                    document.createElement(
                        'div'
                    );

                row.className =
                    'row g-3';


                items.forEach(
                    (item, index) => {

                        let amountCents =
                            baseAmount;


                        if (
                            index
                            === quantity - 1
                        ) {

                            amountCents +=
                                remainder;
                        }


                        const dueDate =
                            addDaysToDate(
                                purchaseDate,
                                Number(
                                    item.days_after_purchase
                                    ?? 0
                                )
                            );


                        const status =
                            getInstallmentStatus(
                                dueDate
                            );


                        const card =
                            createInstallmentCard({
                                index,
                                installmentNumber:
                                    Number(
                                        item.installment_number
                                        ?? index + 1
                                    ),
                                dueDate:
                                    formatDateInput(
                                        dueDate
                                    ),
                                amountCents,
                                status
                            });


                        row.appendChild(
                            card
                        );
                    }
                );


                container.appendChild(
                    row
                );

                bindConfirmationEvents();

                validateConfirmation();

                container.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });


            } catch (error) {

                console.error(error);

                alert(
                    error.message
                    ?? 'Erro ao gerar parcelas.'
                );

            } finally {

                btnGenerate.disabled =
                    false;

                btnGenerate.innerHTML = `
                    <i class="fa-solid fa-gears me-1"></i>
                    Gerar Parcelas
                `;
            }
        }
    );


    /*
     * ==========================================================
     * CARD DA PARCELA
     * ==========================================================
     */
    function createInstallmentCard({
        index,
        installmentNumber,
        dueDate,
        amountCents,
        status
    }) {

        const column =
            document.createElement(
                'div'
            );

        column.className =
            'col-md-6 col-xl-4';


        column.innerHTML = `
            <div class="card h-100 border-secondary">

                <div class="card-header fw-semibold">

                    <i class="fa-solid fa-calendar-day me-1"></i>

                    ${installmentNumber}ª Parcela

                </div>


                <div class="card-body">

                    <input
                        type="hidden"
                        name="installments[${index}][installment_number]"
                        value="${installmentNumber}"
                    >


                    <div class="mb-3">

                        <label class="form-label text-muted">
                            Vencimento
                        </label>

                        <input
                            type="date"
                            class="form-control confirm-installment-due-date"
                            name="installments[${index}][due_date]"
                            value="${dueDate}"
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label text-muted">
                            Valor
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                R$
                            </span>

                            <input
                                type="text"
                                class="form-control confirm-installment-amount"
                                name="installments[${index}][original_amount]"
                                value="${formatMoneyInput(amountCents)}"
                                inputmode="decimal"
                            >

                        </div>

                    </div>


                    <div>

                        <label class="form-label text-muted">
                            Status
                        </label>

                        <select
                            class="form-select confirm-installment-status"
                            name="installments[${index}][status]">

                            <option
                                value="AV"
                                ${status === 'AV'
                ? 'selected'
                : ''}>

                                AV - A Vencer

                            </option>

                            <option
                                value="AT"
                                ${status === 'AT'
                ? 'selected'
                : ''}>

                                AT - Atenção

                            </option>

                            <option
                                value="ON"
                                ${status === 'ON'
                ? 'selected'
                : ''}>

                                ON - Vencida

                            </option>

                        </select>

                    </div>


                    <!--
                        Baixa automática opcional no momento
                        da confirmação das parcelas.
                    -->
                    <div class="border-top mt-3 pt-3">

                        <div class="form-check">

                            <input
                                type="checkbox"
                                class="form-check-input confirm-installment-pay-on-save"
                                form="confirmPaymentScheduleForm"
                                name="installments[${index}][pay_on_save]"
                                id="confirm_installment_pay_on_save_${index}"
                                value="1"
                            >

                            <label
                                class="form-check-label fw-semibold"
                                for="confirm_installment_pay_on_save_${index}">

                                <i class="fa-solid fa-money-bill-wave me-1"></i>

                                Baixar esta parcela ao confirmar

                            </label>

                        </div>

                        <div class="form-text">
                            Registra o pagamento integral desta parcela
                            junto com a confirmação.
                        </div>

                    </div>

                </div>

            </div>
        `;


        return column;
    }


    /*
     * ==========================================================
     * STATUS
     * ==========================================================
     */
    function getInstallmentStatus(date) {

        const today =
            new Date();

        today.setHours(
            0,
            0,
            0,
            0
        );


        const dueDate =
            new Date(
                date.getFullYear(),
                date.getMonth(),
                date.getDate()
            );


        const differenceDays =
            Math.ceil(
                (
                    dueDate.getTime()
                    - today.getTime()
                )
                /
                (
                    1000
                    * 60
                    * 60
                    * 24
                )
            );


        if (differenceDays < 0) {
            return 'ON';
        }


        if (differenceDays <= 7) {
            return 'AT';
        }


        return 'AV';
    }


    /*
     * ==========================================================
     * DATAS
     * ==========================================================
     */
    function addDaysToDate(
        dateString,
        days
    ) {

        const parts =
            dateString.split('-');


        const date =
            new Date(
                Number(parts[0]),
                Number(parts[1]) - 1,
                Number(parts[2])
            );


        date.setDate(
            date.getDate()
            + Number(days)
        );


        return date;
    }


    function formatDateInput(date) {

        const year =
            date.getFullYear();

        const month =
            String(
                date.getMonth() + 1
            ).padStart(
                2,
                '0'
            );

        const day =
            String(
                date.getDate()
            ).padStart(
                2,
                '0'
            );


        return `${year}-${month}-${day}`;
    }


    /*
     * ==========================================================
     * VALORES
     * ==========================================================
     */
    function parseMoneyToCents(value) {

        if (
            value === null
            ||
            value === undefined
        ) {
            return 0;
        }


        let normalized =
            String(value)
                .trim()
                .replace(
                    /R\$/g,
                    ''
                )
                .replace(
                    /\s/g,
                    ''
                );


        if (!normalized) {
            return 0;
        }


        /*
         * Formato brasileiro:
         *
         * 1.500,00
         * 1500,00
         */
        if (normalized.includes(',')) {

            normalized =
                normalized
                    .replace(
                        /\./g,
                        ''
                    )
                    .replace(
                        ',',
                        '.'
                    );

        } else {

            /*
             * Formato vindo do banco:
             *
             * 1500.00
             *
             * Nesse caso o ponto já é o
             * separador decimal e NÃO deve
             * ser removido.
             */
            normalized =
                normalized.replace(
                    /[^\d.-]/g,
                    ''
                );
        }


        const number =
            Number(normalized);


        if (!Number.isFinite(number)) {
            return 0;
        }


        return Math.round(
            number * 100
        );
    }


    function formatMoneyInput(cents) {

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
    * ==========================================================
    * VALIDAR PARCELAS ANTES DA CONFIRMAÇÃO
    * ==========================================================
    */
    function validateConfirmation() {

        const amountInputs =
            container.querySelectorAll(
                '.confirm-installment-amount'
            );

        const dueDateInputs =
            container.querySelectorAll(
                '.confirm-installment-due-date'
            );


        /*
         * Nenhuma parcela gerada.
         */
        if (amountInputs.length === 0) {

            btnConfirm.disabled = true;

            return false;
        }


        const documentTotal =
            parseMoneyToCents(
                btnGenerate.dataset.totalValue
            );


        let installmentsTotal = 0;


        amountInputs.forEach(input => {

            installmentsTotal +=
                parseMoneyToCents(
                    input.value
                );
        });


        /*
         * Todos os vencimentos precisam estar preenchidos.
         */
        let allDatesValid = true;


        dueDateInputs.forEach(input => {

            if (!input.value) {
                allDatesValid = false;
            }
        });


        const isValid =
            documentTotal > 0
            &&
            installmentsTotal === documentTotal
            &&
            allDatesValid;


        btnConfirm.disabled =
            !isValid;


        return isValid;
    }


    /*
     * ==========================================================
     * EVENTOS DOS CAMPOS GERADOS
     * ==========================================================
     */
    function bindConfirmationEvents() {

        container
            .querySelectorAll(
                '.confirm-installment-amount'
            )
            .forEach(input => {

                input.addEventListener(
                    'input',
                    validateConfirmation
                );

                input.addEventListener(
                    'blur',
                    function () {

                        const cents =
                            parseMoneyToCents(
                                this.value
                            );

                        this.value =
                            formatMoneyInput(
                                cents
                            );

                        validateConfirmation();
                    }
                );
            });


        container
            .querySelectorAll(
                '.confirm-installment-due-date'
            )
            .forEach(input => {

                input.addEventListener(
                    'change',
                    validateConfirmation
                );
            });
    }

     /*
     * Trocar condição invalida parcelas já geradas.
     */
    paymentMethod.addEventListener(
        'change',
        function () {

            container.innerHTML = '';

            btnConfirm.disabled = true;
        }
    );

    /*
     * Confirmar e salvar.
     */
    form.addEventListener(
        'submit',
        function (event) {

            if (!validateConfirmation()) {

                event.preventDefault();

                alert(
                    'Confira os valores e vencimentos das parcelas antes de confirmar.'
                );

                return;
            }


            /*
             * Evitar duplo envio.
             */
            btnConfirm.disabled = true;

            btnGenerate.disabled = true;


            btnConfirm.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-1"
                aria-hidden="true">
            </span>

            Confirmando...
        `;
        }
    );

})();