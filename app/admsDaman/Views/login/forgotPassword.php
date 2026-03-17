
<?php
    use App\admsDaman\Helpers\CSRFHelper;
?>
<div class="col-lg-5">
    <div class="card shadow-lg border-0 rounded-lg mt-5">
        <div class="card-header">
            <h3 class="text-center font-weight-light my-4">Recuperar a senha</h3>


            <div class="card-body">

                <?php
                // Incluir arquivo responsável por alerta
                include './app/admsDaman/Views/partials/alerts.php';
                ?>

                <!-- Formulário recuperar a senha -->
                <form action="" method="POST">

                    <!-- Campo oculto para o token CSRF para proteger o formulário contra ataques de falsificação de solicitação entre site -->
                    <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_forgot_password'); ?>" id="">

                    <!-- Campo para email -->
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" name="email" id="email" value="<?php echo $this->data['form']['email'] ?? ''; ?>" placeholder="Seu melhor email"><br><br>
                        <label for="email"> E-mail: </label>
                    </div>

                    <div class="form-floating mb-3">
                        <button type="submit" class="btn btn-primary btn-sm">Recuperar Senha</button> <br><br>
                    </div>

                </form>
            </div>
            <div class="card-footer text-center py-3">
                <div class="small">
                    <a href="<?php echo $_ENV['URL_ADM'] ?>login">Clique aqui</a> para acessar<br><br>
                </div>
            </div>
        </div>
    </div>
</div>