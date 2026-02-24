<?php

namespace App\admsDaman\Controllers\login;

/**
 * Classe responsável em quebrar a sessão do usuário
 */
class Logout
{

    public function index(): void
    {
        // Destirui as seções ativas do usuário
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);

        // Criar a mensagem de sucesso ao cadastrar
        $_SESSION['success'] = "Saiu com sucesso";

        // Redirecionar o usuário para a página de listar usuário
        header("Location: {$_ENV['URL_ADM']}login");
    }
}