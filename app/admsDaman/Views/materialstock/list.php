<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_stock_movement');
?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Estoque</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Estoque</li>
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
                <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">

                    <input type="text" class="form-control flex-grow-1" name="name"
                        value="<?= ($this->data['search']['name'] ?? '') ?>" placeholder="Pesquise por item">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success text-nowrap flex-grow-1 flex-sm-grow-0">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>

                        <a href="<?= $_ENV['URL_ADM'] . 'list-material-stock'; ?>"
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
                <?php if (in_array("CreateMaterialStock", $this->data['buttonPermissions'])): ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'create-Material-Stock'; ?>" class="btn btn-success btn-sm"><i
                            class="fa-solid fa-user-plus"></i> Cadastrar</a>
                <?php endif; ?>
            </span>
        </div>

        <div class="card-body">
            <?php // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            // Acessa o IF quando encontrar o elemento no array material
            if ($this->data['materialStock'] ?? false) {
            ?>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nome</th>
                            <th scope="col">Obra</th>
                            <th scope="col">Qtd</th>
                            <th scope="col" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        // Percorrer o array de usuários
                        foreach ($this->data['materialStock'] as $material) {
                            extract($material);
                        ?>
                            <tr>
                                <td><?= $id ?></td>
                                <td><?= $name ?></td>
                                <td><?= $project_name ?></td>
                                <td><?= $current_quantity ?></td>
                                <td class="text-center">

                                    <?php if (in_array("ViewMaterialStock", $this->data['buttonPermissions'])): ?>
                                        <a href="<?= $_ENV['URL_ADM'] . 'view-material-stock/' . $id; ?>"
                                            class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>
                                    <?php endif; ?>

                                    <?php if (in_array("CreateStockMovement", $this->data['buttonPermissions'])): ?>
                                        <button class="btn btn-success btn-sm me-1 mb-1" data-bs-toggle="modal"
                                            data-project="<?= $adms_daman_project_id ?>" data-bs-target="#modalMovement"
                                            data-id="<?= $id ?>" data-name="<?= htmlspecialchars($name) ?>" data-type="input">
                                            Entrada
                                        </button>

                                        <button class="btn btn-danger btn-sm me-1 mb-1" data-bs-toggle="modal"
                                            data-bs-target="#modalMovement" data-id="<?= $id ?>"
                                            data-name="<?= htmlspecialchars($name) ?>" data-type="output">
                                            Saída
                                        </button>
                                    <?php endif; ?>

                                    <?php if (in_array("UpdateMaterialStock", $this->data['buttonPermissions'])): ?>
                                        <a href="<?= $_ENV['URL_ADM'] . 'update-material-stock/' . $id; ?>"
                                            class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i>
                                            Editar</a>
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
                echo "<div class='alert alert-danger' role='alert'>Nenhum material encontrado</div>";
            }
            ?>
        </div>
    </div>
</div>

<?php // Motal de abertura do formulário de entrada e saída
?>

<div class="modal fade" id="modalMovement" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Registrar Movimentação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= $_ENV['URL_ADM'] ?>create-stock-movement" method="POST">

                <div class="modal-body">

                    <input type="hidden" name="redirect_to" value="<?= $_ENV['URL_ADM'] ?>list-material-stock">

                    <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                    <input type="hidden" name="stock_id" id="stockId">
                    <input type="hidden" name="type" id="movementType">

                    <p class="text-muted mb-3">Item: <strong id="itemName"></strong></p>

                    <div class="mb-3">
                        <label class="form-label">Quantidade</label>
                        <input type="number" name="quantity" class="form-control" min="0.01" step="0.01" required>
                    </div>

                    <div class="mb-3" id="projectField">
                        <label class="form-label">Obra de destino</label>
                        <select name="adms_daman_project_id" class="form-select" required>
                            <option value="">Selecione</option>
                            <?php foreach ($this->data['getAllProjectsSelectActive'] as $project) : ?>
                                <option value="<?= $project['id'] ?>">
                                    <?= htmlspecialchars($project['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Obra fixa para entrada -->
                    <input type="hidden" name="adms_daman_project_id" id="projectFixed">

                    <div class="mb-3 d-none" id="reasonField">
                        <label class="form-label">Motivo da saída</label>
                        <select name="reason" class="form-select">
                            <option value="">Selecione</option>
                            <option value="consumption">Consumo na obra</option>
                            <option value="transfer">Transferência para outra obra</option>
                            <option value="return">Devolução ao fornecedor</option>
                            <option value="discard">Descarte</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observação</label>
                        <textarea name="observation" class="form-control" rows="2" placeholder="Opcional"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="modalBtn">Confirmar</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="overlay"></div>

<!-- Sidebar de Filtro -->
<div id="filterSidebar">
    <h5>Filtrar Material</h5>

    <form action="" method="POST" id="filterForm">

        <?php // Campo para pesquisar Material por numero de registro
        ?>
        <div class="mb-2">
            <label class="mt-4 fw-bold">ID</label>
            <input type="text" class="form-control" id="id_number"
                value="<?= ($this->data['search']['id_number'] ?? '') ?>" name="id_number" placeholder="Número do item">
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
        <a href="<?= $_ENV['URL_ADM'] . 'list-orders'; ?>" class="btn btn-secondary w-100 mt-3">
            <i class="fa-solid fa-filter-circle-xmark"></i> Limpar
        </a>
    </form>
</div>