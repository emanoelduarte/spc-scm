<?php

namespace App\admsDaman\Controllers\projects;

use App\admsDaman\Controllers\Services\Validation\ValidationUserProjectAssociateService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\ProjectsRepository;

class UpdateUserProjectAssociate
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    public function index(): void
    {
        // Receber os dados do formulário de permissões do usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_update_project_associate', $this->data['form']['csrf_token'])) {

            // Editar nível de acesso do usuário
            $this->editUserProjectAssociate();
        } else {
            // Chama a View para apresentar o conteúdo normalmente
            $this->viewUserProjectAssociate();
        }
    }

    private function viewUserProjectAssociate(): void
    {

        // Chamar o método para salvar o log
        GenerateLog::generateLog("error", "Obras vinculadas do usuário não editadas.", ['id' => (int) $this->data['form']['adms_daman_user_id']]);

        // Criar a mensagem de erro
        $_SESSION['error'] = "Obras vinculadas do usuário não editadas!";

        // Redirecionar o usuário para a página listar
        header("Location: {$_ENV['URL_ADM']}view-user/{$this->data['form']['adms_daman_user_id']}");

        return;
    }

    private function editUserProjectAssociate(): void
    {
        // Instanciar a classe que valida os dados do formulário com Rakit
        $validationUserAccessLevel = new ValidationUserProjectAssociateService();
        $_SESSION['errors'] = $validationUserAccessLevel->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados inclorretos
        if (!empty($_SESSION['errors'])) {

            // Chama o método carregar a view
            $this->viewUserProjectAssociate();

            return;
        }

        // Instanciar o UsersRepository para chamar o método que faz a edição das obra vinculadas
        $userProjectsAssociateUpdate = new ProjectsRepository();
        $result = $userProjectsAssociateUpdate->updateUserProjectsAssociate($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar a obra vinculada do usuário
            $_SESSION['success'] = "Obras vinculadas do usuário editadas com sucesso!";

            // Redirecionar a obra vinculada para a página de visualizar Nível de acesso
            header("Location: {$_ENV['URL_ADM']}view-user/{$this->data['form']['adms_daman_user_id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar a obra vinculada do usuário
            $this->data['errors'][] = "Obras vinculadas do usuário não editadas!";

            // Chamar o método carregar a view
            $this->viewUserProjectAssociate();
        }
    }
}
