<?php

use App\admsDaman\Helpers\CSRFHelper;

echo "<h3>Listar Usuários</h3>";

// Incluir arquivo rsponsável por alerta
include './app/admsDaman/Views/partials/alerts.php';

// Acessa o IF quando encontrar o elemento no array users
if ($this->data['users'] ?? false) {

// Gerar o token CSRF para validar o usuário
    $csrf_token = CSRFHelper::generateCSRFToken('form_delete_user');
    
    // Percorrer o array de usuários
    foreach ($this->data['users'] as $user) :
        extract($user);
        echo "ID: $id<br>";
        echo "Nome: $name<br>";
        echo "Email: $email<br>";
        echo "Username: $username<br>";
        echo "<a href='{$_ENV['URL_ADM']}view-user/$id'>Detalhes</a><br>";
        echo "<a href='{$_ENV['URL_ADM']}view-user/$id'>Visualizar</a><br>";
        echo "<a href='{$_ENV['URL_ADM']}update-user/$id'>Editar</a><br>";
        echo "<a href='{$_ENV['URL_ADM']}update-password-user/$id'>Editar Senha</a><br>";
    ?>

    <!-- Formulário par aenvio dos dados para deletar Usuário -->
        <form action="delete-user" method="POST">

            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <input type="hidden" name="id" id="id" value="<?php echo $id ?? ''; ?>">

            <button type="submit">Apagar</button>

        </form>

        <hr>

    <?php
    endforeach;
    // Adiconar o arquivo de paginação
    require_once './app/admsDaman/Views/partials/pagination.php';
} else {
    echo "<p style='color: #f00;'>Nenhum Usuário encontrado</p>";
}