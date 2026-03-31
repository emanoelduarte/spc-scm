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

// Bloquear página inteira e exibir um ícone de carregamento (spinner) no centro da página
function showLoading() {

    // Exibe o overlay com o spinner
    document.getElementById('loadingOverlay').classList.remove('d-none');

}

/** Mostrar ou esconder periodo de locação conforme escolha do usuário */
document.getElementById("adms_daman_order_types_id").addEventListener("change", function () {
    var valor = this.value;
    var campo = document.getElementById("locationPeriod");

    if (valor == "2") { // 2 = locação
        campo.classList.remove("d-none");
    } else {
        campo.classList.add("d-none");
    }
});

/**
 * Função para adicionar e remover itens de um novo pedido
 */

document.getElementById('add-item').addEventListener('click', function () {
    const container = document.getElementById('items-container');

    const newItem = document.createElement('div');
    newItem.classList.add('row', 'g-1', 'item-group', 'mb-2');

    newItem.innerHTML = `
        <div class="col-lg-6 col-md-6 col-sm-12">
            <input type="text" name="description[]" class="form-control" placeholder="Descrição completa: Marca, modelo e referências, evitando compras erradas.">
        </div>

        <div class="col-lg-3 col-md-3 col-sm-12">
            <input type="text" name="quantity[]" class="form-control" placeholder="Qtd">
        </div>

        <div class="col-lg-3 col-md-3 col-sm-12">
            <div class="d-flex">
                <input type="text" name="unit[]" class="form-control me-2" placeholder="Un">
                <button type="button" class="btn btn-danger btn-remove">-</button>
            </div>
        </div>
    `;

    container.appendChild(newItem);
});

// Remover item
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('btn-remove')) {
        e.target.closest('.item-group').remove();
    }
});