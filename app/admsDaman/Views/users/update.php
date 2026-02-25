<?php

use App\admsDaman\Helpers\CSRFHelper;

echo "<h3>Editar Usuário</h3>";

// Incluir arquivo rsponsável por alerta
include './app/admsDaman/Views/partials/alerts.php';

?>
<form action="" method="POST">

    <input type="hidden" name="csrf_token" value="<?= CSRFHelper::generateCSRFToken('form_update_user'); ?>" id="">

    <input type="hidden" name="id" id="id" value="<?= $this->data['form']['id'] ?? ''; ?>">
    
    <!-- Operador de qualiscência nula em PHP (??) - Serve para fornecer um valor padrão se uma determinada chave não estiver presente ou for nula. -->
    <label for="name"> Nome: </label>
    <input type="text" name="name" id="name" value="<?= $this->data['form']['name'] ?? ''; ?>" placeholder="Seu nome"><br><br>

    <label for="email"> E-mail: </label>
    <input type="email" name="email" id="email" value="<?= $this->data['form']['email'] ?? ''; ?>" placeholder="Seu melhor email"><br><br>

    <label for="username"> Usuário: </label>
    <input type="text" name="username" id="username" value="<?= $this->data['form']['username'] ?? ''; ?>" placeholder="Nome de usuário"><br><br>

    <button type="submit">Editar</button>

</form>