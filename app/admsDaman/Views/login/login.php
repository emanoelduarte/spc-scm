<?php

use App\admsDaman\Helpers\CSRFHelper;

?>
<div class="col-lg-5">
    <div class="card shadow-lg border-0 rounded-lg mt-5">
        <div class="card-header">
            <h3 class="text-center font-weight-light my-4">Área Restrita</h3>

            <div class="card-body">
                <?php 
                    // Incluir arquivo rsponsável por alerta
                    include './app/admsDaman/Views/partials/alerts.php';
                ?>

                <form action="" method="POST">

                    <input type="hidden" name="csrf_token" value="<?= CSRFHelper::generateCSRFToken('form_login'); ?>" id="">

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="username" name="username" value="<?= $this->data['form']['username'] ?? ''; ?>" placeholder="Usuário de acesso">
                        <label for="username">Usuário</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" name="password" value="<?= $this->data['form']['password'] ?? ''; ?>" placeholder="Senha">
                        <label for="password">Senha</label>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                        <a href="<?= $_ENV['URL_ADM'] ?>forgot-password" class="small text-decoration-none">Esqueceu a senha?</a>
                        <button type="submit" class="btn btn-primary btn-sm">Acessar</button>
                    </div>

                </form>
            </div>

            <div class="card-footer text-center py-3">
                Usuário: emanoel@damanarqeng.com.br<br>
                Senha: 123456A#<br>
            </div>

        </div>
    </div>
</div>