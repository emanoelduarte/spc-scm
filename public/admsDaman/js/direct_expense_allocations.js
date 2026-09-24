document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('directExpenseForm');
    const hasProration = document.getElementById('has_proration');
    const section = document.getElementById('directExpenseAllocationSection');
    const rows = document.getElementById('directExpenseAllocationRows');
    const addButton = document.getElementById('btnAddDirectExpenseAllocation');
    const amountInput = document.getElementById('amount');
    const projectInput = document.getElementById('adms_daman_project_id');
    const totalExpenseEl = document.getElementById('directExpenseAllocationDocumentTotal');
    const totalAllocationEl = document.getElementById('directExpenseAllocationTotal');
    const differenceEl = document.getElementById('directExpenseAllocationDifference');
    const projectsData = document.getElementById('directExpenseProjectsData');
    const oldAllocationsData = document.getElementById('directExpenseAllocationsOldData');

    if (
        !form
        || !hasProration
        || !section
        || !rows
        || !addButton
        || !amountInput
        || !projectInput
    ) {
        return;
    }

    let projects = [];
    let oldAllocations = [];
    let nextIndex = 0;

    try {
        projects = JSON.parse(projectsData?.value || '[]');
    } catch (error) {
        projects = [];
    }

    try {
        oldAllocations = JSON.parse(oldAllocationsData?.value || '[]');
    } catch (error) {
        oldAllocations = [];
    }

    function parseMoneyToCents(value) {
        let normalized = String(value || '')
            .replace(/R\$/gi, '')
            .replace(/\s/g, '');

        if (normalized.includes(',')) {
            normalized = normalized
                .replace(/\./g, '')
                .replace(',', '.');
        }

        const number = Number(normalized);

        return Number.isFinite(number)
            ? Math.round(number * 100)
            : 0;
    }

    function formatMoney(cents) {
        return (cents / 100).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    function escapeHtml(value) {
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function projectOptions(selectedId = '') {
        let html = '<option value="">Selecione</option>';

        projects.forEach(function (project) {
            const selected = String(project.id) === String(selectedId)
                ? ' selected'
                : '';

            html += '<option value="'
                + String(project.id)
                + '"'
                + selected
                + '>'
                + escapeHtml(String(project.name || ''))
                + '</option>';
        });

        return html;
    }

    function addAllocationRow(allocation = {}, isMainProject = false) {
        const index = nextIndex++;
        const projectId = allocation.adms_daman_project_id || '';
        const allocatedAmount = allocation.allocated_amount || '';

        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end direct-expense-allocation-row';
        row.dataset.mainProject = isMainProject ? '1' : '0';

        const projectLabel = isMainProject
            ? 'Obra principal'
            : 'Obra';

        const hiddenProjectInput = isMainProject
            ? `<input
                    type="hidden"
                    class="direct-expense-allocation-project-hidden"
                    name="allocations[${index}][adms_daman_project_id]"
                    value="${escapeHtml(String(projectId))}">`
            : '';

        const projectNameAttribute = isMainProject
            ? ''
            : `name="allocations[${index}][adms_daman_project_id]"`;

        const actionColumn = isMainProject
            ? '<div class="col-lg-1 col-md-1"></div>'
            : `<div class="col-lg-1 col-md-1 d-grid">
                    <button
                        type="button"
                        class="btn btn-outline-danger btn-remove-direct-expense-allocation"
                        title="É necessário manter ao menos uma obra adicional no rateio"
                        disabled>
                        <i class="fa-solid fa-trash"></i>
                    </button>
               </div>`;

        row.innerHTML = `
            <div class="col-lg-7 col-md-6">
                <label class="form-label mb-1">${projectLabel}</label>
                <select
                    class="form-select direct-expense-allocation-project"
                    ${projectNameAttribute}
                    ${isMainProject ? 'disabled' : ''}>
                    ${projectOptions(projectId)}
                </select>
                ${hiddenProjectInput}
            </div>

            <div class="col-lg-4 col-md-5">
                <label class="form-label mb-1">Valor rateado</label>
                <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input
                        type="text"
                        inputmode="decimal"
                        class="form-control text-end direct-expense-allocation-amount"
                        name="allocations[${index}][allocated_amount]"
                        value="${escapeHtml(String(allocatedAmount))}"
                        placeholder="0,00">
                </div>
            </div>

            ${actionColumn}
        `;

        rows.appendChild(row);

        row.querySelector('.btn-remove-direct-expense-allocation')
            ?.addEventListener('click', function () {
                const additionalRows = rows.querySelectorAll(
                    '.direct-expense-allocation-row[data-main-project="0"]'
                );

                if (additionalRows.length <= 1) {
                    return;
                }

                row.remove();
                updateRemoveButtons();
                updateSummary();
            });

        row.querySelector('.direct-expense-allocation-amount')
            ?.addEventListener('input', updateSummary);

        updateRemoveButtons();
        updateSummary();
    }

    function updateRemoveButtons() {
        const additionalRows = Array.from(
            rows.querySelectorAll(
                '.direct-expense-allocation-row[data-main-project="0"]'
            )
        );

        const canRemove = additionalRows.length > 1;

        additionalRows.forEach(function (allocationRow) {
            const removeButton = allocationRow.querySelector(
                '.btn-remove-direct-expense-allocation'
            );

            if (!removeButton) {
                return;
            }

            removeButton.disabled = !canRemove;
            removeButton.title = canRemove
                ? 'Remover obra'
                : 'É necessário manter ao menos uma obra adicional no rateio';
        });
    }

    function updateSummary() {
        const totalExpense = parseMoneyToCents(amountInput.value);
        let allocatedTotal = 0;

        rows.querySelectorAll('.direct-expense-allocation-amount')
            .forEach(function (input) {
                allocatedTotal += parseMoneyToCents(input.value);
            });

        const difference = totalExpense - allocatedTotal;

        if (totalExpenseEl) {
            totalExpenseEl.textContent = formatMoney(totalExpense);
        }

        if (totalAllocationEl) {
            totalAllocationEl.textContent = formatMoney(allocatedTotal);
            totalAllocationEl.classList.toggle('text-success', difference === 0);
            totalAllocationEl.classList.toggle('text-danger', difference !== 0);
        }

        if (differenceEl) {
            differenceEl.textContent = formatMoney(Math.abs(difference));
            differenceEl.classList.toggle('text-success', difference === 0);
            differenceEl.classList.toggle('text-danger', difference !== 0);
        }
    }

    function getMainAllocationRow() {
        return rows.querySelector(
            '.direct-expense-allocation-row[data-main-project="1"]'
        );
    }

    function syncMainAllocationProject() {
        const mainRow = getMainAllocationRow();

        if (!mainRow) {
            return;
        }

        const projectId = projectInput.value || '';
        const projectSelect = mainRow.querySelector(
            '.direct-expense-allocation-project'
        );
        const hiddenProject = mainRow.querySelector(
            '.direct-expense-allocation-project-hidden'
        );

        if (projectSelect) {
            projectSelect.value = projectId;
        }

        if (hiddenProject) {
            hiddenProject.value = projectId;
        }
    }

    function ensureInitialRows() {
        if (rows.children.length > 0) {
            syncMainAllocationProject();
            updateRemoveButtons();
            return;
        }

        addAllocationRow({
            adms_daman_project_id: projectInput.value || '',
            allocated_amount: ''
        }, true);

        addAllocationRow();
    }

    function restoreAllocationRows() {
        if (oldAllocations.length === 0) {
            return;
        }

        const mainProjectId = String(projectInput.value || '');

        const mainAllocationIndex = oldAllocations.findIndex(
            function (allocation) {
                return String(
                    allocation.adms_daman_project_id || ''
                ) === mainProjectId;
            }
        );

        if (mainAllocationIndex >= 0) {
            addAllocationRow(
                oldAllocations[mainAllocationIndex],
                true
            );

            oldAllocations.forEach(function (allocation, index) {
                if (index !== mainAllocationIndex) {
                    addAllocationRow(allocation);
                }
            });
        } else {
            addAllocationRow({
                adms_daman_project_id: mainProjectId,
                allocated_amount: ''
            }, true);

            oldAllocations.forEach(function (allocation) {
                addAllocationRow(allocation);
            });
        }

        const additionalRows = rows.querySelectorAll(
            '.direct-expense-allocation-row[data-main-project="0"]'
        );

        if (additionalRows.length === 0) {
            addAllocationRow();
        }

        updateRemoveButtons();
    }

    function toggleProration() {
        section.classList.toggle(
            'd-none',
            !hasProration.checked
        );

        if (hasProration.checked) {
            ensureInitialRows();
        }

        updateRemoveButtons();
        updateSummary();
    }

    addButton.addEventListener('click', function () {
        addAllocationRow();
    });

    hasProration.addEventListener(
        'change',
        toggleProration
    );

    amountInput.addEventListener(
        'input',
        updateSummary
    );

    projectInput.addEventListener('change', function () {
        if (!hasProration.checked) {
            return;
        }

        syncMainAllocationProject();
    });

    form.addEventListener('submit', function (event) {
        if (!hasProration.checked) {
            return;
        }

        const allocationRows = Array.from(
            rows.querySelectorAll(
                '.direct-expense-allocation-row'
            )
        );

        if (allocationRows.length < 2) {
            event.preventDefault();
            alert('O rateio deve possuir pelo menos duas obras.');
            return;
        }

        const projectIds = [];
        let allocatedTotal = 0;

        for (const row of allocationRows) {
            const project = row.querySelector(
                '.direct-expense-allocation-project'
            );
            const allocationAmount = row.querySelector(
                '.direct-expense-allocation-amount'
            );

            if (!project?.value) {
                event.preventDefault();
                alert('Selecione uma obra em todas as linhas do rateio.');
                project?.focus();
                return;
            }

            if (projectIds.includes(project.value)) {
                event.preventDefault();
                alert('A mesma obra não pode aparecer duas vezes no rateio.');
                project.focus();
                return;
            }

            const cents = parseMoneyToCents(
                allocationAmount?.value || ''
            );

            if (cents <= 0) {
                event.preventDefault();
                alert('Informe um valor maior que zero para cada obra do rateio.');
                allocationAmount?.focus();
                return;
            }

            projectIds.push(project.value);
            allocatedTotal += cents;
        }

        if (!projectIds.includes(String(projectInput.value || ''))) {
            event.preventDefault();
            alert('A obra principal deve fazer parte do rateio.');
            return;
        }

        const totalExpense = parseMoneyToCents(
            amountInput.value
        );

        if (allocatedTotal !== totalExpense) {
            event.preventDefault();
            alert('A soma do rateio deve ser igual ao valor total da despesa.');
        }
    });

    restoreAllocationRows();
    toggleProration();
});
