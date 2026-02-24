<?php

namespace Routes;

use App\adms\Helpers\GenereteLog;
use App\admsDaman\Helpers\GenerateLog;

class LoadPageAdm
{

    /**
     * @var string $urlController Recebe da URL o nome da controller  */
    private string $urlController;
    /** @var string $urlParameter Recebe da URL o nome do parametro */
    private string $urlParameter;

    /** @var string $classLoad Controller que deve ser carregada */
    private string $classLoad;

    /** @var array $listPgPublic Recebe a lista de páginas públicas */
    private array $listPgPublic = ["Login", "Error403"];
    /** @var array $listPgPrivate Recebe a lista de páginas privadas */
    private array $listPgPrivate = ["Dashboard", "ListUsers", "ViewUser", "CreateUser"];

    /** @var array $listDirectory Recebe a lista de diretórios com as controllers */
    private array $listDirectory = ["login", "dashboard", "users", "errors"];
    /** @var array $listPackages Recebe a lista de pacotes com as controllers */
    private array $listPackages = ["admsDaman"];


    /**
     * Verifica se existe a página com método checkPageExists
     * Verifica se existe a classe com método checkControllerExists
     * @param string $urlControler Recebe a URL o nome da controller
     * @param string $urlParameter Recebe a URL o parâmetro (o que vem depois do nome da página/controller)
     * 
     * @return void
     */
    public function loadPageAdm(string|null $urlController, string|null $urlParameter): void
    {
        $this->urlController = $urlController;
        $this->urlParameter = $urlParameter;

        // Verifica se existe a página
        if (!$this->checkPageExists()) {
            // Chama método para salvar o log em caso de erro
            GenerateLog::generateLog("error", "Página não encontrada.", ['pagina' => $this->urlController, 'parametro' => $this->urlParameter]);
            die("Erro 002: Tente novamente, caso o erro persista entre em contato com o administrador Emanoel Duarte {$_ENV['EMAIL_ADM']}");
        }

        // Verificar se a classe existe
        if (!$this->checkControllersExists()) {
            // Chama método para salvar o log em caso de erro
            GenerateLog::generateLog("error", "Controller não encontrada.", ['pagina' => $this->urlController, 'parametro' => $this->urlParameter]);
            die("Erro 003: Tente novamente, caso o erro persista entre em contato com o administrador Emanoel Duarte {$_ENV['EMAIL_ADM']}");
        }
    }
    /**
     * Verificar se a página existe no array de páginas publicas ou privadas
     * 
     * @return bool
     */
    private function checkPageExists(): bool
    {

        // Verificar se existe a página no array de páginas públicas
        if (in_array($this->urlController, $this->listPgPublic)) {
            return true;
        }

        // Verificar se existe a página no array de páginas públicas
        if (in_array($this->urlController, $this->listPgPrivate)) {
            return true;
        }
        return false;
    }

    /**
     * Verificar se existe a controller/página 
     * Chamar o métrodo para verificar se existe o método dentro da controller
     * 
     * @return boll
     */
    private function checkControllersExists(): bool
    {
        // Percorrer o array de pacotes
        foreach ($this->listPackages as $package) {
            // percorrer o array de diretórios 
            foreach ($this->listDirectory as $directory) {

                //Criar o caminho da controller/classe
                $this->classLoad = "\\App\\$package\\Controllers\\$directory\\" . $this->urlController;

                if (class_exists($this->classLoad)) {

                    // Chamar o método para validar o carregamento da página
                    $this->loadMetodo();

                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Verificar se existe o método e carregar a página/controller
     * 
     * @return void
     */
    private function loadMetodo(): void
    {
        // Instancia a classe da página que deve ser carregada
        $classLoad = new $this->classLoad();

        if (method_exists($classLoad, "index")) {
            // Carrega o método da classe passando o parametro caso haja
            $classLoad->{"index"}($this->urlParameter);
        } else {
            // Chama método para salvar o log em caso de erro
            GenerateLog::generateLog("error", "Método não encontrado.", ['pagina' => $this->urlController, 'parametro' => $this->urlParameter]);
            die("Erro 004: Tente novamente, caso o erro persista entre em contato com o administrador Emanoel Duarte {$_ENV['EMAIL_ADM']}");
        }
    }
}