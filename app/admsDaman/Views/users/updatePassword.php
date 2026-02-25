<?php

use App\admsDaman\Helpers\CSRFHelper;

?>

<div class="container-fluid px-4">
    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Usuários</h2>
        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-users">Usuários</a>
            </li>
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] . 'view-user/' . ($this->data['form']['id'] ?? '') ?>">Visualizar</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Editar Senha</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Editar Senha do Usuário</span>
            <span class="ms-auto d-sm-flex flex-row">
                <a href="<?= $_ENV['URL_ADM'] . 'list-users'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>

                <a href="<?= $_ENV['URL_ADM'] . 'view-user/' . ($this->data['form']['id'] ?? ''); ?>" class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>
            </span>
        </div>

        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            ?>

            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= CSRFHelper::generateCSRFToken('form_edit_password_user'); ?>" id="">

                <input type="hidden" name="id" id="id" value="<?= $this->data['form']['id'] ?? ''; ?>">

                <input type="hidden" name="email" id="email" value="<?= $this->data['form']['email'] ?? ''; ?>">

                <div class="col-md-6">
                    <label for="password" class="form-label">Senha:</label>
                    <input type="password" class="form-control" id="password" name="password" value="<?= $this->data['form']['password'] ?? ''; ?>" placeholder="Digite uma senha com 6 caracters">
                </div>

                <div class="col-md-6">
                    <label for="confirm_password" class="form-label">Confirmar Senha:</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" value="<?= $this->data['form']['confirm_password'] ?? ''; ?>" placeholder="Confirme sua senha">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-warning btn-sm">Editar Senha</button>
                </div>

                </form>
        </div>
    </div>
</div>