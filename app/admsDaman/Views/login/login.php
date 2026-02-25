<?php

use App\admsDaman\Helpers\CSRFHelper;

echo "<h3>Formulário de Login</h3>";

// Incluir arquivo rsponsável por alerta
include './app/admsDaman/Views/partials/alerts.php';

?>

<form action="" method="POST">

    <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_login'); ?>" id="">

    <!-- Operador de qualiscência nula em PHP (??) - Serve para fornecer um valor padrão se uma determinada chave não estiver presente ou for nula. -->
    <label for="username"> Usuário: </label>
    <input type="text" name="username" id="username" value="<?php echo $this->data['form']['username'] ?? ''; ?>" placeholder="Usuário de Acesso"><br><br>

    <label for="password"> Senha: </label>
    <input type="password" name="password" id="password" value="<?php echo $this->data['form']['password'] ?? ''; ?>" placeholder="Digite a senha de acesso"><br><br>


    <button type="submit">Login</button><br><br>

</form>

<a href="<?php echo $_ENV['URL_ADM'] ?>forgot-password">Recuperar senha</a><br><br>

Usuário: emanoel@damanarqeng.com.br<br><br>
Senha: 123456A#<br>