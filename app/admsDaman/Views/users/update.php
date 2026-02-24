<?php

use App\admsDaman\Helpers\CSRFHelper;

echo "<h3>Editar Usuário</h3>";

echo "<a href='{$_ENV['URL_ADM']}list-users'>Listar usuários</a><br>";
echo "<a href='{$_ENV['URL_ADM']}view-user/" . ($this->data['form']['id'] ?? '') . "'>Visualizar </a><br><br>";

// Incluir arquivo rsponsável por alerta
include './app/admsDaman/Views/partials/alerts.php';

?>
<form action="" method="POST">

    <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_update_user'); ?>" id="">

    <input type="hidden" name="id" id="id" value="<?php echo $this->data['form']['id'] ?? ''; ?>">
    
    <!-- Operador de qualiscência nula em PHP (??) - Serve para fornecer um valor padrão se uma determinada chave não estiver presente ou for nula. -->
    <label for="name"> Nome: </label>
    <input type="text" name="name" id="name" value="<?php echo $this->data['form']['name'] ?? ''; ?>" placeholder="Seu nome"><br><br>

    <label for="email"> E-mail: </label>
    <input type="email" name="email" id="email" value="<?php echo $this->data['form']['email'] ?? ''; ?>" placeholder="Seu melhor email"><br><br>

    <label for="username"> Usuário: </label>
    <input type="text" name="username" id="username" value="<?php echo $this->data['form']['username'] ?? ''; ?>" placeholder="Nome de usuário"><br><br>

    <button type="submit">Editar</button>

</form>