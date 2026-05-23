function confirmDeletion(event, id) {

    // Para não recarregar a página então vou adicionar um preventDefault
    event.preventDefault();

    Swal.fire({
        title: "Tem certeza?",
        text: "Você não poderá reverter isso!",
        icon: "warning",
        showCancelButton: true,
        cancelButtonColor: "#0d6efd",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#dc3545",
        confirmButtonText: "Sim, excluir!"
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`formDelete${id}`).submit();
        }
    });
}

function confirmCancel(event, id) {

    // Para não recarregar a página então vou adicionar um preventDefault
    event.preventDefault();

    Swal.fire({
        title: "Tem certeza?",
        text: "Você não poderá reverter isso!",
        icon: "warning",
        showCancelButton: true,
        cancelButtonColor: "#0d6efd",
        cancelButtonText: "Não",
        confirmButtonColor: "#dc3545",
        confirmButtonText: "Sim, cancelar!"
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`formCancel${id}`).submit();
        }
    });
}

function confirmAprovation(event, id) {

    // Para não recarregar a página então vou adicionar um preventDefault
    event.preventDefault();

    Swal.fire({
        title: "Tem certeza?",
        text: "Você não poderá reverter isso!",
        icon: "warning",
        showCancelButton: true,
        cancelButtonColor: "#0d6efd",
        cancelButtonText: "Não",
        confirmButtonColor: "#4CAF50",
        confirmButtonText: "Sim, aprovar!"
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`formAprovation${id}`).submit();
        }
    });
}

// Bloquear página inteira e exibir um ícone de carregamento (spinner) no centro da página
function showLoading() {

    // Exibe o overlay com o spinner
    document.getElementById('loadingOverlay').classList.remove('d-none');

}

function showLoadingPdf() {
    document.getElementById('loadingOverlay').classList.remove('d-none');

    setTimeout(function () {
        document.getElementById('loadingOverlay').classList.add('d-none');
    }, 2000);
}

const div = document.querySelector('.adms_daman_acquisition_types_id');
const selectField = document.getElementById('locationPeriod');

if (div) {
    div.addEventListener('change', function () {
        if (this.value == 2) {
            selectField.style.display = 'block';
        } else {
            selectField.style.display = 'none';
        }
    });
}

/**
 * Incluir a opção do select de unidades dinamicamente
 */

// Espera o DOM carregar (IMPORTANTE)
document.addEventListener('DOMContentLoaded', () => {

    // Pega o elemento
    const unitsEl = document.getElementById('units-data');
    const itemsEl = document.getElementById('items-data');

    if (!unitsEl) {
        console.warn('Elemento #units-data não encontrado');
        return;
    }
    // Inicializa array
    let units = JSON.parse(unitsEl.dataset.units || '[]');

    // Converte JSON → objeto JS
    const oldItems = itemsEl?.dataset.oldItems
        ? JSON.parse(itemsEl.dataset.oldItems)
        : [];


    function createUnitOptions(selectedValue = null) {
        let options = `<option value="">Selecione</option>`;

        units.forEach(unit => {
            let selected = (unit.id == selectedValue) ? 'selected' : '';
            options += `<option value="${unit.id}" ${selected}>${unit.name}</option>`;
        });

        return options;
    }

    /**
     * Função para adicionar e remover itens
     */

    let itemIndex = document.querySelectorAll('.item-group').length;

    const addBtn = document.getElementById('add-item');

    if (addBtn) {
        addBtn.addEventListener('click', function () {

            const container = document.getElementById('items-container');

            const index = itemIndex++;

            const newItem = document.createElement('div');
            newItem.classList.add('row', 'g-1', 'item-group', 'mb-2');

            const selectedUnit = oldItems[index]?.adms_daman_measurement_units_id ?? '';

            const tempId = 'tmp_' + Date.now() + '_' + Math.random().toString(16).slice(2);

            newItem.innerHTML = `

                <input type="hidden" name="items[${index}][is_new]" value="1">
                <input type="hidden" name="items[${index}][item_id]" value="">
                <input type="hidden" name="items[${index}][temp_id]" value="${tempId}">
                
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <input type="text" name="items[${index}][description]" class="form-control" placeholder="Descrição completa: Marca, modelo e referências, evitando compras erradas.">
                </div>

                <div class="col-lg-3 col-md-3 col-sm-12">
                    <input type="text" name="items[${index}][quantity]" class="form-control" placeholder="Qtd.">
                </div>

                <div class="col-lg-3 col-md-3 col-sm-12">
                    <div class="d-flex">
                        <select name="items[${index}][adms_daman_measurement_units_id]" class="form-select me-2">
                            ${createUnitOptions(selectedUnit)}
                        </select>
                        <button type="button" class="btn btn-danger btn-remove">-</button>
                    </div>
                </div>
            `;

            container.appendChild(newItem);
        });
    }

    /**
     * Remover item (delegação de evento)
     */
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove')) {
            e.target.closest('.item-group').remove();
        }
    });

});

// Abrir e fechar o Overlay de filtros
const openBtn = document.getElementById('openFilter');
const sidebar = document.getElementById('filterSidebar');
const overlay = document.getElementById('overlay');

if (openBtn && sidebar && overlay) {

    openBtn.addEventListener('click', () => {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

}

document.addEventListener('DOMContentLoaded', function () {

    const modalMovement = document.getElementById('modalMovement');

    if (!modalMovement) {
        console.warn('Modal #modalMovement não encontrado');
        return;
    }

    modalMovement.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;
        const type = button.getAttribute('data-type');
        const projectId = button.getAttribute('data-project');

        const reasonField = document.getElementById('reasonField');
        const projectField = document.getElementById('projectField');
        const projectFixed = document.getElementById('projectFixed');

        if (type === 'input') {

            reasonField.classList.add('d-none');
            projectField.classList.add('d-none');

            projectField.querySelector('select').removeAttribute('required');

            projectFixed.value = projectId;
            projectFixed.removeAttribute('disabled');

            document.querySelector('[name="reason"]').removeAttribute('required');

            document.getElementById('modalTitle').textContent = 'Registrar Entrada';
            document.getElementById('modalBtn').className = 'btn btn-success';

        } else {

            reasonField.classList.remove('d-none');
            projectField.classList.remove('d-none');

            projectField.querySelector('select').setAttribute('required', 'required');
            projectField.querySelector('select').removeAttribute('disabled');

            projectFixed.setAttribute('disabled', 'disabled');
            projectFixed.value = '';

            document.querySelector('[name="reason"]').setAttribute('required', 'required');

            document.getElementById('modalTitle').textContent = 'Registrar Saída';
            document.getElementById('modalBtn').className = 'btn btn-danger';
        }

        document.getElementById('stockId').value = button.getAttribute('data-id');
        document.getElementById('movementType').value = type;
        document.getElementById('itemName').textContent = button.getAttribute('data-name');
        document.getElementById('itemNameHidden').value = button.getAttribute('data-name');
        document.getElementById('categoryIdHidden').value = button.getAttribute('data-category');

    });

});