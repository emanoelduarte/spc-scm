<?php

use App\admsDaman\Helpers\CSRFHelper;

echo "<h3>Nova Senha</h3>";

// Incluir arquivo responsável por alerta
include './app/admsDaman/Views/partials/alerts.php';

?>

<form action="" method="POST">

    <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_reset_password'); ?>" id="">

    <!-- Campo e-mail do usuário -->
    <label for="email"> E-mail: </label>
    <input type="email" name="email" id="email" value="<?php echo $this->data['form']['email'] ?? ''; ?>" placeholder="Seu melhor email"><br><br>

    <!-- Campo senha do usuário -->
    <label for="password"> Senha: </label>
    <input type="password" name="password" id="password" value="<?php echo $this->data['form']['password'] ?? ''; ?>" placeholder="Digite uma senha com 6 caracters"><br><br>

    <!-- Campo confirmar senha do usuário -->
    <label for="confirm_password"> Confirmar Senha: </label>
    <input type="password" name="confirm_password" id="confirm_password" value="<?php echo $this->data['form']['confirm_password'] ?? ''; ?>" placeholder="Confirmar Senha"><br><br>

    <button type="submit">Salvar</button>

</form>