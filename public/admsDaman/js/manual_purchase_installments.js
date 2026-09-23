(() => {

    const btnGenerate =
        document.getElementById(
            'btnGenerateManualInstallments'
        );

    /*
     * Este JS é carregado globalmente.
     * Se não estamos na tela de lançamento avulso,
     * não executar nada.
     */
    if (!btnGenerate) {
        return;
    }

    const form =
        document.getElementById(
            'manualPurchaseDocumentForm'
        );

    const btnSave =
        document.getElementById(
            'btnSaveManualPurchaseDocument'
        );

    const paymentMethod =
        document.getElementById(
            'adms_daman_payment_method_id'
        );

    const purchaseDate =
        document.getElementById(
            'purchase_date'
        );

    const paymentSchedulePending =
        document.getElementById(
            'payment_schedule_pending'
        );

    const totalValue =
        document.getElementById(
            'total_value'
        );

    const preview =
        document.getElementById(
            'manualInstallmentsPreview'
        );

    const container =
        document.getElementById(
            'manualInstallmentsContainer'
        );

    const oldInstallmentsData =
        document.getElementById(
            'manualInstallmentsOldData'
        );

    const btnSavePending =
        document.getElementById(
            'btnSavePendingPurchaseDocument'
        );


    /*
     * ==========================================================
     * FORMAS DE PAGAMENTO DA BAIXA AUTOMÁTICA
     * ==========================================================
     */
    const financialPaymentMethodsData =
        document.getElementById(
            'financialPaymentMethodsData'
        );

    let financialPaymentMethods = [];

    if (financialPaymentMethodsData) {

        try {

            const parsedPaymentMethods =
                JSON.parse(
                    financialPaymentMethodsData.textContent
                    || '[]'
                );

            financialPaymentMethods =
                Array.isArray(parsedPaymentMethods)
                    ? parsedPaymentMethods
                    : [];

        } catch (error) {

            console.error(
                'Erro ao recuperar formas de pagamento.',
                error
            );

            financialPaymentMethods = [];
        }
    }

    /*
 * ==========================================================
 * PARCELAS RETORNADAS PELO POST
 * ==========================================================
 */
    let oldInstallments = [];

    if (oldInstallmentsData) {

        try {

            oldInstallments =
                JSON.parse(
                    oldInstallmentsData.textContent
                    || '[]'
                );

        } catch (error) {

            console.error(
                'Erro ao recuperar parcelas do formulário.',
                error
            );

            oldInstallments = [];
        }
    }

    /*
     * Converter valores vindos do POST/JSON para booleano.
     *
     * Aceita 1, "1", true e "true".
     */
    function emptyBoolean(value) {
        return (
            value === true
            || value === 1
            || value === '1'
            || value === 'true'
        );
    }


    function renderInstallments(
        installments,
        preservePayOnSave = false
    ) {

        /*
         * Limpar os cards atuais.
         */
        container.innerHTML = '';


        /*
         * Reconstruir cada parcela exatamente
         * com os dados recebidos.
         */
        installments.forEach(
            (installment, index) => {

                const installmentNumber =
                    Number(
                        installment.installment_number
                        ?? index + 1
                    );


                const dueDate =
                    installment.due_date
                    ?? '';


                const amountCents =
                    parseMoneyToCents(
                        installment.original_amount
                        ?? 0
                    );


                const status =
                    installment.status
                    ?? 'AV';


                /*
                 * Preservar a regra original da condição de pagamento.
                 *
                 * O checkbox de baixa estará disponível para qualquer
                 * parcela, independentemente do vencimento. Este campo
                 * continua sendo mantido apenas como informação de apoio
                 * e para futuras regras da condição de pagamento.
                 */
                const daysAfterPurchase =
                    Number(
                        installment.days_after_purchase
                        ?? -1
                    );


                /*
                 * Parcelas recém-geradas começam sempre desmarcadas.
                 * Somente restauramos a marcação quando o formulário
                 * voltou do backend após erro de validação.
                 */
                const payOnSave =
                    preservePayOnSave
                    && !emptyBoolean(
                        installment.pay_on_save
                    );


                const financialPaymentMethodId =
                    Number(
                        installment[
                            'adms_daman_financial_payment_method_id'
                        ]
                        ?? 0
                    );


                const card =
                    createInstallmentCard({
                        index,
                        installmentNumber,
                        dueDate,
                        amountCents,
                        status,
                        payOnSave,
                        daysAfterPurchase,
                        financialPaymentMethodId
                    });


                container.appendChild(
                    card
                );
            }
        );


        /*
         * Mostrar novamente o bloco das parcelas.
         */
        preview.classList.remove(
            'd-none'
        );


        /*
         * Reaplicar os eventos dos campos.
         */
        bindInstallmentEvents();


        /*
         * Atualizar totais e liberar ou bloquear
         * o botão de salvar.
         */
        recalculateSummary();
    }

    /*
    * ==========================================================
    * PARCELAMENTO PENDENTE / FALTA BOLETO
    * ==========================================================
    */
    function updatePaymentSchedulePendingState() {

        if (!paymentSchedulePending) {
            return;
        }


        const isPending =
            paymentSchedulePending.checked;


        /*
         * Fluxo normal:
         * gerar e configurar parcelas.
         */
        btnGenerate.classList.toggle(
            'd-none',
            isPending
        );


        /*
         * Fluxo FB:
         * permitir salvar o lançamento
         * sem gerar parcelas.
         */
        if (btnSavePending) {

            btnSavePending.classList.toggle(
                'd-none',
                !isPending
            );
        }


        /*
         * Se marcou "Falta boleto",
         * qualquer parcelamento provisório
         * deve ser descartado.
         */
        if (isPending) {

            container.innerHTML = '';

            preview.classList.add(
                'd-none'
            );

            if (btnSave) {
                btnSave.disabled = true;
            }
        }
    }


    if (paymentSchedulePending) {

        paymentSchedulePending.addEventListener(
            'change',
            updatePaymentSchedulePendingState
        );


        /*
         * Também executar ao carregar a página,
         * principalmente após retorno de validação.
         */
        updatePaymentSchedulePendingState();
    }


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


            if (!purchaseDate.value) {

                alert(
                    'Informe a data da compra.'
                );

                purchaseDate.focus();

                return;
            }


            const totalCents =
                parseMoneyToCents(
                    totalValue.value
                );


            if (totalCents <= 0) {

                alert(
                    'Informe um valor válido para a compra.'
                );

                totalValue.focus();

                return;
            }


            const endpoint =
                btnGenerate.dataset.endpoint;


            try {

                btnGenerate.disabled =
                    true;


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
                        'Nenhuma parcela configurada para esta condição de pagamento.'
                    );
                }


                /*
                 * Dividir sempre em centavos.
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


                const generatedInstallments = [];


                items.forEach(
                    (item, index) => {

                        let amountCents =
                            baseAmount;


                        /*
                         * Diferença de centavos
                         * fica na última parcela.
                         */
                        if (
                            index === quantity - 1
                        ) {

                            amountCents +=
                                remainder;
                        }


                        const dueDate =
                            addDaysToDate(
                                purchaseDate.value,
                                item.days_after_purchase
                            );


                        generatedInstallments.push({

                            installment_number:
                                Number(
                                    item.installment_number
                                    ?? index + 1
                                ),

                            due_date:
                                formatDateInput(
                                    dueDate
                                ),

                            original_amount:
                                formatMoneyInput(
                                    amountCents
                                ),

                            status:
                                getInstallmentStatus(
                                    dueDate
                                ),

                            /*
                             * Guardar a regra original da condição.
                             *
                             * Isso permite restaurar corretamente
                             * o checkbox após erro de validação.
                             */
                            days_after_purchase:
                                Number(
                                    item.days_after_purchase
                                    ?? -1
                                ),

                            pay_on_save: 0,

                            adms_daman_financial_payment_method_id:
                                0
                        });
                    }
                );


                /*
                 * Utilizar a mesma função tanto para
                 * parcelas novas quanto para parcelas
                 * restauradas após erro do formulário.
                 */
                renderInstallments(
                    generatedInstallments,
                    false
                );


                preview.classList.remove(
                    'd-none'
                );


                bindInstallmentEvents();

                recalculateSummary();


                preview.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
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
            }
        }
    );


    /*
     * ==========================================================
     * CRIAR CARD
     * ==========================================================
     */
    function createInstallmentCard({
        index,
        installmentNumber,
        dueDate,
        amountCents,
        status,
        payOnSave = false,
        daysAfterPurchase = -1,
        financialPaymentMethodId = 0
    }) {

        const card =
            document.createElement(
                'div'
            );

        card.className =
            'col-md-6 col-xl-4';


        card.innerHTML = `
            <div class="card h-100 border-secondary">

                <div class="card-header fw-semibold">

                    <i class="fa-solid fa-calendar-day me-1"></i>

                    ${installmentNumber}ª Parcela

                </div>


                <div class="card-body">

                    <input
                        type="hidden"
                        form="manualPurchaseDocumentForm"
                        name="installments[${index}][installment_number]"
                        value="${installmentNumber}"
                    >

                    <input
                        type="hidden"
                        form="manualPurchaseDocumentForm"
                        name="installments[${index}][days_after_purchase]"
                        value="${daysAfterPurchase}"
                    >


                    <div class="mb-3">

                        <label class="form-label text-muted">
                            Vencimento
                        </label>

                        <input
                            type="date"
                            class="form-control manual-installment-due-date"
                            form="manualPurchaseDocumentForm"
                            name="installments[${index}][due_date]"
                            value="${dueDate}"
                            ${status === 'AP' ? 'disabled' : ''}
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
                                class="form-control manual-installment-amount"
                                form="manualPurchaseDocumentForm"
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
                            class="form-select manual-installment-status"
                            form="manualPurchaseDocumentForm"
                            name="installments[${index}][status]">

                            <option
                                value="AV"
                                ${status === 'AV' ? 'selected' : ''}>
                                AV - A Vencer
                            </option>

                            <option
                                value="AT"
                                ${status === 'AT' ? 'selected' : ''}>
                                AT - Atenção
                            </option>

                            <option
                                value="ON"
                                ${status === 'ON' ? 'selected' : ''}>
                                ON - Em Aberto / Vencido
                            </option>

                            <option
                                value="AP"
                                ${status === 'AP' ? 'selected' : ''}>
                                AP - Permuta
                            </option>

                        </select>

                    </div>

                    <div class="border-top mt-3 pt-3">

                        <div class="form-check">

                            <input
                                type="checkbox"
                                class="form-check-input manual-installment-pay-on-save"
                                form="manualPurchaseDocumentForm"
                                name="installments[${index}][pay_on_save]"
                                id="manual_installment_pay_on_save_${index}"
                                value="1"
                                ${payOnSave ? 'checked' : ''}
                                ${status === 'AP' ? 'disabled' : ''}
                            >

                            <label
                                class="form-check-label fw-semibold"
                                for="manual_installment_pay_on_save_${index}">

                                <i class="fa-solid fa-money-bill-wave me-1"></i>

                                Baixar esta parcela ao salvar

                            </label>

                        </div>

                        <div class="form-text">
                            Registra o pagamento integral desta
                            parcela junto com o lançamento.
                        </div>


                        <div
                            class="mt-3 manual-financial-payment-method-wrapper
                            ${payOnSave && status !== 'AP' ? '' : 'd-none'}">

                            <label
                                class="form-label"
                                for="manual_installment_financial_payment_method_${index}">

                                Forma de pagamento
                                <span class="text-danger">*</span>

                            </label>

                            <select
                                class="form-select manual-installment-financial-payment-method"
                                form="manualPurchaseDocumentForm"
                                name="installments[${index}][adms_daman_financial_payment_method_id]"
                                id="manual_installment_financial_payment_method_${index}"
                                ${payOnSave && status !== 'AP' ? 'required' : 'disabled'}>

                                <option value="">
                                    Selecione
                                </option>

                            </select>

                            <div class="form-text">
                                Informe como esta parcela foi efetivamente paga.
                            </div>

                        </div>

                    </div>

                </div>

            </div>
        `;


        const financialPaymentMethodSelect =
            card.querySelector(
                '.manual-installment-financial-payment-method'
            );


        if (financialPaymentMethodSelect) {

            financialPaymentMethods.forEach(
                method => {

                    const methodId =
                        Number(
                            method.id
                            ?? 0
                        );


                    if (methodId <= 0) {
                        return;
                    }


                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        String(methodId);

                    option.textContent =
                        String(
                            method.name
                            ?? ''
                        );

                    option.selected =
                        methodId
                        === Number(
                            financialPaymentMethodId
                        );


                    financialPaymentMethodSelect
                        .appendChild(
                            option
                        );
                }
            );
        }


        updateAutomaticPaymentMethodState(
            card
        );


        return card;
    }



    /*
     * Mostrar e exigir a forma de pagamento somente
     * quando a baixa automática estiver marcada.
     */
    function updateAutomaticPaymentMethodState(card) {

        if (!card) {
            return;
        }


        const payOnSaveInput =
            card.querySelector(
                '.manual-installment-pay-on-save'
            );

        const statusSelect =
            card.querySelector(
                '.manual-installment-status'
            );

        const wrapper =
            card.querySelector(
                '.manual-financial-payment-method-wrapper'
            );

        const paymentMethodSelect =
            card.querySelector(
                '.manual-installment-financial-payment-method'
            );


        if (
            !payOnSaveInput
            ||
            !wrapper
            ||
            !paymentMethodSelect
        ) {
            return;
        }


        const enabled =
            payOnSaveInput.checked
            &&
            !payOnSaveInput.disabled
            &&
            (
                !statusSelect
                ||
                statusSelect.value !== 'AP'
            );


        wrapper.classList.toggle(
            'd-none',
            !enabled
        );

        paymentMethodSelect.disabled =
            !enabled;

        paymentMethodSelect.required =
            enabled;
    }

    /*
     * ==========================================================
     * EVENTOS DOS CAMPOS
     * ==========================================================
     */
    function bindInstallmentEvents() {
        container
            .querySelectorAll(
                '.manual-installment-amount'
            )
            .forEach(input => {

                input.addEventListener(
                    'input',
                    recalculateSummary
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

                        recalculateSummary();
                    }
                );
            });


        container
            .querySelectorAll(
                '.manual-installment-due-date'
            )
            .forEach(input => {

                input.addEventListener(
                    'change',
                    function () {

                        const card =
                            this.closest(
                                '.card'
                            );

                        const statusSelect =
                            card?.querySelector(
                                '.manual-installment-status'
                            );


                        if (!statusSelect) {
                            return;
                        }


                        /*
                        * AP é manual.
                        *
                        * AV, AT e ON são determinados
                        * pelo vencimento.
                        */
                        if (
                            statusSelect.value === 'AP'
                        ) {
                            return;
                        }


                        const date =
                            parseDateInput(
                                this.value
                            );


                        if (date) {

                            statusSelect.value =
                                getInstallmentStatus(
                                    date
                                );
                        }
                    }
                );
            });


        container
            .querySelectorAll(
                '.manual-installment-status'
            )
            .forEach(select => {

                select.addEventListener(
                    'change',
                    function () {

                        const card =
                            this.closest(
                                '.card'
                            );

                        const dueDateInput =
                            card?.querySelector(
                                '.manual-installment-due-date'
                            );

                        const payOnSaveInput =
                            card?.querySelector(
                                '.manual-installment-pay-on-save'
                            );


                        if (!dueDateInput) {
                            return;
                        }


                        /*
                         * Permuta:
                         * vencimento pode ficar indefinido.
                         */
                        if (
                            this.value === 'AP'
                        ) {

                            if (dueDateInput.value) {

                                dueDateInput.dataset.previousDate =
                                    dueDateInput.value;
                            }


                            dueDateInput.value = '';

                            dueDateInput.disabled = true;


                            /*
                             * Permuta não gera baixa financeira.
                             */
                            if (payOnSaveInput) {
                                payOnSaveInput.checked = false;
                                payOnSaveInput.disabled = true;
                            }


                            updateAutomaticPaymentMethodState(
                                card
                            );

                            return;
                        }


                        /*
                        * Saiu da Permuta.
                        */
                        if (
                            dueDateInput.disabled
                        ) {

                            dueDateInput.disabled = false;

                            dueDateInput.value =
                                dueDateInput.dataset.previousDate
                                ?? '';
                        }


                        if (payOnSaveInput) {
                            payOnSaveInput.disabled = false;
                        }


                        updateAutomaticPaymentMethodState(
                            card
                        );
                    }
                );
            });


        container
            .querySelectorAll(
                '.manual-installment-pay-on-save'
            )
            .forEach(input => {

                if (
                    input.dataset.paymentMethodEventBound
                    === '1'
                ) {
                    return;
                }


                input.dataset.paymentMethodEventBound =
                    '1';


                input.addEventListener(
                    'change',
                    function () {

                        updateAutomaticPaymentMethodState(
                            this.closest(
                                '.card'
                            )
                        );
                    }
                );


                updateAutomaticPaymentMethodState(
                    input.closest(
                        '.card'
                    )
                );
            });
    }


    /*
     * ==========================================================
     * RESUMO
     * ==========================================================
     */
    function recalculateSummary() {
        const inputs =
            container.querySelectorAll(
                '.manual-installment-amount'
            );


        const purchaseTotal =
            parseMoneyToCents(
                totalValue.value
            );


        let installmentsTotal =
            0;


        inputs.forEach(input => {

            installmentsTotal +=
                parseMoneyToCents(
                    input.value
                );
        });


        const difference =
            purchaseTotal
            - installmentsTotal;

        /*
        * Liberar o salvamento somente quando:
        *
        * - existir pelo menos uma parcela;
        * - o valor da compra for válido;
        * - a soma das parcelas for exatamente
        *   igual ao valor total da compra.
        */
        if (btnSave) {

            btnSave.disabled =
                inputs.length === 0
                || purchaseTotal <= 0
                || difference !== 0;
        }


        document.getElementById(
            'manualInstallmentsTotalPurchase'
        ).textContent =
            formatMoney(
                purchaseTotal
            );


        const totalElement =
            document.getElementById(
                'manualInstallmentsTotal'
            );


        totalElement.textContent =
            formatMoney(
                installmentsTotal
            );


        totalElement.classList.toggle(
            'text-success',
            difference === 0
        );

        totalElement.classList.toggle(
            'text-danger',
            difference !== 0
        );


        const differenceElement =
            document.getElementById(
                'manualInstallmentsDifference'
            );


        differenceElement.classList.toggle(
            'text-success',
            difference === 0
        );

        differenceElement.classList.toggle(
            'text-danger',
            difference !== 0
        );


        if (difference === 0) {

            differenceElement.textContent =
                formatMoney(0);

            return;
        }


        differenceElement.textContent =
            (
                difference > 0
                    ? '- '
                    : '+ '
            )
            + formatMoney(
                Math.abs(difference)
            );
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

        const date =
            parseDateInput(
                dateString
            );


        if (!date) {

            throw new Error(
                'Data da compra inválida.'
            );
        }


        date.setDate(
            date.getDate()
            + Number(days)
        );


        return date;
    }


    function parseDateInput(value) {
        if (
            !/^\d{4}-\d{2}-\d{2}$/
                .test(value)
        ) {
            return null;
        }


        const parts =
            value.split('-');


        return new Date(
            Number(parts[0]),
            Number(parts[1]) - 1,
            Number(parts[2])
        );
    }


    function formatDateInput(date) {
        const year =
            date.getFullYear();

        const month =
            String(
                date.getMonth() + 1
            ).padStart(2, '0');

        const day =
            String(
                date.getDate()
            ).padStart(2, '0');


        return `${year}-${month}-${day}`;
    }


    /*
     * ==========================================================
     * VALORES
     * ==========================================================
     */
    function parseMoneyToCents(value) {
        if (!value) {
            return 0;
        }


        let normalized =
            String(value)
                .replace(/R\$/gi, '')
                .replace(/\s/g, '');


        if (normalized.includes(',')) {

            normalized =
                normalized
                    .replace(/\./g, '')
                    .replace(',', '.');
        }


        const numericValue =
            Number(normalized);


        if (Number.isNaN(numericValue)) {
            return 0;
        }


        return Math.round(
            numericValue * 100
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


    function formatMoney(cents) {
        return (
            cents / 100
        ).toLocaleString(
            'pt-BR',
            {
                style: 'currency',
                currency: 'BRL'
            }
        );
    }

    /*
 * ==========================================================
 * INVALIDAR PARCELAS GERADAS
 * ==========================================================
 */
    function invalidateInstallments() {
        if (
            preview.classList.contains(
                'd-none'
            )
        ) {
            return;
        }


        container.innerHTML = '';

        preview.classList.add(
            'd-none'
        );


        if (btnSave) {
            btnSave.disabled = true;
        }
    }


    paymentMethod.addEventListener(
        'change',
        invalidateInstallments
    );


    purchaseDate.addEventListener(
        'change',
        invalidateInstallments
    );

    totalValue.addEventListener(
        'input',
        function () {

            if (
                !preview.classList.contains(
                    'd-none'
                )
            ) {

                recalculateSummary();
            }
        }
    );

    /*
    * ==========================================================
    * SALVAR LANÇAMENTO
    * ==========================================================
    */
    form.addEventListener(
        'submit',
        function (event) {

            const isPaymentSchedulePending =
                paymentSchedulePending
                && paymentSchedulePending.checked;


            /*
             * ==========================================================
             * LANÇAMENTO COM PARCELAS PENDENTES
             * ==========================================================
             *
             * Nesse fluxo não existem parcelas ainda.
             * Portanto não executamos as validações
             * de quantidade e soma das parcelas.
             *
             * O servidor continuará sendo responsável
             * pelas validações definitivas.
             */
            if (isPaymentSchedulePending) {

                if (btnSavePending) {

                    btnSavePending.disabled = true;

                    btnSavePending.innerHTML = `
                        <span
                            class="spinner-border spinner-border-sm me-1"
                            aria-hidden="true">
                        </span>

                        Salvando...
                    `;
                }

                return;
            }

            const payOnSaveInputs =
                container.querySelectorAll(
                    '.manual-installment-pay-on-save:checked'
                );


            for (const payOnSaveInput of payOnSaveInputs) {

                const card =
                    payOnSaveInput.closest(
                        '.card'
                    );

                const financialPaymentMethodSelect =
                    card?.querySelector(
                        '.manual-installment-financial-payment-method'
                    );


                if (
                    !financialPaymentMethodSelect
                    ||
                    !financialPaymentMethodSelect.value
                ) {

                    event.preventDefault();

                    alert(
                        'Selecione a forma de pagamento da parcela marcada para baixa automática.'
                    );


                    financialPaymentMethodSelect
                        ?.focus();

                    return;
                }
            }


            const amountInputs =
                container.querySelectorAll(
                    '.manual-installment-amount'
                );


            /*
             * Não permitir salvar sem parcelas.
             */
            if (amountInputs.length === 0) {

                event.preventDefault();

                alert(
                    'Gere as parcelas antes de salvar o lançamento.'
                );

                return;
            }


            const purchaseTotal =
                parseMoneyToCents(
                    totalValue.value
                );


            let installmentsTotal =
                0;


            amountInputs.forEach(input => {

                installmentsTotal +=
                    parseMoneyToCents(
                        input.value
                    );
            });


            /*
             * Proteção adicional.
             *
             * Mesmo que alguém consiga habilitar o botão
             * manualmente pelo navegador, o JavaScript
             * impede o envio se os valores não fecharem.
             *
             * O Service também fará esta validação
             * novamente no servidor.
             */
            if (
                purchaseTotal !==
                installmentsTotal
            ) {

                event.preventDefault();

                alert(
                    'A soma das parcelas deve ser igual '
                    + 'ao valor total da compra.'
                );

                recalculateSummary();

                return;
            }


            /*
             * Evitar dois cliques e dois POSTs
             * enquanto o servidor processa o lançamento.
             */
            if (btnSave) {

                btnSave.disabled =
                    true;

                btnSave.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-1"
                    aria-hidden="true">
                </span>

                Salvando...
            `;
            }
        }
    );

    /*
    * ==========================================================
    * RESTAURAR PARCELAS APÓS ERRO DO FORMULÁRIO
    * ==========================================================
    *
    * Quando o Rakit encontrar algum erro,
    * a Controller recarrega a View mantendo
    * $this->data['form'].
    *
    * Se existirem parcelas no POST,
    * reconstruímos os cards automaticamente.
    */
    if (
        Array.isArray(oldInstallments)
        &&
        oldInstallments.length > 0
    ) {

        renderInstallments(
            oldInstallments,
            true
        );
    }

})();