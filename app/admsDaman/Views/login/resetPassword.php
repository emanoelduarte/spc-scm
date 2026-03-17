<?php

use App\admsDaman\Helpers\CSRFHelper;

?>
<div class="col-lg-5">
    <div class="card shadow-lg border-0 rounded-lg mt-5">
        <div class="card-header">
            <h3 class="text-center font-weight-light my-4">Nova Senha</h3>

            <div class="card-body">

                <?php
                // Incluir arquivo responsável por alerta
                include './app/admsDaman/Views/partials/alerts.php';
                ?>


                <form action="" method="POST">

                    <input type="hidden" name="csrf_token" value="<?= CSRFHelper::generateCSRFToken('form_reset_password'); ?>" id="">

                    <!-- Campo e-mail do usuário -->
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" name="email" id="email" value="<?= $this->data['form']['email'] ?? ''; ?>" placeholder="Seu melhor email">
                                        <label for="email"> E-mail: </label>
                                    </div>

                    <!-- Campo senha do usuário -->
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="password" id="password" value="<?= $this->data['form']['password'] ?? ''; ?>" placeholder="Digite uma senha com 6 caracters">
                        <label for="password"> Senha: </label>
                    </div>

                    <!-- Campo confirmar senha do usuário -->
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" value="<?= $this->data['form']['confirm_password'] ?? ''; ?>" placeholder="Confirmar Senha">
                        <label for="confirm_password"> Confirmar Senha: </label>
                    </div>

                    <div class="form-floating mb-3">
                    <button type="submit" class="btn btn-primary btn-sm">Salvar</button>
                    </div>
                </form> 
            <div class="card-footer text-center py-3">
                <div class="small">
                    <a href="<?php echo $_ENV['URL_ADM'] ?>login">Clique aqui</a> para acessar<br><br>
                </div>
            </div>
        </div>
    </div>
</div>

</form>