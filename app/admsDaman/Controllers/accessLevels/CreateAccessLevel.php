<?php

namespace App\admsDaman\Controllers\accessLevels;

use App\admsDaman\Controllers\Services\Validation\ValidationAccessLevelService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\AccessLevelsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para criação de Nível de Acesso
 *
 * Esta classe é responsável pelo processo de criação de novos Nível de Acesso. Ela lida com a recepção dos dados do
 * formulário, validação dos mesmos, e criação do Nível de Acesso no sistema. Além disso, é responsável por carregar
 * a visualização apropriada com mensagens de sucesso ou erro.
 * 
 * @author Cesar <emanoel.c.duarte@hotmail.com>
 * @package App\adms\Controllers\users
 */
class CreateAccessLevel
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Método principal que gerencia a criação do Nível de Acesso.
     *
     * Este método é chamado para processar a criação de um novo Nível de Acesso. Ele verifica a validade do token CSRF,
     * valida os dados do formulário e, se tudo estiver correto, cria o Nível de Acesso. Caso contrário, carrega a
     * visualização de criação de Nível de Acesso com mensagens de erro.
     * 
     * @return void
     */
    public function index(): void
    {
        // Receber os dados do formulário de cadastro de usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o if se existir o CSRF e for válido
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_create_level', $this->data['form']['csrf_token'])) {
            // Chamar método cadastrar passando pelas validações necessárias
            $this->addAccessLevel();
        }else {
            // Chamar o método para carregar a view de criação de nível de acesso
            $this->viewCreateAccessLevel();
        }
    }

    /**
     * Carregar a visualização de criação de usuário.
     * 
     * Este método configura os dados necessários e carrega a view para a criação de um novo usuário.
     * 
     * @return void
     */
    private function viewCreateAccessLevel(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Cadastrar Nível de Acesso";

        $this->data['menu'] = "list-access-levels";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/accessLevels/create", $this->data);
        $loadView->loadView();
    }

    public function addAccessLevel()
    {
        // Instanciar a classe que valida os dados do formulário com Rakit
        $validationAccessLevel = new ValidationAccessLevelService();
        $this->data['errors'] = $validationAccessLevel->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewCreateAccessLevel();

            return;
        }

        // Instanciar o Repository para cadastrar o Nível de acesso
        $levelAccessCreate = new AccessLevelsRepository();
        $result = $levelAccessCreate->createAccessLevel($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Nivel de acesso Criado com sucesso!";

            // Redirecionar o Nivel de acesso para a página de listar Nivel de acesso
            header("Location: {$_ENV['URL_ADM']}view-access-level/$result");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            // $_SESSION['error'] = "Nivel de acesso não cadastrado!";
            $this->data['errors'][] = "Nivel de acesso não cadastrado!";

            // Chamar o método carregar a view
            $this->viewCreateAccessLevel();
        }
    }
}