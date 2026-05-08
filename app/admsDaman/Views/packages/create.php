<?php

use App\admsDaman\Helpers\CSRFHelper;
?>
<div class="container-fluid px-4">
    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Pacotes</h2>
        <ol class="breadcrumb mb-3 mt-3 ms-auto">

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-packages">Pacotes</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Cadastrar</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Cadastrar Pacote</span>
            <span class="ms-auto d-sm-flex flex-row">
                <?php if (in_array("ListPackages", $this->data['buttonPermissions'])) : ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'list-packages'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>
            </span>
        </div>
        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            ?>

            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_create_package'); ?>" id="">

                <div class="col-12">
                    <label for="name" class="form-label">Nome:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= $this->data['form']['name'] ?? ''; ?>" placeholder="Nome do Pacote">
                </div>

                <div class="col-12">
                    <label for="obs" class="form-label">Observação</label>
                    <textarea class="form-control" placeholder="Observação" name="obs" id="obs" style="height: 100px"><?= $this->data['form']['obs'] ?? ''; ?></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>