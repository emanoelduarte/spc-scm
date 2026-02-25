<?php

namespace App\admsDaman\Controllers\login;

use App\admsDaman\Controllers\Services\Validation\ValidationUserPasswordService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\ResetPasswordRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ResetPassword
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    public function index(string|null $recoverPassword): void
    {
        // Receber os dados do formulário de cadastro de usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // receber o código para recuperar a senha $recoverPassword
        $this->data['form']['recover_password'] = (string) $recoverPassword;

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_reset_password', $this->data['form']['csrf_token'])) {

            // Chamar método recoverPassord (esqueceu a senha) passando pelas validações necessárias
            $this->resetPassword();
        } else {
            // Chamar o método carregar a view atualizar senha
            $this->viewResetPassword();
        }
    }

    private function viewResetPassword(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Nova Senha";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/login/resetPassword", $this->data);
        $loadView->loadView();
    }

    private function resetPassword(): void
    {
        // Instaciar a classe que valida os dados do formulário com Rakit
        $validationUser = new ValidationUserPasswordService();
        $this->data['errors'] = $validationUser->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            GenerateLog::generateLog("error", "Erro na passagem dos dados", ['error' => (array) $this->data['errors']]);
            $this->viewResetPassword();
            return;
        }

        // Instanciar o Repository para Buscar o Usuário
        $viewUser = new ResetPasswordRepository();
        $this->data['user'] = $viewUser->getUser((string) $this->data['form']['email']);

        // Verificar se foi encontrado no banco de dados e retornou
        if (!$this->data['user']) {
            // Gerrar o erro e redirecionar, caso negativo
            GenerateLog::generateLog("error", "Usuário não encontrado", ['email' => (string) $this->data['form']['email']]);

            // Redirecionar o usuário para a página de recuperar usuárip
            $_SESSION['error'] = "Usuário não encontrado";

            $this->viewResetPassword();
            return;
        }

        // Verificar se o código recupearar a senha é válido
        if (($this->data['form']['recover_password'] ?? false) and ($this->data['user']['recover_password'] ?? false) and (!password_verify($this->data['form']['recover_password'], $this->data['user']['recover_password']))) {
            // Gerrar o erro e redirecionar, caso negativo
            GenerateLog::generateLog("error", "Código de recuperação inválido", ['email' => (string) $this->data['form']['email']]);

            // Redirecionar o usuário para a página de recuperar usuárip
            $_SESSION['error'] = "Código de recuperação senha inválido, ou expirado";

            $this->viewResetPassword();
            return;
        }

        // Verificar se a data de validade da chave é menor ou igual a data atual
        if ($this->data['user']['validate_recover_password'] < date('Y-m-d H:i:s')) {
            // Gerrar o erro e redirecionar, caso negativo
            GenerateLog::generateLog("error", "Código de recuperação inválido", ['email' => (string) $this->data['form']['email']]);

            // Redirecionar o usuário para a página de recuperar usuárip
            $_SESSION['error'] = "Código de recuperação senha inválido, ou expirado!";

            $this->viewResetPassword();
            return;
        }

        // Instaciar o repositório para editar o recover_password no banco de dados
        $userUpdate = new ResetPasswordRepository();
        $result = $userUpdate->updatePasswordUser($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar
            $_SESSION['success'] = "Senha editada com sucesso!";

            // Redirecionar o usuário para a página de login
            header("Location: {$_ENV['URL_ADM']}login");

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar
            $this->data['errors'][] = "Senha não editada";

            // Chamar o método carregar a view
            $this->viewResetPassword();
        }
    }
}