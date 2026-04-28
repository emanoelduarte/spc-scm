<?php

namespace App\admsDaman\Controllers\projects;

use App\admsDaman\Controllers\Services\Validation\ValidationProjectService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Views\Services\LoadViewService;

class CreateProject
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Método principal que gerencia a criação da Obra.
     *
     * Este método é chamado para processar a criação de um nova Obra. Ele verifica a validade do token CSRF,
     * valida os dados do formulário e, se tudo estiver correto, cria a Obra. Caso contrário, carrega a
     * visualização de criação de obra com mensagens de erro.
     * 
     * @return void
     */
    public function index()
    {
        // Receber os dados do formulário de cadastro de obra
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_create_obra', $this->data['form']['csrf_token'])) {

            // Chamar método cadastrar passando pelas validações necessárias
            $this->addProject();
        } else {
            // Chamar o método carregar a view
            $this->viewProject();
        }
    }

    /**
     * Carregar a visualização de criação da Obra.
     * 
     * Este método configura os dados necessários e carrega a view para a criação de uma nova Obra.
     * 
     * @return void
     */
    private function viewProject(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Cadastrar Obra";

        // Ativar o item de Menu
        $this->data['menu'] = "list-projects";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/projects/create", $this->data);
        $loadView->loadView();
    }

    /**
     * Adicionar um novo usuário ao sistema.
     * 
     * Este método valida os dados do formulário usando a classe de validação `ValidationUserRakitService` e,
     * se não houver erros, cria o usuário no banco de dados usando o `UsersRepository`. Caso contrário, ele
     * recarrega a visualização de criação com mensagens de erro.
     * 
     * @return void
     */
    private function addProject(): void
    {
        // Instaciar a classe que valida os dados do formulário com Rakit
        $validationPackage = new ValidationProjectService();
        $this->data['errors'] = $validationPackage->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewProject();

            return;
        }

        // Instanciar o Repository para cadastrar a Obra
        $projectCreate = new ProjectsRepository();
        $result = $projectCreate->createProject($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Obra cadastrada com sucesso!";

            // Redirecionar o usuário para a página de visualizar o Obra recem criado
            header("Location: {$_ENV['URL_ADM']}view-project/$result");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            $this->data['errors'][] = "Obra não cadastrada!";

            // Chamar o método carregar a view
            $this->viewProject();
        }
    }
}