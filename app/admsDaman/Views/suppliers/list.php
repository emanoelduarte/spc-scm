<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o fornecedor
$csrf_token = CSRFHelper::generateCSRFToken('form_delete_supplier');
?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Fornecedores</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Fornecedor</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Filtrar</span>
        </div>

        <div class="card-body">

            <?php  // Formulário para buscar Fornecedor 
            ?>
            <form action="" method="POST">
                <div class="col-lg-12 col-md-12 col-sm-12 d-flex justify-content-center align-items-center">

                    <input type="text" class="form-control w-50 p-2" name="legal_name"
                        value="<?= ($this->data['search']['legal_name'] ?? '') ?>" placeholder="Pesquise por fornecedor">
                    <button type="submit" class="btn btn-success h-100 ms-1"><i class="fa-solid fa-magnifying-glass"></i> 
                        Buscar
                    </button>
                    <a href="<?= $_ENV['URL_ADM'] . 'list-suppliers'; ?>" class="btn btn-secondary h-100 ms-1">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Listar</span>
            <span class="ms-auto">
                <a href="<?= $_ENV['URL_ADM'] . 'create-supplier'; ?>" class="btn btn-success btn-sm"><i
                        class="fa-solid fa-user-plus"></i> Cadastrar</a>
            </span>
        </div>

        <div class="card-body">
            <?php // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            // Acessa o IF quando encontrar o elemento no array supplier
            if ($this->data['suppliers'] ?? false) {
            ?>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nome</th>
                            <th scope="col" class="d-none d-md-table-cell">CNPJ</th>
                            <th scope="col" class="d-none d-md-table-cell">Contato</th>
                            <th scope="col" class="d-none d-md-table-cell">Status</th>
                            <th scope="col" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Percorrer o array de usuários
                        foreach ($this->data['suppliers'] as $supplier) {
                            extract($supplier);
                        ?>
                            <tr>
                                <td><?= $id ?></td>
                                <td><?= $legal_name ?></td>
                                <td class="d-none d-md-table-cell"> <?= $cnpj; ?> </td>
                                <td class="d-none d-md-table-cell"> <?= $contact_name; ?> </td>
                                <td class="d-none d-md-table-cell">
                                    <?= $supplier_status ? "<span class='badge text-bg-success'>Ativo</span>" : "<span class='badge text-bg-danger'>Inativo</span>"; ?>
                                </td>
                                <td class="d-md-flex flex-row justify-content-center">
                                    <a href="<?= $_ENV['URL_ADM'] . 'view-supplier/' . $id; ?>"
                                        class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>

                                    <a href="<?= $_ENV['URL_ADM'] . 'update-supplier/' . $id; ?>" class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar</a>

                                    <?php  // Formulário para envio dos dados para deletar Obra 
                                    // 
                                    ?>
                                    <form id="formDelete<?= $id; ?>" action="<?= $_ENV['URL_ADM']; ?>delete-supplier" method="POST">

                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                                        <input type="hidden" name="id" id="id" value="<?= $id ?? ''; ?>">

                                        <button type="submit" class="btn btn-danger btn-sm me-1 mb-1" onclick="confirmDeletion(event, <?= $id ?>)"> <i class="fa-solid fa-trash"></i> Apagar</button>

                                    </form>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php
                // Adiconar o arquivo de paginação
                require_once './app/admsDaman/Views/partials/pagination.php';
            } else {
                echo "<div class='alert alert-danger' role='alert'>Nenhum Fornecedor encontrado</div>";
            }
            ?>
        </div>
    </div>
</div>