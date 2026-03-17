<?php

namespace App\admsDaman\Controllers\pages;

use App\admsDaman\Controllers\Services\Validation\ValidationPageService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\GroupsRepository;
use App\admsDaman\Models\Repository\PackagesRepository;
use App\admsDaman\Models\Repository\PagesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para criação de página
 *
 * Esta classe é responsável pelo processo de criação de novas Pages. Ela lida com a recepção dos dados do
 * formulário, validação dos mesmos, e criação da página no sistema. Além disso, é responsável por carregar
 * a visualização apropriada com mensagens de sucesso ou erro.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\pages
 */
class CreatePage
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Método principal que gerencia a criação da página.
     *
     * Este método é chamado para processar a criação de uma nova página. Ele verifica a validade do token CSRF,
     * valida os dados do formulário e, se tudo estiver correto, cria a página. Caso contrário, carrega a
     * visualização de criação de página com mensagens de erro.
     * 
     * @return void
     */
    public function index()
    {
        // Receber os dados do formulário de cadastro de página
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_create_page', $this->data['form']['csrf_token'])) {

            // Chamar método cadastrar passando pelas validações necessárias
            $this->addPage();
        } else {
            // Chamar o método carregar a view
            $this->viewPage();
        }
    }

    /**
     * Carregar a visualização de criação de Grupo.
     * 
     * Este método configura os dados necessários e carrega a view para a criação de um nova página.
     * 
     * @return void
     */
    private function viewPage(): void
    {
        // Intanciar o repositório para recuperar os pacotes do banco para preencher o select
        $listPackagesPages = new PackagesRepository();
        $this->data['listPackagesPages'] = $listPackagesPages->getAllPackagesSelect();

        // Intanciar o repositório para recuperar os grupos do banco para preencher o select
        $getAllGroupsPagesSelect = new GroupsRepository();
        $this->data['getAllGroupsPagesSelect'] = $getAllGroupsPagesSelect->getAllGroupsPagesSelect();

        // Criar o título da página
        $this->data['title_head'] = "Cadastrar Página";

        // Ativar o item de Menu
        $this->data['menu'] = "list-pages";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/pages/create", $this->data);
        $loadView->loadView();
    }

    /**
     * Adicionar uma nova página ao sistema.
     * 
     * Este método valida os dados do formulário usando a classe de validação `ValidationUserRakitService` e,
     * se não houver erros, cria a página no banco de dados usando o `UsersRepository`. Caso contrário, ele
     * recarrega a visualização de criação com mensagens de erro.
     * 
     * @return void
     */
    private function addPage(): void
    {
        // Instaciar a classe que valida os dados do formulário com Rakit
        $validationPage = new ValidationPageService();
        $this->data['errors'] = $validationPage->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewPage();

            return;
        }

        // Instanciar o Repository para cadastrar a página
        $pageCreate = new PagesRepository();
        $result = $pageCreate->createPage($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Página cadastrada com sucesso!";

            // Redirecionar o usuário para a página de visualizar a Página recem criado
            header("Location: {$_ENV['URL_ADM']}view-page/$result");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            $this->data['errors'][] = "Página não cadastrada!";

            // Chamar o método carregar a view
            $this->viewPage();
        }
    }
}
