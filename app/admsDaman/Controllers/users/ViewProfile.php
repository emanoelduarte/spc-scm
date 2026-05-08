<?php

namespace App\admsDaman\Controllers\users;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationUserPasswordService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;
use App\admsDaman\Models\Repository\UsersRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ViewProfile
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /**
     * Recuperar os ultimos usuários
     * 
     * @return void
     */
    public function index(int|string $id): void
    {

        // Receber os dados do formulário de cadastro de usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_edit_password_user', $this->data['form']['csrf_token'])) {

            // Chamar o método Editar a senha do usuário usuário
            $this->editPasswordUser();
        } else {
            // Instanciar o Repository para recuperar o registro do banco de dados
            $viewUser = new UsersRepository();
            $this->data['form'] = $viewUser->getUser((int) $id);

            // Verificar se encontrou o registro no banco de dados
            if (!$this->data['form']) {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Usuário não encontrado", ['id' => (int) $id]);

                // Criar a mensagem de erro
                $_SESSION['error'] = "Usuário não encontrado! 2";

                // Redirecionar o usuário para a página listar
                header("Location: {$_ENV['URL_ADM']}view-profile/{$id}");

                return;
            }

            // Chamar o método carregar a view
            $this->viewUpdatePasswordUser();
        }
    }

    // Metodo responsável em carregar a VIEW
    private function viewUpdatePasswordUser(): void
    {

        $pageElements = [
            'title_head' => "Editar Senha do Usuário",
            'menu' => "list-users",
            'buttonPermissions' => [],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/users/viewProfile", $this->data);
        $loadView->loadView();
    }

    /**
     * Editar Senha do usuário
     * 
     * Este método realiza a edição da senha do usuário existente no sistema. Ele valida os dados do formulário usando a
     * Classe `ValidationUserPasswordService`, exibe a view com os erros caso existam campos com dados incorretos,
     * Chama o repositório para atualizar o usuário e, depedendo do resultado, redireciona o usuário ou exibe
     * uma mensagem de erro
     * 
     * @return void
     */
    public function editPasswordUser(): void
    {
        // Instanciar a classe validar os dados do formulário com Rakit
        $validationUser = new ValidationUserPasswordService();
        $this->data['errors'] = $validationUser->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados incorretos
        if (!empty($this->data['errors'])) {

            // Chama o método carregar a view
            $this->viewUpdatePasswordUser();

            return;
        }

        // Instanciar o UsersRepository para chamar o método que faz a edição do usuário
        $userPasswordUpdateProfile = new UsersRepository();
        $result = $userPasswordUpdateProfile->updatePasswordUser($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar
            $_SESSION['success'] = "Senha editado com sucesso!";

            // Redirecionar o usuário para a página de visualizar usuário
            header("Location: {$_ENV['URL_ADM']}view-profile/{$this->data['form']['id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar
            $this->data['errors'][] = "Senha não editada!";

            // Chamar o método carregar a view
            $this->viewUpdatePasswordUser();
        }
    }
}
