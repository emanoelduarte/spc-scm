<?php

namespace App\admsDaman\Controllers\Services;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\LoginRepository;

/**
 * Classe de Validação de  Login do usuário
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\Services
 */
class ValidationUserLogin
{
    /**
     * Método que valida o login do usuário no banco de dados
     * 
     * Caso os dados esteja corretos com algum cadastro no banco retorna true, do contrário false
     * @return bool
     */
    public function validationUserLogin(array $data): bool
    {
        // Instanciar o repository para validar o usuário do banco de daos
        $login = new LoginRepository();
        $result = $login->getUser((string) $data['username']);


        if (!$result) {
            // Chamar o método para salvar o log e salvar a mensagem na sessão de erro
            GenerateLog::generateLog("error", "Usuário incorreto", ['username' => $data['username']]);

            $_SESSION['error'] = "Usuário ou senha incorreto!";

            return false;
        }

        if (password_verify($data['password'], $result['password'])) {
            // Extrair o array para imprimir o elemento do array através do nome
            extract($result);

            // Salvar os dados do usuário na sessão
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            return true;
        } else {
            // Chamar o método para salvar o log e salvar a mensagem na sessão de erro
            GenerateLog::generateLog("error", "Senha incorreta", []);

            $_SESSION['error'] = "Usuário ou senha incorreto!";
        }
        return false;
    }
}