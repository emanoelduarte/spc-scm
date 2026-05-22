<?php

namespace App\admsDaman\Controllers\users;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationUserRakitService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\AccessLevelsRepository;
use App\admsDaman\Models\Repository\AddressesRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\UsersRepository;
use App\admsDaman\Views\Services\LoadViewService;

class CreateUser
{
    /** @var array|null $dadosForm Recebe os dados do Formulário */
    private array|null $dataForm;

    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    public function index()
    {
        // Receber os dados do formulário de cadastro de usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_create_user', $this->data['form']['csrf_token'])) {

            $this->addUser();
        } else {
            $this->viewCreateUser();
        }
    }

    private function addUser(): void
    {
        // Instaciar a classe que valida os dados do formulário se há algum campo vazio
        $validationUser = new ValidationUserRakitService();
        $this->data['errors'] = $validationUser->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewCreateUser();

            return;
        }

        // Instanciar o Repository para cadastrar o Usuário
        $userCreate = new UsersRepository();
        $userId = $userCreate->createUser($this->data['form']);

        // Salvar endereço vinculado ao usuário Quando necessário
        // $addressRepository = new AddressesRepository();
        // $userAddress = $addressRepository->createAddress($this->data['form'], $userId, 'user');

        // Acesso o IF se o repository retornou true
        if ($userId) {
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Usuário cadastrado com sucesso!";

            // Redirecionar o usuário para a página de visualizar usuário
            header("Location: {$_ENV['URL_ADM']}view-user/$userId");

            return;
        } else {
            // Criar a mensagem de sucesso ao logar
            // $_SESSION['error'] = "Usuário não cadastrado!";
            $this->data['errors'][] = "Usuário não cadastrado!";
        }
    }

    public function viewCreateUser()
    {

        // Instanciar o repositório para preencher os selects.
        $getAllProjectsSelectActive = new ProjectsRepository();
        $this->data['getAllProjectsSelectActive'] = $getAllProjectsSelectActive->getAllProjectsSelectActive();

        // Instanciar o repositório para preencher os selects.
        $getAllAccessLevels = new AccessLevelsRepository();
        $this->data['getAllAccessLevels'] = $getAllAccessLevels->getAllAccessLevelsSelect();

        $pageElements = [
            'title_head' => "Cadastrar Usuário",
            'menu' => "list-users",
            'buttonPermissions' => ["ListUsers"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/users/create", $this->data);
        $loadView->loadView();
    }
}
