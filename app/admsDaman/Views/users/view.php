<?php

echo "<a href='{$_ENV['URL_ADM']}list-users'>Listar usuários</a><br>";
echo "<a href='{$_ENV['URL_ADM']}update-user/" . ($this->data['user']['id'] ?? '') . "'>Editar </a><br><br>";

// Usar operador Ternário para verificar se existe a mensagem de sucesso e erro
echo isset($_SESSION['success']) ? "<p style='color: #086;'>{$_SESSION['success']}</p>" : "";

echo isset($_SESSION['error']) ? "<p style='color: #f00;'>{$_SESSION['error']}</p>" : "";

// Destrua o que estiver na sessão
unset($_SESSION['success'], $_SESSION['error']);

// Acessa o IF quando encontrar o elemento no array errors
if (isset($this->data['errors'])) {

    foreach ($this->data['errors'] as $error) {
        echo "<p style='color: #f00;'>$error</p>";
    }
}

echo "<h3>Visualizar Usuário</h3>";

// Acessa o IF quando encontrar o elemento no array users
if ($this->data['user'] ?? false) {
    // Extrair o Array pela coluna
    extract($this->data['user']);

    // Imprimir as informações do registro.
    echo "ID: $id<br>";
    echo "Nome: $name<br>";
    echo "Username: $username<br>";

    // O operador ternário verifica se $created_at não é null antes de chamar a strtotime(). Se $created_at for null, ele retorna uma strng vazia.
    echo "Cadastrado: " . ($created_at ? date('d/m/Y H:i:s', strtotime($created_at)) : "") . "<br>";
    echo "Editado: " . ($updated_at ? date('d/m/Y H:i:s', strtotime($updated_at)) : "") . " <br>";

    echo "<hr>";
} else { // Acessa o else quando não encontrar registro
    echo "<p style='color: #f00;'>Usuário não encontrado!</p>";
}