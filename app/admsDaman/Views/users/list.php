<?php

echo "<h3>Listar Usuários</h3>";

if (isset($_SESSION['error'])) {
    echo "<p style='color:#f00'>{$_SESSION['error']}</p>";
    unset($_SESSION['error']);
}

// Acessa o IF quando encontrar o elemento no array users
if ($this->data['users'] ?? false) {
    foreach ($this->data['users'] as $user) {
        extract($user);
        echo "ID: $id<br>";
        echo "Nome: $name<br>";
        echo "Email: $email<br>";
        echo "Username: $username<br>";
        echo "<a href='{$_ENV['URL_ADM']}view-user/$id'>Detalhes</a>";
        echo "<hr>";
    }
} else {
    echo "<p style='color: #f00;'>Nenhum Usuário encontrado</p>";
}