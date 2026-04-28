<?php

namespace App\admsDaman\Controllers\packages;

use App\admsDaman\Controllers\Services\Validation\ValidationPackageService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\PackagesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para criação de Pacote
 *
 * Esta classe é responsável pelo processo de criação de novos pacotes. Ela lida com a recepção dos dados do
 * formulário, validação dos mesmos, e criação do Pacote no sistema. Além disso, é responsável por carregar
 * a visualização apropriada com mensagens de sucesso ou erro.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\packages
 */
class CreatePackage
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Método principal que gerencia a criação do pacote.
     *
     * Este método é chamado para processar a criação de um novo pacote. Ele verifica a validade do token CSRF,
     * valida os dados do formulário e, se tudo estiver correto, cria o pacote. Caso contrário, carrega a
     * visualização de criação de pacote com mensagens de erro.
     * 
     * @return void
     */
    public function index()
    {
        // Receber os dados do formulário de cadastro de usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_create_package', $this->data['form']['csrf_token'])) {

            // Chamar método cadastrar passando pelas validações necessárias
            $this->addPackage();
        } else {
            // Chamar o método carregar a view
            $this->viewPackage();
        }
    }

    /**
     * Carregar a visualização de criação de Pacote.
     * 
     * Este método configura os dados necessários e carrega a view para a criação de um novo Pacote.
     * 
     * @return void
     */
    private function viewPackage(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Cadastrar Pacote";

        // Ativar o item de Menu
        $this->data['menu'] = "list-packages";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/packages/create", $this->data);
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
    private function addPackage(): void
    {
        // Instaciar a classe que valida os dados do formulário com Rakit
        $validationPackage = new ValidationPackageService();
        $this->data['errors'] = $validationPackage->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewPackage();

            return;
        }

        // Instanciar o Repository para cadastrar o Pacote
        $userCreate = new PackagesRepository();
        $result = $userCreate->createPackage($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Pacote cadastrado com sucesso!";

            // Redirecionar o usuário para a página de visualizar o pacote recem criado
            header("Location: {$_ENV['URL_ADM']}view-package/$result");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            // $_SESSION['error'] = "Usuário não cadastrado!";
            $this->data['errors'][] = "Pacote não cadastrado!";

            // Chamar o método carregar a view
            $this->viewPackage();
        }
    }
}
?>