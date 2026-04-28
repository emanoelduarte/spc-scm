<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_delete_order');
?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Pedidos</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Pedidos</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Filtrar</span>
        </div>

        <div class="card-body">

            <?php  // Formulário para buscar pedido por item 
            ?>
            <form action="" method="POST">
                <div class="col-lg-12 col-md-12 col-sm-12 d-flex justify-content-center align-items-center">

                    <input type="text" class="form-control w-50 p-2" name="description"
                        value="<?= ($this->data['search']['description'] ?? '') ?>" placeholder="Pesquise por item">
                    <button type="submit" class="btn btn-success h-100 ms-1">
                        Buscar
                    </button>

                </div>
            </form>

            <div class="d-flex justify-content-end">
                <button id="openFilter" class="btn btn-outline-secondary mt-2">
                    <i class="fa-solid fa-filter"></i> Filtros
                </button>
            </div>

        </div>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Listar</span>
            <span class="ms-auto">
                <a href="<?= $_ENV['URL_ADM'] . 'create-order'; ?>" class="btn btn-success btn-sm"><i
                        class="fa-solid fa-user-plus"></i> Cadastrar</a>
            </span>
        </div>

        <div class="card-body">
            <?php // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            // Acessa o IF quando encontrar o elemento no array orders
            if ($this->data['orders'] ?? false) {
            ?>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Pedido</th>
                            <th scope="col">Obra</th>
                            <th scope="col" class="d-none d-md-table-cell">Status</th>
                            <th scope="col" class="d-none d-md-table-cell">Data do Pedido</th>
                            <th scope="col" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Percorrer o array de Pedidos
                        foreach ($this->data['orders'] as $order) {
                            extract($order);

                            $created = ($created_at ? date('d/m/Y', strtotime($created_at)) : "");
                        ?>

                            <?php
                            // Verificar o Status e aplicar a cor na borda do pedido
                            switch ($status_id) {
                                case 1:
                                    $highlightClass = "highlight-analysis";
                                    break;
                                case 2:
                                    $highlightClass = "highlight-budget";
                                    break;
                                case 3:
                                    $highlightClass = "highlight-purchased";
                                    break;
                                case 4:
                                    $highlightClass = "highlight-partial-purchase";
                                    break;
                                case 5:
                                    $highlightClass = "highlight-delivered";
                                    break;
                                case 6:
                                    $highlightClass = "highlight-partial-delivery";
                                    break;
                                case 7:
                                    $highlightClass = "highlight-rented";
                                    break;
                                case 8:
                                    $highlightClass = "highlight-returned";
                                    break;
                                case 9:
                                    $highlightClass = "highlight-partial-return";
                                    break;
                                case 10:
                                    $highlightClass = "highlight-canceled";
                                    break;
                                default:
                                    $highlightClass = "highlight-analysis";
                            }
                            ?>

                            <tr class="<?= $highlightClass ?>">
                                <td><?= $pedido_id; ?></td>
                                <td><?= $project_name; ?></td>
                                <td><?= $status_name; ?></td>

                                <td class="d-none d-md-table-cell"><?= $created; ?></td>
                                <td class="d-md-flex flex-row justify-content-center">
                                    <a href="<?= $_ENV['URL_ADM'] . 'view-order/' . $pedido_id; ?>"
                                        class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>

                                    <?php if (($order) and ($order['adms_daman_acquisition_types_id'] == 1)): ?>

                                        <a href="<?= $_ENV['URL_ADM'] . 'update-order/' . $pedido_id; ?>"
                                            class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i>
                                            Editar</a>

                                    <?php else: ?>

                                        <a href="<?= $_ENV['URL_ADM'] . 'update-rental-order/' . $pedido_id; ?>"
                                            class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i>
                                            Editar</a>

                                    <?php endif; ?>


                                    <?php  // Formulário para envio dos dados para deletar Pedido 
                                    // 
                                    ?>
                                    <form id="formDelete<?= $pedido_id; ?>" action="<?= $_ENV['URL_ADM']; ?>delete-order"
                                        method="POST">

                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                                        <input type="hidden" name="id" id="id" value="<?= $pedido_id ?? ''; ?>">

                                        <button type="submit" class="btn btn-danger btn-sm me-1 mb-1"
                                            onclick="confirmDeletion(event, <?= $pedido_id ?>)"> <i
                                                class="fa-solid fa-trash"></i> Apagar</button>

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
                echo "<div class='alert alert-danger' role='alert'>Nenhum Pedido encontrado</div>";
            }
            ?>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="overlay"></div>

<!-- Sidebar de Filtro -->
<div id="filterSidebar">
    <h5>Filtrar Pedidos</h5>

    <form action="" method="POST" id="filterForm">

        <?php // Campo para pesquisar pedido por numero 
        ?>
        <div class="mb-2">
            <label class="mt-4 fw-bold">Nº Pedido</label>
            <input type="text" class="form-control" id="order_number"
                value="<?= ($this->data['search']['order_number'] ?? '') ?>" name="order_number"
                placeholder="Número do pedido">
        </div>

        <div class="mb-2">
            <label class="fw-bold">Obra</label>

            <select name="adms_daman_project_id" class="form-select" id="adms_daman_project_id">
                <option value="" selected>Selecione</option>

                <?php
                // Verificar se existe pacotes
                if ($this->data['getAllProjectsSelect'] ?? false) {

                    // Percorrer array de pacotes
                    foreach ($this->data['getAllProjectsSelect'] as $getAllProjectsSelect) {
                        extract($getAllProjectsSelect);

                        // Verificar se deve manter selecionada a opção
                        $selected = isset($this->data['search']['adms_daman_project_id']) && $this->data['search']['adms_daman_project_id'] == $id ? 'selected' : '';

                        echo "<option value='$id' $selected>$name</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="mb-2">
            <label class="fw-bold">Status</label>
            <select name="adms_daman_acquisition_status_id" class="form-select" id="adms_daman_acquisition_status_id">
                <option value="" selected>Selecione</option>

                <?php
                // Verificar se existe pacotes
                if ($this->data['getAllStatusSelect'] ?? false) {

                    // Percorrer array de pacotes
                    foreach ($this->data['getAllStatusSelect'] as $getAllStatusSelect) {
                        extract($getAllStatusSelect);

                        // Verificar se deve manter selecionada a opção
                        $selected = isset($this->data['search']['adms_daman_acquisition_status_id']) && $this->data['search']['adms_daman_acquisition_status_id'] == $id ? 'selected' : '';

                        echo "<option value='$id' $selected>$name</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="mb-2">
            <label class="fw-bold">Categoria</label>
            <select name="adms_daman_category_id" class="form-select" id="adms_daman_category_id">
                <option value="" selected>Selecione</option>

                <?php
                // Verificar se existe pacotes
                if ($this->data['getAllCategoriesSelect'] ?? false) {

                    // Percorrer array de pacotes
                    foreach ($this->data['getAllCategoriesSelect'] as $getAllCategoriesSelect) {
                        extract($getAllCategoriesSelect);

                        // Verificar se deve manter selecionada a opção
                        $selected = isset($this->data['search']['adms_daman_category_id']) && $this->data['search']['adms_daman_category_id'] == $id ? 'selected' : '';

                        echo "<option value='$id' $selected>$name</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="mb-2">
            <label class="fw-bold">Data Inicial</label>
            <input type="date" name="data_inicio" class="form-control">
        </div>

        <div class="mb-2">
            <label class="fw-bold">Data Final</label>
            <input type="date" name="data_fim" class="form-control">
        </div>

        <button type="submit" class="btn btn-success w-100 mt-3">
            Filtrar
        </button>

    </form>
</div>