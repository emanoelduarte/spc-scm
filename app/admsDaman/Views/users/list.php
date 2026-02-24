<?php

echo "<a href='{$_ENV["URL_ADM"]}create-user'>Cadastrar Usuário</a>";

echo "<h3>Listar Usuários</h3>";

// Usar operador Ternário para verificar se existe a mensagem de sucesso e erro
echo isset($_SESSION['success']) ? "<p style='color: #086;'>{$_SESSION['success']}</p>" : "";

echo isset($_SESSION['error']) ? "<p style='color: #f00;'>{$_SESSION['error']}</p>" : "";

// Destrua o que estiver na sessão
unset($_SESSION['success'], $_SESSION['error']);

// Acessa o IF quando encontrar o elemento no array users
if ($this->data['users'] ?? false) {
    foreach ($this->data['users'] as $user) {
        extract($user);
        echo "ID: $id<br>";
        echo "Nome: $name<br>";
        echo "Email: $email<br>";
        echo "Username: $username<br>";
        echo "<a href='{$_ENV['URL_ADM']}view-user/$id'>Detalhes</a><br>";
        echo "<a href='{$_ENV['URL_ADM']}view-user/$id'>Visualizar</a><br>";
        echo "<a href='{$_ENV['URL_ADM']}update-user/$id'>Editar</a><br>";
        echo "<hr>";
    }
} else {
    echo "<p style='color: #f00;'>Nenhum Usuário encontrado</p>";
}