<?php

namespace Routes;

use App\admsDaman\Helpers\GenerateLog;

/* Classe LoadPageAdm
 * 
 *  @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * 
 * Esta classe é responsável por carregar a página de administração solicitada, verificando se a página e a controller existem, 
 * e se o método necessário está presente na controller. Ela também registra logs de erros ou acessos bem-sucedidos.
 * 
 */
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
    private array $listPgPublic = ["Login", "Error403", "NewUser", "Logout", "ForgotPassword", "ResetPassword", "RecoverPassword"];
    /** @var array $listPgPrivate Recebe a lista de páginas privadas */
    private array $listPgPrivate = ["Dashboard", "ListUsers", "ViewUser", "CreateUser", "UpdateUser", "DeleteUser", "UpdatePasswordUser", "ListAccessLevels", "ViewAccessLevel", "UpdateAccessLevel", "CreateAccessLevel", "DeleteAccessLevel", "UpdateUserAccessLevels", "AccessLevelPageSync", "ListPackages", "CreatePackage", "ViewPackage", "UpdatePackage", "DeletePackage", "ListGroupsPages", "ViewGroupPage", "CreateGroupPage", "UpdateGroupPage", "DeleteGroupPage", "ListPages", "ViewPage", "CreatePage", "UpdatePage", "DeletePage", "ListOrders", "ViewOrder", "CreateOrder", "UpdateOrder", "UpdateRentalOrder", "DeleteOrder", "ListProjects", "ViewProject", "CreateProject", "UpdateProject", "DeleteProject", "ListCategories", "ViewCategory", "DeleteItem", "CreateCategory", "UpdateCategory", "DeleteCategory"];

    /** @var array $listDirectory Recebe a lista de diretórios com as controllers */
    private array $listDirectory = ["login", "dashboard", "users", "errors", "accessLevels", "orders", "packages", "groupsPages", "pages","projects", "categories"];
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

            // Criar mensagem de erro
            $_SESSION['error'] = "Necessário está logado para acessar uma página restrita.";

            //Redirecionar o usuario para a pagina de login
            header("Location: {$_ENV['URL_ADM']}login");
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

        // Chamar o método para verificar se existe a página no array de página privada
        if ($this->checkPagePrivateExists()) {
            return true;
        }

        return false;
    }

    private function checkPagePrivateExists(): bool
    {
        // Veririficar se a página existe no array de páginas privadas
        if (!in_array($this->urlController, $this->listPgPrivate)) {
        return false;
        }

        // Verifico se o usuário está logado
        if ((!isset($_SESSION['user_id'])) || (!isset($_SESSION['user_name'])) || (!isset($_SESSION['user_email']))) {

            // Chama método para salvar o log em caso de erro
            GenerateLog::generateLog("error", "Usuário tentou acessar página privada sem uma sessão iniciada.", ['pagina' => $this->urlController, 'parametro' => $this->urlParameter]);

            return false;
        }

        return true;
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
            // Chama método para salvar o log em caso de sucesso
            GenerateLog::generateLog("info", "Página acessada com sucesso.", ['pagina' => $this->urlController, 'parametro' => $this->urlParameter]);

            // Carrega o método da classe passando o parametro caso haja
            $classLoad->{"index"}($this->urlParameter);
        } else {

            // Chama método para salvar o log em caso de erro
            GenerateLog::generateLog("error", "Método não encontrado.", ['pagina' => $this->urlController, 'parametro' => $this->urlParameter]);

            die("Erro 004: Tente novamente, caso o erro persista entre em contato com o administrador Emanoel Duarte {$_ENV['EMAIL_ADM']}");
        }
    }
}