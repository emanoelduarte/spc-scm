<?php

namespace App\admsDaman\Controllers\login;

use App\admsDaman\Controllers\Services\Validation\ValidationLoginService;
use App\admsDaman\Controllers\Services\ValidationUserLogin;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller Login
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\adms\Controllers\login
 */
class Login 
{

/** @var array|string|null $dados Rece os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;


    public function index()
    {
        // Receber os dados do formulário de Login
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_login', $this->data['form']['csrf_token'])) {

            // Chamar método Login
            $this->login();
        } else {
            // Chamar o método carregar a view Login
            $this->viewLogin();
        }
    }

    /**
     * Carregar a visualização de criação de usuário.
     * 
     * Este método configura os dados necessários e carrega a view para a criação de um novo usuário.
     * 
     * @return void
     */
    private function viewLogin(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Login";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/login/login", $this->data);
        $loadView->loadViewLogin();
    }

    /**
     * Metódo de Login
     */
    private function login()
    {
        // Validar os dados do formulário instanciando a validação
        // Instaciar a classe que valida os dados do formulário com Rakit
        $validationLogin = new ValidationLoginService();
        $this->data['errors'] = $validationLogin->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view Login
            $this->viewLogin();
            return;
        }

        // Instancia a classe Login
        $validationUserLogin = new ValidationUserLogin();
        $result = $validationUserLogin->validationUserLogin($this->data['form']);

        if ($result) {
            // Redirecionar o usuário para a página dashboard
            header("Location: {$_ENV['URL_ADM']}dashboard");
        } else {
            // Chamar o método carregar a view Login
            $this->viewLogin();
            return;
        }
    }
}