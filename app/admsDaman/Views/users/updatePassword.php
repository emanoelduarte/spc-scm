<?php

use App\admsDaman\Helpers\CSRFHelper;

echo "<h3>Editar Senha</h3>";

echo "<a href='{$_ENV['URL_ADM']}list-users'>Listar usuários</a><br>";
echo "<a href='{$_ENV['URL_ADM']}view-user/" . ($this->data['form']['id'] ?? '') . "'>Visualizar </a><br><br>";

// Incluir arquivo rsponsável por alerta
include './app/admsDaman/Views/partials/alerts.php';

?>

<form action="" method="POST">

    <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_edit_password_user'); ?>" id="">

    <input type="hidden" name="id" id="id" value="<?php echo $this->data['form']['id'] ?? ''; ?>">

    <label for="password"> Senha: </label>
    <input type="password" name="password" id="password" value="<?php echo $this->data['form']['password'] ?? ''; ?>" placeholder="Digite uma senha com 6 caracters"><br><br>

    <label for="confirm_password"> Confirmar Senha: </label>
    <input type="password" name="confirm_password" id="confirm_password" value="<?php echo $this->data['form']['confirm_password'] ?? ''; ?>" placeholder="Confirmar Senha"><br><br>

    <button type="submit">Salvar</button>

</form>