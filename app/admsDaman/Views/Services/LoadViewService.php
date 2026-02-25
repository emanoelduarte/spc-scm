<?php

namespace App\admsDaman\Views\Services;

class LoadViewService 
{

    /** @var string $view Recebe o endereço da VIEW */
    private string $view;

    /**
     *  Receber o endereço (fisico) da View e dos dados.
     * @param string $nameView Endereço (físico) da View que deve ser carregada
     * @param array|string|null $data Dados que a VIEW deve receber para exibir.
     * */
    public function __construct(private string $nameView, private array|string|null $data) {}

     /**
     * Carregar a View.
     * Verificar se o arquivo existe. Se existir ele carrega, se não existir deve apresentar um erro de carregamento.
     * 
     * @return void
     */
    public function loadView()
    {
        // Definir o caminho da View
        $this->view = './app/' . $this->nameView . '.php';
        if (file_exists($this->view)) {

            // Incluir o layout
            include './app/admsDaman/Views/layouts/main.php';

        }else {
            die("Erro 005: Tente novamente, caso o erro persista entre em contato com o administrador Emanoel Duarte {$_ENV['EMAIL_ADM']}");
        }

    }

    /**
     * Carregar a View Login.
     * Verificar se o arquivo existe. Se existir ele carrega o Layout Login,  que carregrá a view. Se não existir deve apresentar um erro de carregamento.
     * 
     * @return void
     * 
     * @throws Exception Se o arquivo da VIEW não for encontrado, exibe uma mensagem de erro e encerra a execução
     */

    public function loadViewLogin(): void
    {
        // Definir o caminho da View
        $this->view = './app/' . $this->nameView . '.php';
        if (file_exists($this->view)) {

            // Incluir o layout
            include './app/admsDaman/Views/layouts/login.php';
        } else {
            die("Erro 005: Tente novamente, caso o erro persista entre em contato com o administrador Emanoel Duarte {$_ENV['EMAIL_ADM']}");
        }
    }
}