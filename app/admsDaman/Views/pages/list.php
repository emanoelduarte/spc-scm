<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar a requisição de exclusão de Página
$csrf_token = CSRFHelper::generateCSRFToken('form_delete_page');

?>
<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Páginas</h2>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?php echo $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Páginas</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Filtrar</span>
        </div>

        <div class="card-body">

            <?php  // Formulário para buscar Página 
            ?>
            <form action="" method="POST">
                <div class="col-lg-12 col-md-12 col-sm-12 d-flex justify-content-center align-items-center">

                    <input type="text" class="form-control w-50 p-2" name="name"
                        value="<?= ($this->data['search']['name'] ?? '') ?>" placeholder="Pesquise por nome da pagina">
                    <button type="submit" class="btn btn-success h-100 ms-1"><i
                            class="fa-solid fa-magnifying-glass"></i>
                        Buscar
                    </button>
                    <a href="<?= $_ENV['URL_ADM'] . 'list-pages'; ?>" class="btn btn-secondary h-100 ms-1">
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
                <?php if (in_array("CreatePage", $this->data['buttonPermissions'])) : ?>
                <a href="<?= $_ENV['URL_ADM'] . 'create-page'; ?>" class="btn btn-success btn-sm"><i
                        class="fa-solid fa-circle-plus"></i> Cadastrar</a>
                <?php endif; ?>
            </span>
        </div>
        <div class="card-body">
            <?php // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            // Acessa o IF quando encontrar o elemento no array páginas
            if ($this->data['pages'] ?? false) :
            ?>

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Status</th>
                        <th scope="col">Pública</th>
                        <th scope="col" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Percorrer o array de páginas
                        foreach ($this->data['pages'] as $page) :
                            extract($page);
                        ?>

                    <tr>
                        <td><?= $id ?></td>
                        <td><?= $name ?></td>
                        <td class="d-none d-md-table-cell">
                            <?= $page_status ? "<span class='badge text-bg-success'>Ativa</span>" : "<span class='badge text-bg-danger'>Inativa</span>"; ?>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <?= $public_page ? "<span class='badge text-bg-success'>Sim</span>" : "<span class='badge text-bg-danger'>Não</span>"; ?>
                        </td>
                        <td class="text-center">
                            <?php if (in_array("ViewPage", $this->data['buttonPermissions'])) : ?>
                            <a href="<?= $_ENV['URL_ADM'] . 'view-page/' . $id; ?>"
                                class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>
                            <?php endif; ?>

                            <?php if (in_array("UpdatePage", $this->data['buttonPermissions'])) : ?>
                            <a href="<?= $_ENV['URL_ADM'] . 'update-page/' . $id; ?>"
                                class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i>
                                Editar</a>
                            <?php endif; ?>

                            <?php if (in_array("DeletePage", $this->data['buttonPermissions'])) : ?>
                            <?php  // Formulário para envio dos dados para deletar Página 
                                    ?>
                            <form id="formDelete<?= $id; ?>" action="<?= $_ENV['URL_ADM']; ?>delete-page" method="POST"
                                class="d-inline">

                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                <input type="hidden" name="id" id="id" value="<?php echo $id ?? ''; ?>">

                                <button type="submit" class="btn btn-danger btn-sm me-1 mb-1"
                                    onclick="confirmDeletion(event, <?= $id ?>)"><i class="fa-solid fa-trash"></i>
                                    Apagar</button>

                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php endforeach ?>
                </tbody>
            </table>
            <?php
                // Adiconar o arquivo de paginação
                require_once './app/admsDaman/Views/partials/pagination.php';
            else :
                echo "<div class='alert alert-danger' role='alert'>Nenhuma página encontrado</div>";
            endif;
            ?>
        </div>
    </div>

</div>