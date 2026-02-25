<?php

use App\admsDaman\Helpers\CSRFHelper;

// Exibe o título da página
echo "<h3>Recuperar a senha</h3>";

// Incluir arquivo responsável por alerta
include './app/admsDaman/Views/partials/alerts.php';

?>

<!-- Formulário recuperar a senha -->
<form action="" method="POST">

    <!-- Campo oculto para o token CSRF para proteger o formulário contra ataques de falsificação de solicitação entre site -->
    <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_forgot_password'); ?>" id="">

    <!-- Campo para email -->
    <label for="email"> E-mail: </label>
    <input type="email" name="email" id="email" value="<?php echo $this->data['form']['email'] ?? ''; ?>" placeholder="Seu melhor email"><br><br>

    <button type="submit">Recuperar Senha</button>

</form>