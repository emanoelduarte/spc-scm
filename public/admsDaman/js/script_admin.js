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
/**
 * Mostrar modal de saída e entrada de material
 */
const modalMovement = document.getElementById('modalMovement');

if (modalMovement) {

    modalMovement.addEventListener(
        'show.bs.modal',
        function (event) {

            const button = event.relatedTarget;

            if (!button) {
                return;
            }

            const type =
                button.getAttribute('data-type');

            const projectId =
                button.getAttribute('data-project');

            const modalBody =
                modalMovement.querySelector('.modal-body');

            const isEncarregado =
                modalBody?.getAttribute(
                    'data-is-encarregado'
                ) === '1';


            const reasonField =
                document.getElementById(
                    'reasonField'
                );

            const projectField =
                document.getElementById(
                    'projectField'
                );

            const projectFixed =
                document.getElementById(
                    'projectFixed'
                );

            const reasonSelect =
                document.querySelector(
                    '[name="reason"]'
                );

            const modalTitle =
                document.getElementById(
                    'modalTitle'
                );

            const modalBtn =
                document.getElementById(
                    'modalBtn'
                );


            /*
             * ENTRADA
             */
            if (type === 'input') {

                if (reasonField) {
                    reasonField.classList.add(
                        'd-none'
                    );
                }

                if (projectField) {

                    projectField.classList.add(
                        'd-none'
                    );

                    const projectSelect =
                        projectField.querySelector(
                            'select'
                        );

                    if (projectSelect) {

                        projectSelect.removeAttribute(
                            'required'
                        );
                    }
                }


                if (projectFixed) {

                    projectFixed.value =
                        projectId ?? '';

                    projectFixed.removeAttribute(
                        'disabled'
                    );
                }


                if (reasonSelect) {

                    reasonSelect.removeAttribute(
                        'required'
                    );
                }


                if (modalTitle) {

                    modalTitle.textContent =
                        'Registrar Entrada';
                }


                if (modalBtn) {

                    modalBtn.className =
                        'btn btn-success';
                }

            } else {

                /*
                 * SAÍDA
                 */
                if (isEncarregado) {

                    /*
                     * Encarregado:
                     * obra e motivo fixos.
                     */
                    if (projectFixed) {

                        projectFixed.value =
                            projectId ?? '';

                        projectFixed.removeAttribute(
                            'disabled'
                        );
                    }

                } else {

                    /*
                     * Outros níveis:
                     * mostrar campos normalmente.
                     */
                    if (reasonField) {

                        reasonField.classList.remove(
                            'd-none'
                        );
                    }


                    if (projectField) {

                        projectField.classList.remove(
                            'd-none'
                        );

                        const projectSelect =
                            projectField.querySelector(
                                'select'
                            );

                        if (projectSelect) {

                            projectSelect.setAttribute(
                                'required',
                                'required'
                            );

                            projectSelect.removeAttribute(
                                'disabled'
                            );
                        }
                    }


                    if (projectFixed) {

                        projectFixed.setAttribute(
                            'disabled',
                            'disabled'
                        );

                        projectFixed.value = '';
                    }


                    if (reasonSelect) {

                        reasonSelect.setAttribute(
                            'required',
                            'required'
                        );
                    }
                }


                if (modalTitle) {

                    modalTitle.textContent =
                        'Registrar Saída';
                }


                if (modalBtn) {

                    modalBtn.className =
                        'btn btn-danger';
                }
            }


            /*
             * Dados do item.
             */
            const stockId =
                document.getElementById(
                    'stockId'
                );

            const movementType =
                document.getElementById(
                    'movementType'
                );

            const itemName =
                document.getElementById(
                    'itemName'
                );

            const itemNameHidden =
                document.getElementById(
                    'itemNameHidden'
                );

            const categoryIdHidden =
                document.getElementById(
                    'categoryIdHidden'
                );


            if (stockId) {

                stockId.value =
                    button.getAttribute(
                        'data-id'
                    ) ?? '';
            }


            if (movementType) {

                movementType.value =
                    type ?? '';
            }


            if (itemName) {

                itemName.textContent =
                    button.getAttribute(
                        'data-name'
                    ) ?? '';
            }


            if (itemNameHidden) {

                itemNameHidden.value =
                    button.getAttribute(
                        'data-name'
                    ) ?? '';
            }


            if (categoryIdHidden) {

                categoryIdHidden.value =
                    button.getAttribute(
                        'data-category'
                    ) ?? '';
            }
        }
    );
}