<?php

use App\admsDaman\Helpers\CSRFHelper;

?>

<div class="container-fluid px-4">
    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Obras</h2>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-projects">Obras</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </li>
        </ol>
    </div>
    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Editar</span>
            <span class="ms-auto d-sm-flex flex-row">
                <?php if (in_array("ListProjects", $this->data['buttonPermissions'])) : ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'list-projects'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>

                <?php if (in_array("ViewProject", $this->data['buttonPermissions'])) : ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'view-project/' . ($this->data['form']['id'] ?? ''); ?>" class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>
                <?php endif; ?>
            </span>
        </div>

        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            ?>

            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_update_project'); ?>" id="">

                <input type="hidden" name="id" id="id" value="<?php echo $this->data['form']['id'] ?? ''; ?>">

                <div class="col-12">
                    <label for="name" class="form-label">Nome:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= $this->data['form']['name'] ?? ''; ?>" placeholder="Obra">
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Endereço:</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?= $this->data['form']['address'] ?? ''; ?>" placeholder="Endereço/Logradouro">
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" placeholder="Descrição da Obra" name="description" id="description" style="height: 100px"><?= $this->data['form']['description'] ?? ''; ?></textarea>
                </div>

                <div class="col-md-6 col-sm-12">
                    <label for="status" class="form-label">Status</label>

                    <select name="status" class="form-select" id="status">
                        <option value="" selected>Selecione</option>
                        <option value="1" <?= isset($this->data['form']['status']) && $this->data['form']['status'] == 1 ? 'selected' : ''; ?>>Ativa</option>
                        <option value="0" <?= isset($this->data['form']['status']) && $this->data['form']['status'] == 0 ? 'selected' : ''; ?>>Inativa</option>
                    </select>

                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-warning btn-sm">Editar</button>
                </div>

            </form>
        </div>
    </div>
</div>