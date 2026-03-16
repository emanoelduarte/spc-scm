<?php

// Usar operador Ternário para verificar se existe a mensagem de sucesso e erro
echo isset($_SESSION['success']) ? "<div class='alert alert-success' role='alert'>{$_SESSION['success']}</div>" : "";

// Acessa o IF quando encontrar o elemento no array errors
if (isset($_SESSION['errors'])) {

    foreach ($_SESSION['errors'] as $error) {
        echo "<div class='alert alert-danger' role='alert'>$error</div>";
    }
}

echo isset($_SESSION['error']) ? "<div class='alert alert-danger' role='alert'>{$_SESSION['error']}</div>" : "";

// Destrua o que estiver na sessão
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['errors']);


// Acessa o IF quando encontrar o elemento no array errors
if (isset($this->data['errors'])) {

    foreach ($this->data['errors'] as $error) {
        echo "<div class='alert alert-danger' role='alert'>$error</div>";
    }
}