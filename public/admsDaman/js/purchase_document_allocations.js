(() => {

    /*
     * ==========================================================
     * ELEMENTOS
     * ==========================================================
     */

    const hasProration =
        document.getElementById(
            'has_proration'
        );


    /*
     * Este JS pode ser carregado globalmente.
     * Se não estivermos na tela correta, não executa.
     */
    if (!hasProration) {
        return;
    }


    const mainProject =
        document.getElementById(
            'adms_daman_project_id'
        );


    const totalValue =
        document.getElementById(
            'total_value'
        );


    const section =
        document.getElementById(
            'purchaseAllocationSection'
        );


    const rowsContainer =
        document.getElementById(
            'purchaseAllocationRows'
        );


    const btnAdd =
        document.getElementById(
            'btnAddPurchaseAllocation'
        );


    const documentTotalElement =
        document.getElementById(
            'purchaseAllocationDocumentTotal'
        );


    const allocationTotalElement =
        document.getElementById(
            'purchaseAllocationTotal'
        );


    const differenceElement =
        document.getElementById(
            'purchaseAllocationDifference'
        );


    const oldDataElement =
        document.getElementById(
            'purchaseAllocationsOldData'
        );


    /*
     * ==========================================================
     * DADOS ANTIGOS DO POST
     * ==========================================================
     */

    let oldAllocations = [];


    if (oldDataElement) {

        try {

            oldAllocations =
                JSON.parse(
                    oldDataElement.textContent
                    || '[]'
                );

        } catch (error) {

            console.error(
                'Erro ao recuperar rateios do formulário.',
                error
            );

            oldAllocations = [];
        }
    }


    /*
     * ==========================================================
     * VALORES MONETÁRIOS
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


        /*
         * Formato brasileiro:
         *
         * 1.234,56
         *      ↓
         * 1234.56
         */
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
     * OPÇÕES DE OBRAS
     * ==========================================================
     *
     * Utilizamos o próprio select principal como fonte.
     * Assim não precisamos duplicar a lista de obras
     * em outro JSON.
     */

    function buildProjectOptions(
        selectedId = ''
    ) {

        let html =
            '<option value="">Selecione</option>';


        Array.from(
            mainProject.options
        ).forEach(option => {

            if (!option.value) {
                return;
            }


            const selected =
                String(option.value)
                === String(selectedId)
                    ? 'selected'
                    : '';


            html += `
                <option
                    value="${option.value}"
                    ${selected}>

                    ${escapeHtml(option.textContent.trim())}

                </option>
            `;
        });


        return html;
    }


    function escapeHtml(value) {

        const div =
            document.createElement(
                'div'
            );

        div.textContent =
            value ?? '';

        return div.innerHTML;
    }


    /*
     * ==========================================================
     * CRIAR LINHA DE RATEIO
     * ==========================================================
     */

    function createAllocationRow(
        allocation = {},
        isFirst = false
    ) {

        const row =
            document.createElement(
                'div'
            );


        row.className =
            'row g-2 align-items-end purchase-allocation-row';


        const projectId =
            isFirst
                ? mainProject.value
                : (
                    allocation.adms_daman_project_id
                    ?? ''
                );


        const amount =
            allocation.allocated_amount
            ?? '';


        /*
         * A primeira obra é sempre a obra principal
         * selecionada no lançamento.
         *
         * O select fica apenas visualmente desabilitado
         * e enviamos o ID através de input hidden.
         */
        const projectField =
            isFirst
                ? `
                    <select
                        class="form-select allocation-project-display"
                        disabled>

                        ${buildProjectOptions(projectId)}

                    </select>

                    <input
                        type="hidden"
                        class="allocation-project"
                        value="${escapeHtml(projectId)}">
                `
                : `
                    <select
                        class="form-select allocation-project">

                        ${buildProjectOptions(projectId)}

                    </select>
                `;


        row.innerHTML = `

            <div class="col-md-5">

                <label class="form-label small text-muted mb-1">
                    ${isFirst
                        ? 'Obra principal'
                        : 'Obra'}
                </label>

                ${projectField}

            </div>


            <div class="col-md-4">

                <label class="form-label small text-muted mb-1">
                    Valor
                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        R$
                    </span>

                    <input
                        type="text"
                        class="form-control allocation-amount"
                        value="${escapeHtml(amount)}"
                        placeholder="0,00"
                        inputmode="decimal">

                </div>

            </div>


            <div class="col-md-2">

                <label class="form-label small text-muted mb-1">
                    Percentual
                </label>

                <div
                    class="form-control bg-body-secondary
                           allocation-percentage">

                    0,00%

                </div>

            </div>


            <div class="col-md-1">

                ${
                    isFirst
                        ? ''
                        : `
                            <button
                                type="button"
                                class="btn btn-outline-danger
                                       w-100 btn-remove-allocation"
                                title="Remover obra">

                                <i class="fa-solid fa-trash"></i>

                            </button>
                        `
                }

            </div>
        `;


        rowsContainer.appendChild(
            row
        );


        bindRowEvents(
            row
        );


        reindexRows();
        updateRemoveButtons();
        updateDuplicatedProjects();
        recalculate();
    }


    /*
     * ==========================================================
     * EVENTOS DAS LINHAS
     * ==========================================================
     */

    function bindRowEvents(row) {

        const amountInput =
            row.querySelector(
                '.allocation-amount'
            );


        const projectSelect =
            row.querySelector(
                'select.allocation-project'
            );


        const removeButton =
            row.querySelector(
                '.btn-remove-allocation'
            );


        if (amountInput) {

            amountInput.addEventListener(
                'input',
                recalculate
            );


            amountInput.addEventListener(
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


                    recalculate();
                }
            );
        }


        if (projectSelect) {

            projectSelect.addEventListener(
                'change',
                function () {

                    updateDuplicatedProjects();
                }
            );
        }


        if (removeButton) {

            removeButton.addEventListener(
                'click',
                function () {

                    /*
                     * Rateio exige no mínimo duas obras.
                     */
                    if (
                        rowsContainer.querySelectorAll(
                            '.purchase-allocation-row'
                        ).length <= 2
                    ) {
                        return;
                    }


                    row.remove();

                    reindexRows();
                    updateRemoveButtons();
                    updateDuplicatedProjects();
                    recalculate();
                }
            );
        }
    }


    /*
     * ==========================================================
     * REINDEXAR CAMPOS
     * ==========================================================
     *
     * Garante POST no formato:
     *
     * allocations[0][adms_daman_project_id]
     * allocations[0][allocated_amount]
     */

    function reindexRows() {

        const rows =
            rowsContainer.querySelectorAll(
                '.purchase-allocation-row'
            );


        rows.forEach(
            (row, index) => {

                const project =
                    row.querySelector(
                        '.allocation-project'
                    );


                const amount =
                    row.querySelector(
                        '.allocation-amount'
                    );


                if (project) {

                    project.name =
                        `allocations[${index}]`
                        + `[adms_daman_project_id]`;
                }


                if (amount) {

                    amount.name =
                        `allocations[${index}]`
                        + `[allocated_amount]`;
                }
            }
        );
    }


    /*
     * ==========================================================
     * NÃO PERMITIR OBRA REPETIDA VISUALMENTE
     * ==========================================================
     */

    function updateDuplicatedProjects() {

        const fields =
            Array.from(
                rowsContainer.querySelectorAll(
                    '.allocation-project'
                )
            );


        const selectedValues =
            fields
                .map(field => field.value)
                .filter(Boolean);


        rowsContainer
            .querySelectorAll(
                'select.allocation-project'
            )
            .forEach(select => {

                Array.from(
                    select.options
                ).forEach(option => {

                    if (!option.value) {
                        return;
                    }


                    option.disabled =
                        selectedValues.includes(
                            option.value
                        )
                        &&
                        option.value
                            !== select.value;
                });
            });
    }


    /*
     * ==========================================================
     * CÁLCULOS
     * ==========================================================
     */

    function recalculate() {

        const documentTotal =
            parseMoneyToCents(
                totalValue.value
            );


        let allocationTotal =
            0;


        rowsContainer
            .querySelectorAll(
                '.purchase-allocation-row'
            )
            .forEach(row => {

                const amountInput =
                    row.querySelector(
                        '.allocation-amount'
                    );


                const percentageElement =
                    row.querySelector(
                        '.allocation-percentage'
                    );


                const amount =
                    parseMoneyToCents(
                        amountInput?.value
                    );


                allocationTotal +=
                    amount;


                let percentage =
                    0;


                if (documentTotal > 0) {

                    percentage =
                        (
                            amount
                            / documentTotal
                        )
                        * 100;
                }


                if (percentageElement) {

                    percentageElement.textContent =
                        percentage
                            .toLocaleString(
                                'pt-BR',
                                {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }
                            )
                        + '%';
                }
            });


        const difference =
            documentTotal
            - allocationTotal;


        documentTotalElement.textContent =
            formatMoney(
                documentTotal
            );


        allocationTotalElement.textContent =
            formatMoney(
                allocationTotal
            );


        differenceElement.textContent =
            formatMoney(
                difference
            );


        /*
         * Apenas feedback visual.
         *
         * A validação definitiva continua sendo feita
         * pelo PurchaseDocumentAllocationService.
         */
        if (
            documentTotal > 0
            &&
            difference === 0
        ) {

            allocationTotalElement
                .classList
                .remove(
                    'text-danger'
                );

            allocationTotalElement
                .classList
                .add(
                    'text-success'
                );


            differenceElement
                .classList
                .remove(
                    'text-danger'
                );

            differenceElement
                .classList
                .add(
                    'text-success'
                );

        } else {

            allocationTotalElement
                .classList
                .remove(
                    'text-success'
                );

            allocationTotalElement
                .classList
                .add(
                    'text-danger'
                );


            differenceElement
                .classList
                .remove(
                    'text-success'
                );

            differenceElement
                .classList
                .add(
                    'text-danger'
                );
        }
    }


    /*
     * ==========================================================
     * BOTÕES DE REMOÇÃO
     * ==========================================================
     */

    function updateRemoveButtons() {

        const rows =
            rowsContainer.querySelectorAll(
                '.purchase-allocation-row'
            );


        rows.forEach(row => {

            const button =
                row.querySelector(
                    '.btn-remove-allocation'
                );


            if (!button) {
                return;
            }


            button.disabled =
                rows.length <= 2;
        });
    }


    /*
     * ==========================================================
     * SINCRONIZAR OBRA PRINCIPAL
     * ==========================================================
     */

    function syncMainProject() {

        const firstRow =
            rowsContainer.querySelector(
                '.purchase-allocation-row'
            );


        if (!firstRow) {
            return;
        }


        const display =
            firstRow.querySelector(
                '.allocation-project-display'
            );


        const hidden =
            firstRow.querySelector(
                '.allocation-project'
            );


        if (display) {

            display.value =
                mainProject.value;
        }


        if (hidden) {

            hidden.value =
                mainProject.value;
        }


        updateDuplicatedProjects();
    }


    /*
     * ==========================================================
     * ABRIR RATEIO
     * ==========================================================
     */

    function openProration() {

        section.classList.remove(
            'd-none'
        );


        /*
         * Se já existem linhas restauradas,
         * não criamos novamente.
         */
        if (
            rowsContainer.querySelector(
                '.purchase-allocation-row'
            )
        ) {

            syncMainProject();
            recalculate();

            return;
        }


        /*
         * Restaurar POST anterior.
         */
        if (
            Array.isArray(oldAllocations)
            &&
            oldAllocations.length > 0
        ) {

            oldAllocations.forEach(
                (allocation, index) => {

                    createAllocationRow(
                        allocation,
                        index === 0
                    );
                }
            );


            /*
             * Rateio precisa ter pelo menos duas linhas.
             */
            while (
                rowsContainer.querySelectorAll(
                    '.purchase-allocation-row'
                ).length < 2
            ) {

                createAllocationRow();
            }


            syncMainProject();

        } else {

            /*
             * Primeira obra:
             * sempre corresponde à obra principal.
             */
            createAllocationRow(
                {
                    adms_daman_project_id:
                        mainProject.value,

                    allocated_amount: ''
                },
                true
            );


            /*
             * Segunda obra:
             * necessária para caracterizar rateio.
             */
            createAllocationRow(
                {
                    adms_daman_project_id: '',
                    allocated_amount: ''
                }
            );
        }


        recalculate();
    }


    /*
     * ==========================================================
     * CHECKBOX
     * ==========================================================
     */

    hasProration.addEventListener(
        'change',
        function () {

            if (this.checked) {

                openProration();

            } else {

                /*
                 * Não apagamos os campos.
                 *
                 * Se o usuário marcar novamente,
                 * o preenchimento continua lá.
                 *
                 * No servidor, allocations será ignorado
                 * quando has_proration não estiver marcado.
                 */
                section.classList.add(
                    'd-none'
                );
            }
        }
    );


    /*
     * Alterar a obra principal também altera
     * a primeira obra do rateio.
     */
    mainProject.addEventListener(
        'change',
        function () {

            if (!hasProration.checked) {
                return;
            }


            syncMainProject();
        }
    );


    /*
     * Mudança no valor total deve atualizar
     * percentuais e diferença.
     */
    totalValue.addEventListener(
        'input',
        recalculate
    );


    /*
     * Adicionar nova obra.
     */
    btnAdd.addEventListener(
        'click',
        function () {

            createAllocationRow();
        }
    );


    /*
     * ==========================================================
     * ESTADO INICIAL
     * ==========================================================
     */

    if (hasProration.checked) {

        openProration();

    } else {

        section.classList.add(
            'd-none'
        );
    }

})();