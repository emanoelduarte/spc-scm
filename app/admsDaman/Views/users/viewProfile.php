<?php

use App\admsDaman\Helpers\CSRFHelper;

$this->data['user'] = isset($this->data['form']);
?>
<div class="container-fluid px-4">
    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Perfil</h2>
        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Perfil</li>
            </li>
        </ol>
    </div>

    <?php
        // Incluir arquivo responsável por alerta
        include './app/admsDaman/Views/partials/alerts.php';
        ?>

    <div class="row align-items-start">
        <!-- Card lateral do perfil -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mx-auto mb-3 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                    style="width: 72px; height: 72px; font-size: 1.5rem; font-weight: 500; color: #0d6efd;">
                    <?= strtoupper(substr($_SESSION['user_name'] ?? '', 0, 1)) . strtoupper(substr(strrchr($_SESSION['user_name'] ?? '', ' '), 1, 1)) ?>
                </div>
                <h5 class="mb-1 fw-semibold"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($this->data['form']['email'] ?? '') ?></p>
            </div>
        </div>

        <!-- Cards de edição -->
        <div class="col-lg-9 col-md-12">

            <!-- Dados pessoais -->
            <div class="card border-0 shadow-sm mb-4 col-lg-12">
                <div class="card-body p-4">
                    <p class="text-uppercase text-muted small fw-semibold mb-3" style="letter-spacing: .05em;">Dados pessoais</p>
                    <form action="" method="POST">

                        <div class="mb-3">
                            <label class="form-label small text-muted">Nome</label>
                            <input type="text" disabled name="name" class="form-control"
                                value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-muted">E-mail</label>
                            <input type="email" disabled name="email" class="form-control"
                                value="<?= htmlspecialchars($this->data['form']['email'] ?? '') ?>">
                        </div>
                    </form>
                </div>
            </div>

            <!-- Alterar senha -->
            <div class="card border-0 shadow-sm col-lg-12">
                <div class="card-body p-4">
                    
                    <p class="text-uppercase text-muted small fw-semibold mb-3" style="letter-spacing: .05em;">Alterar senha</p>
                    <form action="" method="POST">

                    <input type="hidden" name="csrf_token" value="<?= CSRFHelper::generateCSRFToken('form_edit_password_user'); ?>" id="">

                        <input type="hidden" name="id" id="id" value="<?= $this->data['form']['id'] ?? ''; ?>">

                        <input type="hidden" name="email" id="email" value="<?= $this->data['form']['email'] ?? ''; ?>">

                        <div class="mb-3">
                            <label class="form-label small text-muted">Nova senha</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="••••••••">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-muted">Confirmar nova senha</label>
                            <input type="password" name="confirm_password" class="form-control"
                                placeholder="••••••••">
                        </div>

                        <button type="submit" class="btn btn-warning btn-sm px-4">
                            <i class="fas fa-lock me-1"></i> Alterar senha
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>