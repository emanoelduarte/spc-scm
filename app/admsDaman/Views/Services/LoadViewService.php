<?php

namespace App\admsDaman\Views\Services;

class LoadViewService 
{
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
        if (file_exists('./app/' . $this->nameView . '.php')) {
            include './app/' . $this->nameView . '.php';

        }else {
            die("Erro 005: Tente novamente, caso o erro persista entre em contato com o administrador Emanoel Duarte {$_ENV['EMAIL_ADM']}");
        }

    }
}