(() => {
    const form = document.getElementById('financialRenegotiationForm');

    if (!form) {
        return;
    }

    const supplierSelect = document.getElementById('adms_daman_supplier_id');
    const message = document.getElementById('renegotiationInstallmentsMessage');
    const tableWrapper = document.getElementById('renegotiationInstallmentsTableWrapper');
    const sourceBody = document.getElementById('renegotiationInstallmentsBody');
    const selectAll = document.getElementById('selectAllRenegotiationInstallments');
    const selectionBadge = document.getElementById('renegotiationSelectionBadge');
    const selectedPrincipal = document.getElementById('selectedPrincipalAmount');
    const selectedDocumentsSummary = document.getElementById('selectedDocumentsSummary');
    const interestInput = document.getElementById('interest_amount');
    const penaltyInput = document.getElementById('penalty_amount');
    const discountInput = document.getElementById('discount_amount');
    const renegotiatedAmountInput = document.getElementById('renegotiatedAmount');
    const installmentsCountInput = document.getElementById('renegotiationInstallmentsCount');
    const firstDueDateInput = document.getElementById('renegotiationFirstDueDate');
    const generateButton = document.getElementById('btnGenerateRenegotiationInstallments');
    const newInstallmentsBody = document.getElementById('renegotiationNewInstallmentsBody');
    const newInstallmentsPreview = document.getElementById('renegotiationInstallmentsPreview');
    const installmentsTotalElement = document.getElementById('renegotiationInstallmentsTotal');
    const saveButton = document.getElementById('btnSaveFinancialRenegotiation');

    const oldSelectedData = document.getElementById('renegotiationOldSelectedIds');
    const oldInstallmentsData = document.getElementById('renegotiationOldInstallments');

    let oldSelectedIds = [];
    let oldInstallments = [];
    let sourceInstallments = [];

    try {
        oldSelectedIds = JSON.parse(oldSelectedData?.textContent || '[]');
        if (!Array.isArray(oldSelectedIds)) {
            oldSelectedIds = [];
        }
        oldSelectedIds = oldSelectedIds.map(Number).filter((id) => id > 0);
    } catch (error) {
        oldSelectedIds = [];
    }

    try {
        oldInstallments = JSON.parse(oldInstallmentsData?.textContent || '[]');
        if (!Array.isArray(oldInstallments)) {
            oldInstallments = [];
        }
    } catch (error) {
        oldInstallments = [];
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function parseMoneyToCents(value) {
        let normalized = String(value ?? '')
            .trim()
            .replace(/[^0-9,.-]/g, '');

        if (!normalized) {
            return 0;
        }

        const hasComma = normalized.includes(',');
        const hasDot = normalized.includes('.');

        if (hasComma && hasDot) {
            normalized = normalized.replaceAll('.', '').replace(',', '.');
        } else if (hasComma) {
            normalized = normalized.replace(',', '.');
        }

        const number = Number(normalized);

        if (!Number.isFinite(number)) {
            return 0;
        }

        return Math.round(number * 100);
    }

    function formatMoneyFromCents(cents, withPrefix = false) {
        const value = (Number(cents || 0) / 100).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        return withPrefix ? `R$ ${value}` : value;
    }

    function formatDate(value) {
        if (!value) {
            return '-';
        }

        const parts = String(value).split('-');
        if (parts.length !== 3) {
            return value;
        }

        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function statusBadge(status) {
        const meta = {
            AV: ['bg-primary', 'AV'],
            AT: ['bg-warning text-dark', 'AT'],
            ON: ['bg-danger', 'ON'],
            AP: ['bg-info text-dark', 'AP'],
        }[status] || ['bg-secondary', status || '-'];

        return `<span class="badge ${meta[0]}">${escapeHtml(meta[1])}</span>`;
    }

    function setLoading(messageText) {
        sourceBody.innerHTML = '';
        tableWrapper.classList.add('d-none');
        message.classList.remove('d-none');
        message.innerHTML = messageText;
        sourceInstallments = [];
        updateSummary();
    }

    async function loadSourceInstallments() {
        const supplierId = Number(supplierSelect.value || 0);

        if (supplierId <= 0) {
            setLoading('Selecione um fornecedor para carregar as parcelas disponíveis.');
            return;
        }

        setLoading(
            '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Carregando parcelas...'
        );

        try {
            const endpoint = `${form.dataset.installmentsUrl}${supplierId}`;
            const response = await fetch(endpoint, {
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Falha ao carregar parcelas.');
            }

            sourceInstallments = Array.isArray(result.installments)
                ? result.installments
                : [];

            renderSourceInstallments();
        } catch (error) {
            setLoading(
                `<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>${escapeHtml(error.message || 'Não foi possível carregar as parcelas.')}</span>`
            );
        }
    }

    function renderSourceInstallments() {
        sourceBody.innerHTML = '';

        if (sourceInstallments.length === 0) {
            tableWrapper.classList.add('d-none');
            message.classList.remove('d-none');
            message.textContent = 'Este fornecedor não possui parcelas disponíveis para renegociação.';
            updateSummary();
            return;
        }

        const oldSelectedSet = new Set(oldSelectedIds.map(Number));

        sourceInstallments.forEach((installment) => {
            const id = Number(installment.id || 0);
            const checked = oldSelectedSet.has(id);
            const remainingCents = parseMoneyToCents(installment.remaining_amount);
            const originalCents = parseMoneyToCents(installment.original_amount);
            const paidCents = parseMoneyToCents(installment.principal_paid);

            const row = document.createElement('tr');
            row.dataset.installmentId = String(id);
            row.dataset.documentId = String(installment.adms_daman_purchase_document_id || '');
            row.dataset.remainingCents = String(remainingCents);

            row.innerHTML = `
                <td class="text-center">
                    <input
                        type="checkbox"
                        class="form-check-input renegotiation-source-checkbox"
                        name="source_installment_ids[]"
                        value="${id}"
                        ${checked ? 'checked' : ''}>
                </td>
                <td>
                    <div class="fw-semibold">${escapeHtml(installment.document_number || '-')}</div>
                    <small class="text-muted">${escapeHtml(installment.document_type_label || 'Documento')}</small>
                </td>
                <td>${escapeHtml(installment.installment_number || '-')}</td>
                <td>${escapeHtml(formatDate(installment.due_date))}</td>
                <td>${statusBadge(String(installment.display_status || ''))}</td>
                <td>${escapeHtml(installment.project_names || '-')}</td>
                <td class="text-end">${formatMoneyFromCents(originalCents, true)}</td>
                <td class="text-end">${formatMoneyFromCents(paidCents, true)}</td>
                <td class="text-end fw-semibold">${formatMoneyFromCents(remainingCents, true)}</td>
            `;

            sourceBody.appendChild(row);
        });

        message.classList.add('d-none');
        tableWrapper.classList.remove('d-none');

        bindSourceCheckboxes();
        updateSummary();
    }

    function getSelectedRows() {
        return Array.from(
            sourceBody.querySelectorAll('.renegotiation-source-checkbox:checked')
        ).map((checkbox) => checkbox.closest('tr')).filter(Boolean);
    }

    function getSelectedPrincipalCents() {
        return getSelectedRows().reduce(
            (total, row) => total + Number(row.dataset.remainingCents || 0),
            0
        );
    }

    function getRenegotiatedCents() {
        return Math.max(
            0,
            getSelectedPrincipalCents()
                + parseMoneyToCents(interestInput.value)
                + parseMoneyToCents(penaltyInput.value)
                - parseMoneyToCents(discountInput.value)
        );
    }

    function updateSummary() {
        const selectedRows = getSelectedRows();
        const principalCents = getSelectedPrincipalCents();
        const renegotiatedCents = getRenegotiatedCents();
        const documentIds = new Set(
            selectedRows.map((row) => row.dataset.documentId).filter(Boolean)
        );

        selectedPrincipal.value = formatMoneyFromCents(principalCents);
        renegotiatedAmountInput.value = formatMoneyFromCents(renegotiatedCents);

        selectionBadge.textContent =
            `${selectedRows.length} selecionada${selectedRows.length === 1 ? '' : 's'}`;

        selectedDocumentsSummary.textContent =
            `${documentIds.size} documento${documentIds.size === 1 ? '' : 's'} / `
            + `${selectedRows.length} parcela${selectedRows.length === 1 ? '' : 's'}`;

        const allCheckboxes = sourceBody.querySelectorAll('.renegotiation-source-checkbox');
        const checkedCheckboxes = sourceBody.querySelectorAll('.renegotiation-source-checkbox:checked');

        if (selectAll) {
            selectAll.checked =
                allCheckboxes.length > 0
                && checkedCheckboxes.length === allCheckboxes.length;

            selectAll.indeterminate =
                checkedCheckboxes.length > 0
                && checkedCheckboxes.length < allCheckboxes.length;
        }

        updateNewInstallmentsTotal();
        updateSaveState();
    }

    function bindSourceCheckboxes() {
        sourceBody
            .querySelectorAll('.renegotiation-source-checkbox')
            .forEach((checkbox) => {
                checkbox.addEventListener('change', updateSummary);
            });
    }

    function addMonthsClamped(dateString, monthsToAdd) {
        const parts = dateString.split('-').map(Number);

        if (parts.length !== 3 || parts.some((value) => !Number.isFinite(value))) {
            return '';
        }

        const [year, month, day] = parts;
        const targetMonthIndex = (month - 1) + monthsToAdd;
        const targetYear = year + Math.floor(targetMonthIndex / 12);
        const targetMonth = ((targetMonthIndex % 12) + 12) % 12;
        const lastDay = new Date(targetYear, targetMonth + 1, 0).getDate();
        const targetDay = Math.min(day, lastDay);

        return [
            targetYear,
            String(targetMonth + 1).padStart(2, '0'),
            String(targetDay).padStart(2, '0'),
        ].join('-');
    }

    function renderNewInstallments(installments) {
        newInstallmentsBody.innerHTML = '';

        installments.forEach((installment, index) => {
            const row = document.createElement('tr');
            const amountCents = parseMoneyToCents(installment.original_amount);

            row.innerHTML = `
                <td class="fw-semibold">${index + 1}/${installments.length}</td>
                <td>
                    <input
                        type="date"
                        class="form-control form-control-sm renegotiation-new-due-date"
                        name="installments[${index}][due_date]"
                        value="${escapeHtml(installment.due_date || '')}"
                        required>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">R$</span>
                        <input
                            type="text"
                            class="form-control renegotiation-new-amount"
                            name="installments[${index}][original_amount]"
                            value="${formatMoneyFromCents(amountCents)}"
                            required>
                    </div>
                </td>
            `;

            newInstallmentsBody.appendChild(row);
        });

        newInstallmentsPreview.classList.toggle('d-none', installments.length === 0);
        bindNewInstallmentInputs();
        updateNewInstallmentsTotal();
        updateSaveState();
    }

    function generateInstallments() {
        const count = Number(installmentsCountInput.value || 0);
        const firstDueDate = firstDueDateInput.value;
        const totalCents = getRenegotiatedCents();

        if (getSelectedRows().length === 0) {
            alert('Selecione ao menos uma parcela de origem.');
            return;
        }

        if (!Number.isInteger(count) || count <= 0 || count > 120) {
            alert('Informe uma quantidade de parcelas entre 1 e 120.');
            return;
        }

        if (!firstDueDate) {
            alert('Informe o vencimento da primeira parcela.');
            return;
        }

        if (totalCents <= 0) {
            alert('O valor final da renegociação precisa ser maior que zero.');
            return;
        }

        const baseCents = Math.floor(totalCents / count);
        let distributed = 0;
        const installments = [];

        for (let index = 0; index < count; index += 1) {
            const amountCents =
                index === count - 1
                    ? totalCents - distributed
                    : baseCents;

            distributed += amountCents;

            installments.push({
                due_date: addMonthsClamped(firstDueDate, index),
                original_amount: formatMoneyFromCents(amountCents),
            });
        }

        renderNewInstallments(installments);
    }

    function bindNewInstallmentInputs() {
        newInstallmentsBody
            .querySelectorAll('.renegotiation-new-amount')
            .forEach((input) => {
                input.addEventListener('input', () => {
                    updateNewInstallmentsTotal();
                    updateSaveState();
                });

                input.addEventListener('blur', () => {
                    input.value = formatMoneyFromCents(parseMoneyToCents(input.value));
                    updateNewInstallmentsTotal();
                    updateSaveState();
                });
            });

        newInstallmentsBody
            .querySelectorAll('.renegotiation-new-due-date')
            .forEach((input) => {
                input.addEventListener('change', updateSaveState);
            });
    }

    function getNewInstallmentsTotalCents() {
        return Array.from(
            newInstallmentsBody.querySelectorAll('.renegotiation-new-amount')
        ).reduce(
            (total, input) => total + parseMoneyToCents(input.value),
            0
        );
    }

    function updateNewInstallmentsTotal() {
        const totalCents = getNewInstallmentsTotalCents();
        installmentsTotalElement.textContent = formatMoneyFromCents(totalCents, true);

        const expectedCents = getRenegotiatedCents();

        installmentsTotalElement.classList.toggle(
            'text-danger',
            totalCents > 0 && totalCents !== expectedCents
        );

        installmentsTotalElement.classList.toggle(
            'text-success',
            totalCents > 0 && totalCents === expectedCents
        );
    }

    function updateSaveState() {
        const selectedCount = getSelectedRows().length;
        const renegotiatedCents = getRenegotiatedCents();
        const installmentRows = newInstallmentsBody.querySelectorAll('tr');
        const installmentTotalCents = getNewInstallmentsTotalCents();
        const allDueDatesFilled = Array.from(
            newInstallmentsBody.querySelectorAll('.renegotiation-new-due-date')
        ).every((input) => Boolean(input.value));

        saveButton.disabled = !(
            Number(supplierSelect.value || 0) > 0
            && selectedCount > 0
            && renegotiatedCents > 0
            && installmentRows.length > 0
            && installmentTotalCents === renegotiatedCents
            && allDueDatesFilled
        );
    }

    [interestInput, penaltyInput, discountInput].forEach((input) => {
        input.addEventListener('input', updateSummary);
        input.addEventListener('blur', () => {
            input.value = formatMoneyFromCents(parseMoneyToCents(input.value));
            updateSummary();
        });
    });

    supplierSelect.addEventListener('change', () => {
        oldSelectedIds = [];
        newInstallmentsBody.innerHTML = '';
        newInstallmentsPreview.classList.add('d-none');
        loadSourceInstallments();
    });

    selectAll?.addEventListener('change', () => {
        sourceBody
            .querySelectorAll('.renegotiation-source-checkbox')
            .forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });

        updateSummary();
    });

    generateButton.addEventListener('click', generateInstallments);

    if (oldInstallments.length > 0) {
        renderNewInstallments(oldInstallments);
    }

    if (Number(supplierSelect.value || 0) > 0) {
        loadSourceInstallments();
    } else {
        updateSummary();
    }
})();
