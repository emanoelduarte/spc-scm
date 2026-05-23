<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar a compra
$csrf_token = CSRFHelper::generateCSRFToken('form_delete_purchasing');
?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Compras Pendentes de Aprovação</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Compras Pendentes</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Filtrar</span>
        </div>

        <div class="card-body">

            <?php  // Formulário para buscar compra 
            ?>
            <form action="" method="POST">
                <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">

                    <input type="text" class="form-control flex-grow-1" name="legal_name"
                        value="<?= ($this->data['search']['legal_name'] ?? '') ?>"
                        placeholder="Pesquise por Razão Social do fornecedor">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success text-nowrap flex-grow-1 flex-sm-grow-0"><i
                                class="fa-solid fa-magnifying-glass"></i>
                            Buscar
                        </button>
                        <a href="<?= $_ENV['URL_ADM'] . 'list-purchasing-quotes'; ?>"
                            class="btn btn-secondary text-nowrap flex-grow-1 flex-sm-grow-0">
                            <i class="fa-solid fa-filter-circle-xmark"></i> Limpar
                        </a>
                    </div>
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

            </span>
        </div>

        <div class="card-body">
            <?php // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            // Acessa o IF quando encontrar o elemento no array compras
            if ($this->data['purchasingsQuote'] ?? false) {
            ?>

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">N°. Compra</th>
                        <th scope="col">N°. Pedido</th>
                        <th scope="col" class="d-none d-md-table-cell">Comprador</th>
                        <th scope="col">Obra</th>
                        <th scope="col" class="d-none d-md-table-cell">Fornecedor</th>

                        <th scope="col" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                        // Percorrer o array de compras
                        foreach ($this->data['purchasingsQuote'] as $purchasing) {
                            extract($purchasing);
                        ?>
                    <tr>
                        <td><?= $id ?></td>
                        <td><?= $adms_daman_order_id ?></td>
                        <td class="d-none d-md-table-cell"><?= $buyer_name ?></td>
                        <td><?= $project_name ?></td>
                        <td class="d-none d-md-table-cell"><?= $trade_name ?></td>

                        <td class="text-center">

                            <?php if (in_array("ViewPurchasingQuote", $this->data['buttonPermissions'])): ?>

                            <a href="<?= $_ENV['URL_ADM'] . 'view-purchasing-quote/' . $id; ?>"
                                class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>

                            <?php endif; ?>

                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php
                // Adiconar o arquivo de paginação
                require_once './app/admsDaman/Views/partials/pagination.php';
            } else {
                echo "<div class='alert alert-danger' role='alert'>Nenhuma compra pendente encontrada!</div>";
            }
            ?>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="overlay"></div>

<!-- Sidebar de Filtro -->
<div id="filterSidebar">
    <h5>Filtrar Compras</h5>

    <form action="" method="POST" id="filterForm">

        <?php // Campo para pesquisar compra por numero 
        ?>
        <div class="mb-2">
            <label class="mt-4 fw-bold">Nº Compra</label>
            <input type="text" class="form-control" id="purchasing_number"
                value="<?= ($this->data['search']['purchasing_number'] ?? '') ?>" name="purchasing_number"
                placeholder="Número do Compra">
        </div>

        <div class="mb-2">
            <label class="fw-bold">Nº Pedido</label>
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
            <label class="fw-bold">Data Inicial</label>
            <input type="date" name="data_inicio" class="form-control">
        </div>

        <div class="mb-2">
            <label class="fw-bold">Data Final</label>
            <input type="date" name="data_fim" class="form-control">
        </div>

        <button type="submit" class="btn btn-success w-100 mt-3"><i class="fa-solid fa-filter"></i>
            Filtrar
        </button>
        <a href="<?= $_ENV['URL_ADM'] . 'list-purchasings'; ?>" class="btn btn-secondary w-100 mt-3">
            <i class="fa-solid fa-filter-circle-xmark"></i> Limpar
        </a>

    </form>
</div>