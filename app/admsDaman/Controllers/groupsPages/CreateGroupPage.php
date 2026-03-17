<?php

namespace App\admsDaman\Controllers\groupsPages;

use App\admsDaman\Controllers\Services\Validation\ValidationGroupService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\GroupsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para criação de Grupo
 *
 * Esta classe é responsável pelo processo de criação de novos Grupos. Ela lida com a recepção dos dados do
 * formulário, validação dos mesmos, e criação do Grupo no sistema. Além disso, é responsável por carregar
 * a visualização apropriada com mensagens de sucesso ou erro.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\group
 */
class CreateGroupPage
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Método principal que gerencia a criação do Grupo.
     *
     * Este método é chamado para processar a criação de um novo Grupo. Ele verifica a validade do token CSRF,
     * valida os dados do formulário e, se tudo estiver correto, cria o Grupo. Caso contrário, carrega a
     * visualização de criação de Grupo com mensagens de erro.
     * 
     * @return void
     */
    public function index()
    {
        // Receber os dados do formulário de cadastro de Grupo
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_create_group', $this->data['form']['csrf_token'])) {

            // Chamar método cadastrar passando pelas validações necessárias
            $this->addGroup();
        } else {
            // Chamar o método carregar a view
            $this->viewGroup();
        }
    }

    /**
     * Carregar a visualização de criação de Grupo.
     * 
     * Este método configura os dados necessários e carrega a view para a criação de um novo Grupo.
     * 
     * @return void
     */
    private function viewGroup(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Cadastrar Grupo";

        // Ativar o item de Menu
        $this->data['menu'] = "list-groups-pages";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/groupsPages/create", $this->data);
        $loadView->loadView();
    }

    /**
     * Adicionar um novo grupo ao sistema.
     * 
     * Este método valida os dados do formulário usando a classe de validação `ValidationUserRakitService` e,
     * se não houver erros, cria o grupo no banco de dados usando o `UsersRepository`. Caso contrário, ele
     * recarrega a visualização de criação com mensagens de erro.
     * 
     * @return void
     */
    private function addGroup(): void
    {
        // Instaciar a classe que valida os dados do formulário com Rakit
        $validationGroup = new ValidationGroupService();
        $this->data['errors'] = $validationGroup->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewGroup();

            return;
        }

        // Instanciar o Repository para cadastrar o Grupo
        $groupCreate = new GroupsRepository();
        $result = $groupCreate->createGroup($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Grupo cadastrado com sucesso!";

            // Redirecionar o usuário para a página de visualizar o Grupo recem criado
            header("Location: {$_ENV['URL_ADM']}view-group-page/$result");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            $this->data['errors'][] = "Grupo não cadastrado!";

            // Chamar o método carregar a view
            $this->viewGroup();
        }
    }
}
