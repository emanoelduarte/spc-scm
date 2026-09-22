/*
 * ============================================================
 * EDIÇÃO DAS PARCELAS DO LANÇAMENTO FINANCEIRO
 * ============================================================
 *
 * Esta rotina cuida somente da experiência da tela:
 *
 * - adicionar nova parcela;
 * - remover parcela editável;
 * - alternar NORMAL / AP;
 * - recalcular AV / AT / ON na interface;
 * - recalcular soma e diferença;
 * - impedir submit visualmente enquanto o total não fechar.
 *
 * As regras definitivas continuam no backend.
 */
(() => {

    const form =
        document.getElementById(
            'editPurchaseDocumentForm'
        );


    /*
     * Este JS é carregado globalmente.
     * Se não estivermos na tela de edição financeira,
     * não executar nada.
     */
    if (!form) {
        return;
    }


    const container =
        document.getElementById(
            'editInstallmentsContainer'
        );


    const addButton =
        document.getElementById(
            'btnAddEditInstallment'
        );


    const saveButton =
        document.getElementById(
            'btnSaveEditPurchaseDocument'
        );


    const countElement =
        document.getElementById(
            'editInstallmentCount'
        );


    const totalElement =
        document.getElementById(
            'editInstallmentsTotal'
        );


    const differenceElement =
        document.getElementById(
            'editInstallmentsDifference'
        );


    const noInstallmentsWarning =
        document.getElementById(
            'editNoInstallmentsWarning'
        );


    const paymentScheduleStatus =
        form.dataset.paymentScheduleStatus
        ?? 'confirmed';


    const documentTotalCents =
        Number(
            form.dataset.totalCents
            ?? 0
        );


    /*
     * FB não usa este editor de parcelas.
     */
    if (
        paymentScheduleStatus === 'pending'
        ||
        !container
    ) {
        return;
    }


    let newIndex =
        Number(
            container.dataset.nextNewIndex
            ?? 1
        );


    function parseMoneyToCents(value) {

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


        if (
            normalized.includes(',')
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
            Number(normalized);


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


    function formatMoneyInput(cents) {

        return (
            cents / 100
        ).toLocaleString(
            'pt-BR',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
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
                currency: 'BRL',
            }
        );
    }


    function getDynamicStatus(
        nature,
        dueDateValue
    ) {

        if (nature === 'AP') {
            return 'AP';
        }


        if (!dueDateValue) {
            return 'AV';
        }


        const parts =
            dueDateValue.split('-');


        if (parts.length !== 3) {
            return 'AV';
        }


        const dueDate =
            new Date(
                Number(parts[0]),
                Number(parts[1]) - 1,
                Number(parts[2])
            );


        const today =
            new Date();


        today.setHours(
            0,
            0,
            0,
            0
        );


        dueDate.setHours(
            0,
            0,
            0,
            0
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


        if (differenceDays < 0) {
            return 'ON';
        }


        if (differenceDays <= 7) {
            return 'AT';
        }


        return 'AV';
    }


    function statusMeta(status) {

        switch (status) {

            case 'AP':
                return {
                    label: 'Permuta',
                    className:
                        'bg-info text-dark',
                };

            case 'ON':
                return {
                    label: 'Vencida',
                    className:
                        'bg-danger',
                };

            case 'AT':
                return {
                    label: 'Atenção',
                    className:
                        'bg-warning text-dark',
                };

            default:
                return {
                    label: 'A Vencer',
                    className:
                        'bg-primary',
                };
        }
    }


    function updateCardStatus(card) {

        if (!card) {
            return;
        }


        /*
         * Parcelas bloqueadas não recebem
         * atualização visual por JS.
         */
        if (
            card.dataset.locked === '1'
        ) {
            return;
        }


        const natureInput =
            card.querySelector(
                '.edit-installment-nature'
            );


        const dueDateInput =
            card.querySelector(
                '.edit-installment-due-date'
            );


        const badge =
            card.querySelector(
                '.edit-installment-status-badge'
            );


        const statusText =
            card.querySelector(
                '.edit-installment-status-text'
            );


        if (
            !natureInput
            ||
            !dueDateInput
            ||
            !badge
            ||
            !statusText
        ) {
            return;
        }


        const status =
            getDynamicStatus(
                natureInput.value,
                dueDateInput.value
            );


        const meta =
            statusMeta(status);


        badge.className =
            `badge ${meta.className} edit-installment-status-badge`;


        badge.textContent =
            status;


        statusText.value =
            `${status} - ${meta.label}`;
    }


    function updateNatureState(card) {

        const natureInput =
            card.querySelector(
                '.edit-installment-nature'
            );


        const dueDateInput =
            card.querySelector(
                '.edit-installment-due-date'
            );


        if (
            !natureInput
            ||
            !dueDateInput
        ) {
            return;
        }


        if (
            natureInput.value === 'AP'
        ) {

            if (
                dueDateInput.value
            ) {

                dueDateInput.dataset.previousDate =
                    dueDateInput.value;
            }


            dueDateInput.value =
                '';


            /*
             * disabled:
             * o navegador não envia due_date;
             * o backend grava NULL para AP.
             */
            dueDateInput.disabled =
                true;


            updateCardStatus(card);

            return;
        }


        if (dueDateInput.disabled) {

            dueDateInput.disabled =
                false;


            dueDateInput.value =
                dueDateInput.dataset.previousDate
                ?? '';
        }


        updateCardStatus(card);
    }


    function getCards() {

        return Array.from(
            container.querySelectorAll(
                '.edit-installment-card'
            )
        );
    }


    function recalculate() {

        const cards =
            getCards();


        let totalCents =
            0;


        cards.forEach(card => {

            const amountInput =
                card.querySelector(
                    '.edit-installment-amount'
                );


            if (!amountInput) {
                return;
            }


            totalCents +=
                parseMoneyToCents(
                    amountInput.value
                );
        });


        const difference =
            documentTotalCents
            - totalCents;


        if (totalElement) {

            totalElement.textContent =
                formatMoney(
                    totalCents
                );


            totalElement.classList.toggle(
                'text-success',
                difference === 0
            );


            totalElement.classList.toggle(
                'text-danger',
                difference !== 0
            );
        }


        if (differenceElement) {

            differenceElement.classList.remove(
                'text-success',
                'text-danger'
            );


            if (difference === 0) {

                differenceElement.classList.add(
                    'text-success'
                );


                differenceElement.textContent =
                    formatMoney(0);

            } else {

                differenceElement.classList.add(
                    'text-danger'
                );


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
        }


        if (countElement) {

            countElement.textContent =
                `${cards.length} parcela${cards.length !== 1 ? 's' : ''}`;
        }


        if (noInstallmentsWarning) {

            noInstallmentsWarning.classList.toggle(
                'd-none',
                cards.length > 0
            );
        }


        if (saveButton) {

            saveButton.disabled =
                (
                    cards.length === 0
                    ||
                    difference !== 0
                );
        }
    }


    function createNewCard() {

        const key =
            `new_${newIndex}`;


        newIndex++;


        const wrapper =
            document.createElement(
                'div'
            );


        wrapper.className =
            'col-xl-4 col-md-6 edit-installment-card';


        wrapper.dataset.locked =
            '0';


        wrapper.dataset.new =
            '1';


        wrapper.innerHTML = `
            <div class="card h-100 border-success">

                <div class="card-header d-flex align-items-center gap-2">

                    <strong class="edit-installment-title">
                        Nova Parcela
                    </strong>

                    <span class="badge bg-primary edit-installment-status-badge">
                        AV
                    </span>

                    <span class="badge bg-secondary">
                        Número automático
                    </span>

                    <span class="ms-auto">

                        <button
                            type="button"
                            class="btn btn-outline-danger btn-sm btn-remove-edit-installment"
                            title="Remover nova parcela">

                            <i class="fa-solid fa-trash"></i>

                        </button>

                    </span>

                </div>

                <div class="card-body">

                    <input
                        type="hidden"
                        name="installments[${key}][id]"
                        value="0">

                    <div class="mb-3">

                        <label class="form-label">
                            Natureza
                        </label>

                        <select
                            name="installments[${key}][nature]"
                            class="form-select edit-installment-nature">

                            <option value="NORMAL" selected>
                                Parcela normal
                            </option>

                            <option value="AP">
                                Permuta (AP)
                            </option>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Vencimento
                        </label>

                        <input
                            type="date"
                            name="installments[${key}][due_date]"
                            class="form-control edit-installment-due-date">

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Valor Original
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                R$
                            </span>

                            <input
                                type="text"
                                name="installments[${key}][original_amount]"
                                class="form-control fw-bold edit-installment-amount"
                                inputmode="decimal"
                                value="0,00">

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Status atual
                        </label>

                        <input
                            type="text"
                            class="form-control edit-installment-status-text"
                            value="AV - A Vencer"
                            readonly>

                    </div>

                    <div class="alert alert-success mb-0 py-2">

                        <div class="fw-semibold">

                            <i class="fa-solid fa-plus me-1"></i>

                            Nova parcela

                        </div>

                        <div class="small mt-1">

                            O número será atribuído automaticamente ao salvar.

                        </div>

                    </div>

                </div>

            </div>
        `;


        container.appendChild(
            wrapper
        );


        const amountInput =
            wrapper.querySelector(
                '.edit-installment-amount'
            );


        if (amountInput) {
            amountInput.focus();
            amountInput.select();
        }


        recalculate();
    }


    if (addButton) {

        addButton.addEventListener(
            'click',
            createNewCard
        );
    }


    /*
     * Delegação de eventos:
     * também funciona para cartões criados dinamicamente.
     */
    container.addEventListener(
        'click',
        event => {

            const removeButton =
                event.target.closest(
                    '.btn-remove-edit-installment'
                );


            if (!removeButton) {
                return;
            }


            const card =
                removeButton.closest(
                    '.edit-installment-card'
                );


            if (
                !card
                ||
                card.dataset.locked === '1'
            ) {
                return;
            }


            card.remove();


            recalculate();
        }
    );


    container.addEventListener(
        'change',
        event => {

            if (
                event.target.matches(
                    '.edit-installment-nature'
                )
            ) {

                updateNatureState(
                    event.target.closest(
                        '.edit-installment-card'
                    )
                );


                recalculate();

                return;
            }


            if (
                event.target.matches(
                    '.edit-installment-due-date'
                )
            ) {

                updateCardStatus(
                    event.target.closest(
                        '.edit-installment-card'
                    )
                );
            }
        }
    );


    container.addEventListener(
        'input',
        event => {

            if (
                event.target.matches(
                    '.edit-installment-amount'
                )
            ) {

                recalculate();
            }
        }
    );


    container.addEventListener(
        'focusout',
        event => {

            if (
                !event.target.matches(
                    '.edit-installment-amount'
                )
            ) {
                return;
            }


            const cents =
                parseMoneyToCents(
                    event.target.value
                );


            event.target.value =
                formatMoneyInput(
                    cents
                );


            recalculate();
        }
    );


    /*
     * Aplicar estado inicial em APs editáveis
     * e calcular o resumo atual.
     */
    getCards().forEach(card => {

        if (
            card.dataset.locked !== '1'
        ) {

            updateNatureState(card);
        }
    });


    recalculate();

})();
