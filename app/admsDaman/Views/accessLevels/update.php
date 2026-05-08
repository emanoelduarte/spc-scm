<?php

use App\admsDaman\Helpers\CSRFHelper;

?>
<div class="container-fluid px-4">
    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Níveis de acesso</h2>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-users">Níveis de acesso</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Editar</span>
            <span class="ms-auto d-sm-flex flex-row">
                <span class="ms-auto d-sm-flex flex-row">
                    <?php if (in_array('ViewAccessLevel', $this->data['buttonPermissions'])) : ?>
                        <a href="<?= $_ENV['URL_ADM'] . 'view-access-level/' . $this->data['form']['id']; ?>" class="btn btn-secondary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>
                    <?php endif; ?>
                    <?php if (in_array('ListAccessLevels', $this->data['buttonPermissions'])) : ?>
                        <a href="<?= $_ENV['URL_ADM'] . 'list-access-levels'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>
                    <?php endif; ?>
                </span>
            </span>
        </div>

        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            ?>
            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= CSRFHelper::generateCSRFToken('form_update_level'); ?>" id="">

                <input type="hidden" name="id" id="id" value="<?= $this->data['form']['id'] ?? ''; ?>">

                <div class="col-12">
                    <label for="name" class="form-label">Nome:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= $this->data['form']['name'] ?? ''; ?>" placeholder="Nome do Nível de acesso">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-warning btn-sm" onclick="showLoading()">Editar</button>
                </div>

            </form>
        </div>
    </div>
</div>