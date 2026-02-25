<?php

use App\admsDaman\Helpers\CSRFHelper;

echo "<h3>Cadastrar Usuário</h3>";

// Incluir arquivo rsponsável por alerta
include './app/admsDaman/Views/partials/alerts.php';

?>

<form action="" method="POST">

 <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_create_user'); ?>" id="">

    <label for="name"> Nome: </label>
    <input type="text" name="name" id="name" value="<?= $this->data['form']['name'] ?? ''; ?>" placeholder="Seu nome"><br><br>

    <label for="email"> E-mail: </label>
    <input type="email" name="email" id="email" value="<?= $this->data['form']['email'] ?? ''; ?>" placeholder="Seu melhor email"><br><br>

    <label for="password"> Senha: </label>
    <input type="password" name="password" id="password" value="<?= $this->data['form']['password'] ?? ''; ?>" placeholder="Digite uma senha com 6 caracters"><br><br>

    <label for="confirm_password"> Confirmar Senha: </label>
    <input type="password" name="confirm_password" id="confirm_password" value="<?php echo $this->data['form']['confirm_password'] ?? ''; ?>" placeholder="Confirmar Senha"><br><br>

    <button type="submit">Cadastrar</button>

</form>