// ==========================================================
// PARCELAS DO LANÇAMENTO FINANCEIRO DA NF-e
// ==========================================================
//
// Este arquivo é carregado globalmente pelo layout.
// Por isso, todo o comportamento fica protegido pela existência
// do botão específico desta tela.
//

(() => {

    const btnGenerateInstallments = document.getElementById(
        'btnGenerateInstallments'
    );

    /*
    * ==========================================================
    * PARCELAMENTO PENDENTE / FALTA BOLETO
    * ==========================================================
    */

    const paymentSchedulePending =
        document.getElementById(
            'payment_schedule_pending'
        );

    const btnSavePendingPurchaseDocument =
        document.getElementById(
            'btnSavePendingPurchaseDocument'
        );

    const installmentsPreview =
        document.getElementById(
            'installmentsPreview'
        );

    const installmentsContainer =
        document.getElementById(
            'installmentsContainer'
        );


    function updatePaymentSchedulePendingState() {

        /*
         * Este JS é global.
         * Se a página não possuir os elementos,
         * simplesmente não executar esta parte.
         */
        if (
            !paymentSchedulePending
            ||
            !btnGenerateInstallments
            ||
            !btnSavePendingPurchaseDocument
        ) {
            return;
        }


        const isPending =
            paymentSchedulePending.checked;


        /*
         * FB marcado:
         *
         * - não gerar parcelas;
         * - permitir salvar diretamente.
         */
        btnGenerateInstallments.classList.toggle(
            'd-none',
            isPending
        );

        btnSavePendingPurchaseDocument.classList.toggle(
            'd-none',
            !isPending
        );


        /*
         * Se o usuário já havia gerado parcelas
         * e depois marcou FB, remover as parcelas.
         *
         * Não queremos enviar parcelas provisórias
         * junto com um lançamento pendente.
         */
        if (
            isPending
            &&
            installmentsContainer
        ) {

            installmentsContainer.innerHTML = '';
        }


        if (
            isPending
            &&
            installmentsPreview
        ) {

            installmentsPreview.classList.add(
                'd-none'
            );
        }


        /*
         * O botão normal de salvar pertence à
         * prévia das parcelas.
         *
         * Garantir que permaneça desabilitado
         * enquanto o lançamento estiver como FB.
         */
        const btnSavePurchaseDocument =
            document.getElementById(
                'btnSavePurchaseDocument'
            );


        if (
            isPending
            &&
            btnSavePurchaseDocument
        ) {

            btnSavePurchaseDocument.disabled =
                true;
        }
    }


    if (paymentSchedulePending) {

        paymentSchedulePending.addEventListener(
            'change',
            updatePaymentSchedulePendingState
        );


        /*
         * Aplicar também ao carregar a página.
         *
         * Importante quando o backend devolver
         * o formulário após alguma validação.
         */
        updatePaymentSchedulePendingState();
    }

    const btnSavePurchaseDocument =
        document.getElementById(
            'btnSavePurchaseDocument'
        );

    // Não estamos na tela de lançamento financeiro.
    if (!btnGenerateInstallments) {
        return;
    }


    const form = document.getElementById(
        'purchaseDocumentForm'
    );

    const paymentMethod = document.getElementById(
        'adms_daman_payment_method_id'
    );

    const purchaseDate = document.getElementById(
        'purchase_date'
    );

    const preview = document.getElementById(
        'installmentsPreview'
    );

    const container = document.getElementById(
        'installmentsContainer'
    );

    const totalNfeElement = document.getElementById(
        'installmentsTotalNfe'
    );

    const totalInstallmentsElement = document.getElementById(
        'installmentsTotal'
    );

    const differenceElement = document.getElementById(
        'installmentsDifference'
    );


    /*
     * Se algum elemento essencial estiver ausente,
     * não executar o módulo.
     */
    if (
        !form
        ||
        !paymentMethod
        ||
        !purchaseDate
        ||
        !preview
        ||
        !container
    ) {

        console.error(
            'Não foi possível inicializar o módulo de parcelas.'
        );

        return;
    }


    // ==========================================================
    // GERAR PARCELAS
    // ==========================================================

    btnGenerateInstallments.addEventListener(
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


            const endpoint =
                btnGenerateInstallments.dataset.endpoint;

            const totalCents =
                getNfeTotalCents();


            if (!endpoint) {

                alert(
                    'Endpoint para geração das parcelas não configurado.'
                );

                return;
            }


            if (totalCents <= 0) {

                alert(
                    'Valor da NF-e inválido.'
                );

                return;
            }


            try {

                btnGenerateInstallments.disabled = true;


                const response = await fetch(
                    `${endpoint}/${encodeURIComponent(paymentMethod.value)}`
                );


                const result = await response.json();


                if (!response.ok || !result.success) {

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
                 * Divisão em centavos para evitar
                 * diferenças de ponto flutuante.
                 */
                const installmentsCount =
                    items.length;

                const baseAmount =
                    Math.floor(
                        totalCents / installmentsCount
                    );

                const remainder =
                    totalCents
                    - (
                        baseAmount
                        * installmentsCount
                    );


                container.innerHTML = '';


                items.forEach(
                    (item, index) => {

                        let amountCents =
                            baseAmount;


                        /*
                         * Eventual diferença de centavos
                         * fica na última parcela.
                         */
                        if (
                            index === installmentsCount - 1
                        ) {

                            amountCents += remainder;
                        }


                        const dueDate =
                            addDaysToDate(
                                purchaseDate.value,
                                item.days_after_purchase
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
                                status,

                                /*
                                 * A opção de baixa automática fica
                                 * disponível em qualquer parcela.
                                 * Parcela recém-gerada inicia desmarcada.
                                 */
                                payOnSave: false
                            });


                        container.appendChild(card);
                    }
                );


                preview.classList.remove(
                    'd-none'
                );


                bindInstallmentEvents();

                recalculateInstallmentsSummary();


                /*
                 * Levar suavemente até a prévia.
                 */
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

                btnGenerateInstallments.disabled =
                    false;
            }
        }
    );


    // ==========================================================
    // CRIAR CARD DE PARCELA
    // ==========================================================

    function createInstallmentCard({
        index,
        installmentNumber,
        dueDate,
        amountCents,
        status,
        payOnSave = false
    }) {

        const safeInstallmentNumber =
            Number.isInteger(installmentNumber)
                && installmentNumber > 0
                ? installmentNumber
                : index + 1;


        const safeDueDate =
            normalizeDateInputValue(
                dueDate
            );


        const safeStatus =
            normalizeStatus(
                status
            );


        const displayedDueDate =
            safeStatus === 'AP'
                ? ''
                : safeDueDate;


        const dateAttributes =
            safeStatus === 'AP'
                ? 'disabled'
                : '';


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

                    ${safeInstallmentNumber}ª Parcela

                </div>


                <div class="card-body">

                    <input
                        type="hidden"
                        form="purchaseDocumentForm"
                        name="installments[${index}][installment_number]"
                        value="${safeInstallmentNumber}"
                    >


                    <!-- Vencimento -->
                    <div class="mb-3">

                        <label class="form-label text-muted">
                            Vencimento
                        </label>

                        <input
                            type="date"
                            class="form-control installment-due-date"
                            form="purchaseDocumentForm"
                            name="installments[${index}][due_date]"
                            value="${displayedDueDate}"
                            ${dateAttributes}
                        >

                    </div>


                    <!-- Valor -->
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
                                class="form-control installment-amount"
                                form="purchaseDocumentForm"
                                name="installments[${index}][original_amount]"
                                value="${formatMoneyInput(amountCents)}"
                                data-cents="${amountCents}"
                                inputmode="decimal"
                                autocomplete="off"
                            >

                        </div>

                    </div>


                    <!-- Status -->
                    <div>

                        <label class="form-label text-muted">
                            Status
                        </label>

                        <select
                            class="form-select installment-status"
                            form="purchaseDocumentForm"
                            name="installments[${index}][status]"
                            data-index="${index}">

                            <option
                                value="AV"
                                ${safeStatus === 'AV' ? 'selected' : ''}>
                                AV - A Vencer
                            </option>

                            <option
                                value="AT"
                                ${safeStatus === 'AT' ? 'selected' : ''}>
                                AT - Atenção
                            </option>

                            <option
                                value="ON"
                                ${safeStatus === 'ON' ? 'selected' : ''}>
                                ON - Em Aberto / Vencido
                            </option>

                            <option
                                value="AP"
                                ${safeStatus === 'AP' ? 'selected' : ''}>
                                AP - Permuta
                            </option>

                        </select>

                    </div>


                    <!-- Baixa automática ao salvar -->
                    <div class="border-top mt-3 pt-3">

                        <div class="form-check">

                            <input
                                type="checkbox"
                                class="form-check-input installment-pay-on-save"
                                form="purchaseDocumentForm"
                                name="installments[${index}][pay_on_save]"
                                id="installment_pay_on_save_${index}"
                                value="1"
                                ${
                                    payOnSave
                                    && safeStatus !== 'AP'
                                        ? 'checked'
                                        : ''
                                }
                                ${safeStatus === 'AP' ? 'disabled' : ''}
                            >

                            <label
                                class="form-check-label fw-semibold"
                                for="installment_pay_on_save_${index}">

                                <i class="fa-solid fa-money-bill-wave me-1"></i>

                                Baixar esta parcela ao salvar

                            </label>

                        </div>

                        <div class="form-text">
                            Registra o pagamento integral desta parcela
                            junto com o lançamento.
                        </div>

                    </div>

                </div>

            </div>
        `;


        /*
        * Se a parcela voltou como AP após erro,
        * o vencimento permanece desabilitado.
        *
        * A obrigatoriedade é validada pelo Rakit.
        */
        if (safeStatus === 'AP') {

            const dueDateInput =
                card.querySelector(
                    '.installment-due-date'
                );

            if (dueDateInput) {

                dueDateInput.disabled =
                    true;
            }
        }


        return card;
    }


    // ==========================================================
    // VINCULAR EVENTOS DOS CAMPOS
    // ==========================================================

    function bindInstallmentEvents() {

        /*
         * ======================================================
         * VALORES
         * ======================================================
         */
        container
            .querySelectorAll(
                '.installment-amount'
            )
            .forEach(input => {

                /*
                 * Evitar listeners duplicados caso esta função
                 * seja chamada mais de uma vez.
                 */
                if (
                    input.dataset.eventsBound
                    === '1'
                ) {
                    return;
                }


                input.dataset.eventsBound =
                    '1';


                input.addEventListener(
                    'input',
                    recalculateInstallmentsSummary
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


                        this.dataset.cents =
                            String(cents);


                        recalculateInstallmentsSummary();
                    }
                );
            });


        /*
         * ======================================================
         * VENCIMENTOS
         * ======================================================
         */
        container
            .querySelectorAll(
                '.installment-due-date'
            )
            .forEach(input => {

                if (
                    input.dataset.eventsBound
                    === '1'
                ) {
                    return;
                }


                input.dataset.eventsBound =
                    '1';


                input.addEventListener(
                    'change',
                    function () {

                        const card =
                            this.closest(
                                '.card'
                            );


                        if (!card) {
                            return;
                        }


                        const statusSelect =
                            card.querySelector(
                                '.installment-status'
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


                        if (!this.value) {
                            return;
                        }


                        const date =
                            parseDateInput(
                                this.value
                            );


                        if (!date) {
                            return;
                        }


                        /*
                         * ON = vencida
                         * AT = hoje até 7 dias
                         * AV = mais de 7 dias
                         */
                        statusSelect.value =
                            getInstallmentStatus(
                                date
                            );
                    }
                );
            });


        /*
         * ======================================================
         * STATUS
         * ======================================================
         */
        container
            .querySelectorAll(
                '.installment-status'
            )
            .forEach(select => {

                if (
                    select.dataset.eventsBound
                    === '1'
                ) {
                    return;
                }


                select.dataset.eventsBound =
                    '1';


                select.addEventListener(
                    'change',
                    function () {

                        const card =
                            this.closest(
                                '.card'
                            );


                        if (!card) {
                            return;
                        }


                        const dueDateInput =
                            card.querySelector(
                                '.installment-due-date'
                            );


                        const payOnSaveInput =
                            card.querySelector(
                                '.installment-pay-on-save'
                            );


                        if (!dueDateInput) {
                            return;
                        }


                        /*
                        * AP = Permuta.
                        *
                        * Permite vencimento indefinido.
                        */
                        if (
                            this.value === 'AP'
                        ) {

                            /*
                             * Guardar a data para restaurar
                             * caso o usuário mude de ideia
                             * antes de enviar o formulário.
                             */
                            if (
                                dueDateInput.value
                            ) {

                                dueDateInput.dataset.previousDate =
                                    dueDateInput.value;
                            }


                            dueDateInput.value =
                                '';

                            dueDateInput.disabled =
                                true;


                            /*
                             * Permuta não representa pagamento.
                             * Portanto não pode ser baixada ao salvar.
                             */
                            if (payOnSaveInput) {

                                payOnSaveInput.checked =
                                    false;

                                payOnSaveInput.disabled =
                                    true;
                            }


                            return;
                        }


                        /*
                         * Saiu de AP.
                         */
                        if (
                            dueDateInput.disabled
                        ) {

                            dueDateInput.disabled =
                                false;

                            dueDateInput.value =
                                dueDateInput.dataset.previousDate
                                ?? '';
                        }


                        /*
                         * Fora de AP, a baixa automática volta
                         * a ficar disponível para a parcela.
                         */
                        if (payOnSaveInput) {

                            payOnSaveInput.disabled =
                                false;
                        }


                        /*
                        * AV, AT e ON são determinados
                        * pelo vencimento.
                        */
                        if (
                            dueDateInput.value
                        ) {

                            const date =
                                parseDateInput(
                                    dueDateInput.value
                                );


                            if (date) {

                                this.value =
                                    getInstallmentStatus(
                                        date
                                    );
                            }
                        }
                    }
                );
            });
    }


    // ==========================================================
    // RESTAURAR PARCELAS APÓS ERRO DO FORMULÁRIO
    // ==========================================================

    function restoreInstallments() {

        const oldDataElement =
            document.getElementById(
                'purchaseInstallmentsOldData'
            );


        if (!oldDataElement) {
            return;
        }


        let installments;


        try {

            installments =
                JSON.parse(
                    oldDataElement.textContent
                );


        } catch (error) {

            console.error(
                'Erro ao restaurar parcelas:',
                error
            );

            return;
        }


        /*
         * Normalmente o PHP envia um array.
         * Esta conversão também tolera um objeto
         * com índices numéricos.
         */
        if (
            !Array.isArray(
                installments
            )
        ) {

            if (
                installments
                &&
                typeof installments === 'object'
            ) {

                installments =
                    Object.values(
                        installments
                    );

            } else {

                return;
            }
        }


        if (!installments.length) {
            return;
        }


        container.innerHTML = '';


        installments.forEach(
            (installment, index) => {

                const installmentNumber =
                    Number(
                        installment.installment_number
                        ?? index + 1
                    );


                const amountCents =
                    parseMoneyToCents(
                        installment.original_amount
                        ?? '0'
                    );


                const status =
                    normalizeStatus(
                        installment.status
                        ?? 'AV'
                    );


                const dueDate =
                    normalizeDateInputValue(
                        installment.due_date
                        ?? ''
                    );


                const card =
                    createInstallmentCard({
                        index,
                        installmentNumber,
                        dueDate,
                        amountCents,
                        status,
                        payOnSave:
                            normalizeBooleanFlag(
                                installment.pay_on_save
                            )
                    });


                container.appendChild(
                    card
                );
            }
        );


        preview.classList.remove(
            'd-none'
        );


        bindInstallmentEvents();

        recalculateInstallmentsSummary();
    }


    // ==========================================================
    // RECALCULAR RESUMO DAS PARCELAS
    // ==========================================================

    function recalculateInstallmentsSummary() {

        const amountInputs =
            container.querySelectorAll(
                '.installment-amount'
            );


        const totalNfe =
            getNfeTotalCents();


        let installmentsTotal =
            0;


        amountInputs.forEach(
            input => {

                installmentsTotal +=
                    parseMoneyToCents(
                        input.value
                    );
            }
        );


        const difference =
            totalNfe
            - installmentsTotal;

        if (btnSavePurchaseDocument) {

            btnSavePurchaseDocument.disabled =
                (
                    amountInputs.length === 0
                    ||
                    difference !== 0
                );
        }


        if (totalNfeElement) {

            totalNfeElement.textContent =
                formatMoney(
                    totalNfe
                );
        }


        if (totalInstallmentsElement) {

            totalInstallmentsElement.textContent =
                formatMoney(
                    installmentsTotal
                );


            totalInstallmentsElement.classList.toggle(
                'text-success',
                difference === 0
            );


            totalInstallmentsElement.classList.toggle(
                'text-danger',
                difference !== 0
            );
        }


        if (!differenceElement) {
            return;
        }


        differenceElement.classList.remove(
            'text-success',
            'text-danger',
            'text-warning'
        );


        if (difference === 0) {

            differenceElement.classList.add(
                'text-success'
            );


            differenceElement.textContent =
                formatMoney(
                    0
                );


            return;
        }


        differenceElement.classList.add(
            'text-danger'
        );


        /*
         * Se faltar valor:
         * - R$ 10,00
         *
         * Se ultrapassar:
         * + R$ 10,00
         */
        differenceElement.textContent =
            (
                difference > 0
                    ? '- '
                    : '+ '
            )
            + formatMoney(
                Math.abs(
                    difference
                )
            );
    }


    // ==========================================================
    // OBTER VALOR TOTAL DA NF-e EM CENTAVOS
    // ==========================================================

    function getNfeTotalCents() {

        const rawValue =
            btnGenerateInstallments
                .dataset
                .totalValue
            ?? '0';


        /*
         * O banco normalmente fornece 76.00.
         * Também toleramos 76,00.
         */
        const numericValue =
            Number(
                String(rawValue)
                    .replace(',', '.')
            );


        if (
            Number.isNaN(
                numericValue
            )
        ) {
            return 0;
        }


        return Math.round(
            numericValue * 100
        );
    }


    // ==========================================================
    // STATUS AUTOMÁTICO DA PARCELA
    // ==========================================================

    function getInstallmentStatus(
        date
    ) {

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


        const differenceMs =
            dueDate.getTime()
            - today.getTime();


        const differenceDays =
            Math.ceil(
                differenceMs
                / (
                    1000
                    * 60
                    * 60
                    * 24
                )
            );


        // Já venceu.
        if (
            differenceDays < 0
        ) {
            return 'ON';
        }


        // Vence hoje ou nos próximos 7 dias.
        if (
            differenceDays <= 7
        ) {
            return 'AT';
        }


        // Mais de 7 dias.
        return 'AV';
    }


    // ==========================================================
    // SOMAR DIAS A UMA DATA
    // ==========================================================

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


    // ==========================================================
    // CONVERTER YYYY-MM-DD EM DATE LOCAL
    // ==========================================================

    function parseDateInput(
        value
    ) {

        const safeValue =
            normalizeDateInputValue(
                value
            );


        if (!safeValue) {
            return null;
        }


        const parts =
            safeValue.split('-');


        const date =
            new Date(
                Number(parts[0]),
                Number(parts[1]) - 1,
                Number(parts[2])
            );


        /*
         * Evitar datas inválidas como 2026-02-31.
         */
        if (
            date.getFullYear()
            !== Number(parts[0])
            ||
            date.getMonth()
            !== Number(parts[1]) - 1
            ||
            date.getDate()
            !== Number(parts[2])
        ) {
            return null;
        }


        return date;
    }


    // ==========================================================
    // NORMALIZAR DATA PARA INPUT TYPE="DATE"
    // ==========================================================

    function normalizeDateInputValue(
        value
    ) {

        const stringValue =
            String(
                value
                ?? ''
            ).trim();


        if (
            !/^\d{4}-\d{2}-\d{2}$/
                .test(
                    stringValue
                )
        ) {
            return '';
        }


        return stringValue;
    }


    // ==========================================================
    // FORMATAR DATE PARA YYYY-MM-DD
    // ==========================================================

    function formatDateInput(
        date
    ) {

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


    // ==========================================================
    // NORMALIZAR FLAG BOOLEANA DO POST
    // ==========================================================

    function normalizeBooleanFlag(
        value
    ) {

        return (
            value === true
            || value === 1
            || value === '1'
            || value === 'true'
            || value === 'on'
        );
    }


    // ==========================================================
    // NORMALIZAR STATUS
    // ==========================================================

    function normalizeStatus(
        status
    ) {

        const allowedStatuses = [
            'AV',
            'AT',
            'ON',
            'AP'
        ];


        const normalized =
            String(
                status
                ?? ''
            )
                .trim()
                .toUpperCase();


        return allowedStatuses.includes(
            normalized
        )
            ? normalized
            : 'AV';
    }


    // ==========================================================
    // CONVERTER VALOR BRASILEIRO PARA CENTAVOS
    // ==========================================================

    function parseMoneyToCents(
        value
    ) {

        if (
            value === null
            ||
            value === undefined
            ||
            value === ''
        ) {
            return 0;
        }


        let normalized =
            String(value)
                .trim()
                .replace(
                    /R\$/gi,
                    ''
                )
                .replace(
                    /\s/g,
                    ''
                );


        /*
         * Formato brasileiro:
         * 1.234,56
         */
        if (
            normalized.includes(
                ','
            )
        ) {

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
             * Se vier 76.00, tratar o ponto
             * como separador decimal.
             *
             * Se vier 1.234 sem casas decimais,
             * tratamos como milhar.
             */
            const decimalDot =
                /^\-?\d+\.\d{1,2}$/;


            if (
                !decimalDot.test(
                    normalized
                )
            ) {

                normalized =
                    normalized.replace(
                        /\./g,
                        ''
                    );
            }
        }


        const numericValue =
            Number(
                normalized
            );


        if (
            Number.isNaN(
                numericValue
            )
        ) {
            return 0;
        }


        return Math.round(
            numericValue * 100
        );
    }


    // ==========================================================
    // FORMATAR CENTAVOS PARA CAMPO DE VALOR
    // ==========================================================

    function formatMoneyInput(
        cents
    ) {

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


    // ==========================================================
    // FORMATAR CENTAVOS PARA REAL
    // ==========================================================

    function formatMoney(
        cents
    ) {

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
             * =====================================================
             * LANÇAMENTO COM PARCELAS PENDENTES
             * =====================================================
             *
             * Nesse fluxo ainda não existem parcelas definitivas.
             * Portanto não executamos as validações
             * de quantidade e soma das parcelas.
             *
             * O servidor continuará responsável
             * pelas validações definitivas.
             */
            if (isPaymentSchedulePending) {

                if (btnSavePendingPurchaseDocument) {

                    btnSavePendingPurchaseDocument.disabled =
                        true;

                    btnSavePendingPurchaseDocument.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm me-1"
                        aria-hidden="true">
                    </span>

                    Salvando...
                `;
                }


                return;
            }


            /*
             * =====================================================
             * LANÇAMENTO COM PARCELAS CONFIRMADAS
             * =====================================================
             */
            const amountInputs =
                container.querySelectorAll(
                    '.installment-amount'
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


            let totalInstallments = 0;


            amountInputs.forEach(input => {

                totalInstallments +=
                    parseMoneyToCents(
                        input.value
                    );
            });


            const totalNfe =
                getNfeTotalCents();


            /*
             * Garantir que a soma das parcelas
             * seja exatamente igual ao valor da NF-e.
             */
            if (
                totalInstallments !==
                totalNfe
            ) {

                event.preventDefault();

                alert(
                    'A soma das parcelas precisa ser igual ao valor da NF-e.'
                );

                return;
            }


            /*
             * Evitar dois cliques e dois POSTs.
             */
            if (btnSavePurchaseDocument) {

                btnSavePurchaseDocument.disabled =
                    true;

                btnSavePurchaseDocument.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-1"
                    aria-hidden="true">
                </span>

                Salvando...
            `;
            }
        }
    );

    // ==========================================================
    // RESTAURAR DADOS ANTERIORES, SE HOUVER
    // ==========================================================

    restoreInstallments();

})();
