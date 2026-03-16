<?php

namespace App\admsDaman\Controllers\accessLevels;

use App\admsDaman\Controllers\Services\Validation\ValidationUserAccessLevelService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;

class UpdateUserAccessLevels
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    public function index(): void
    {
        // Receber os dados do formulário de permissões do usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_update_access_level', $this->data['form']['csrf_token'])) {

            // Editar nível de acesso do usuário
            $this->editUserAccessLevel();
        } else {
            // Chama a View para apresentar o conteúdo normalmente
            $this->viewUserAccessLevel();
        }
    }

    private function viewUserAccessLevel(): void
    {

        // Chamar o método para salvar o log
        GenerateLog::generateLog("error", "Nível de Acesso do usuário não editado.", ['id' => (int) $this->data['form']['adms_daman_user_id']]);

        // Criar a mensagem de erro
        $_SESSION['error'] = "Nível de Acesso do usuário não editado!";

        // Redirecionar o usuário para a página listar
        header("Location: {$_ENV['URL_ADM']}view-user/{$this->data['form']['adms_daman_user_id']}");

        return;
    }

    private function editUserAccessLevel(): void
    {
        // Instanciar a classe que valida os dados do formulário com Rakit
        $validationUserAccessLevel = new ValidationUserAccessLevelService();
        $_SESSION['errors'] = $validationUserAccessLevel->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados inclorretos
        if (!empty($_SESSION['errors'])) {

            // Chama o método carregar a view
            $this->viewUserAccessLevel();

            return;
        }

        // Instanciar o UsersRepository para chamar o método que faz a edição do Nível de acesso
        $userAccessLevelUpdate = new UsersAccessLevelsRepository();
        $result = $userAccessLevelUpdate->updateUserAccessLevel($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar o nível de acesso do usuário
            $_SESSION['success'] = "Nível de acesso do usuário editado com sucesso!";

            // Redirecionar o Nível de acesso para a página de visualizar Nível de acesso
            header("Location: {$_ENV['URL_ADM']}view-user/{$this->data['form']['adms_daman_user_id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar o nível de acesso do usuário
            $this->data['errors'][] = "Nível de acesso do usuário não editado!";

            // Chamar o método carregar a view
            $this->viewUserAccessLevel();
        }
    }
}
?>