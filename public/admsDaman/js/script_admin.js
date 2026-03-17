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
