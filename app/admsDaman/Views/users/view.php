<?php

// Incluir arquivo rsponsável por alerta
include './app/admsDaman/Views/partials/alerts.php';

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