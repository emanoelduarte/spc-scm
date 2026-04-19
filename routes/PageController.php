<?php

namespace Routes;

use App\admsDaman\Helpers\ClearUrl;
use App\admsDaman\Helpers\SlugController;

class PageController 
{
    /** @var string $url Receber a URL do .htacess */
    private string $url;
    
    /** @var array $urlArray recebe a URL convertida para array */
    private array $urlArray;

    /** @var string Recebe da URL o nome da controller */
    private string $urlController = "";

    /** @var string $urlParametro Recebe da URL o nome do parametro  */
    private string $urlParameter = "";

    /** 
     * Recebe e chama métodos para tratar a url e controlar as páginas
     */
    public function __construct()
    {

        // Verificar se tem o valor na váriável URL enviada pelo .htaccess
        if(!empty(filter_input(INPUT_GET, 'url', FILTER_DEFAULT))) {
            $this->url = filter_input(INPUT_GET, 'url', FILTER_DEFAULT);

            // Chamar a classe helper para limpar a URL
            $this->url = ClearUrl::clearUrl($this->url);

            // Converter a string da URL para um array de palavras
            $this->urlArray = explode("/", $this->url);

            // Verificar se existe a controller na URL posição (zero) do array
            if (isset($this->urlArray[0])) {
                // Chamar a classe helper para converter a controller enviada na URL para o formato da classe
                $this->urlController = SlugController::slugController($this->urlArray[0]);

                // $this->urlController = $this->urlArray[0];
            } else {
                $this->urlController = SlugController::slugController("Login");
            }

            // Verificar se existe a parametro na URL será a posicao (um) do array
            if (isset($this->urlArray[1])) {
                $this->urlParameter = $this->urlArray[1];
            }

        }else {
             $this->urlController = SlugController::slugController("Login");
        }
    }

    /**
     * Este método instancia a classe `LoadPageAdm`, responsável por validar e carregar a página correspondente.
     * Ele passa o nome da controller e o parâmetro extraído da URL para o método `loadPageAdm` da classe `LoadPageAdm`.
     * 
     * @return void
     */
    public function loadPage(): void
    {
        // Instanciar a classe para validar e carregar a página/controller
        // Carregar sem nível de acesso
        // $loadPageAdm = new LoadPageAdm();

        //Carregar com nível de acesso e verificar no banco de dados

        $loadPageAdm = new LoadPageAdmAccessLevel();

        // Chamar o método e enviar como parametro a controller e o parametro da URL
        $loadPageAdm->loadPageAdm($this->urlController, $this->urlParameter);
    }
}