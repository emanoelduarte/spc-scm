<?php

namespace App\admsDaman\Controllers\login;

use App\admsDaman\Controllers\Services\GenerateKeyService;
use App\admsDaman\Controllers\Services\Validation\ValidationEmailService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\ResetPasswordRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller responsável pela recuperação de senha.
 *
 * Esta classe gerencia o processo de recuperação de senha para usuários esquecidos.
 * Ela lida com a validação do e-mail, geração de chaves para redefinição de senha,
 * e envio de instruções por e-mail. Também carrega as visualizações apropriadas com
 * mensagens de erro ou sucesso.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\adms\Controllers\login
 */
class ForgotPassword
{

    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Método principal da classe, responsável por iniciar o processo de recuperação de senha.
     *
     * Verifica se o formulário foi submetido e se o token CSRF é válido. Se o token for válido, 
     * chama o método para processar a recuperação de senha; caso contrário, carrega a visualização do formulário.
     * 
     * @return void
     */
    public function index(): void
    {

        // Receber os dados do formulário de cadastro de usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_forgot_password', $this->data['form']['csrf_token'])) {

            // Chamar método recoverPassord (esqueceu a senha) passando pelas validações necessárias
            $this->forgotPassword();
        } else {
            // Chamar o método carregar a view esqueceu a senha
            $this->viewForgotPassword();
        }
    }

     /**
     * Carrega a visualização para a página de recuperação de senha.
     *
     * Configura os dados necessários, como o título da página, e carrega a view correspondente.
     * 
     * @return void
     */
    private function viewForgotPassword(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Recuperar Senha";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/login/forgotPassword", $this->data);
        $loadView->loadView();
    }

    /**
     * Processa a solicitação de recuperação de senha.
     *
     * Valida o e-mail fornecido, verifica se o usuário existe no banco de dados, 
     * e, se válido, gera uma chave de recuperação e envia um e-mail com as instruções.
     * Em caso de sucesso, redireciona o usuário para a página de login; caso contrário, 
     * recarrega a visualização com mensagens de erro.
     * 
     * @return void
     */
    private function forgotPassword(): void
    {
        // Instaciar a classe que valida os dados do formulário com Rakit
        $validationUser = new ValidationEmailService();
        $this->data['errors'] = $validationUser->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            GenerateLog::generateLog("error", "Erro na passagem dos dados", ['error' => (array) $this->data['errors']]);

            $this->viewForgotPassword();
            return;
        }

        // Instanciar o Repository para buscar o usuário
        $viewUser = new ResetPasswordRepository();
        $this->data['user'] = $viewUser->getUser((string) $this->data['form']['email']);

        // Verificar se foi encontrado no banco de dados e retornou
        if (!$this->data['user']) {
            // Gerrar o erro e redirecionar, caso negativo
            GenerateLog::generateLog("error", "Usuário não encontrado", ['email' => (string) $this->data['form']['email']]);

            // Redirecionar o usuário para a página de recuperar usuárip
            $_SESSION['error'] = "E-mail de recuperação não enviado, tente novamente ou entre em contato com o e-mail {$_ENV['EMAIL_ADM']}";
            header("Location: {$_ENV['URL_ADM']}forgot-password");
            $this->viewForgotPassword();
            return;
        }

         // Instanciar o serviço para gerar a chave
        // $valueGenerateKey = GenerateKeyService::generateKey();

        // Instanciar o serviço para recuperar a senha
        $recoverPassword = new RecoverPassword();
        $resultrecoverPassword = $recoverPassword->recoverPassword($this->data);

        

        // Verificar se enviou o e-mail com sucesso
        if (!$resultrecoverPassword) {

        // $this->data['form']['key'] = $valueGenerateKey['key'];
        // $this->data['form']['recover_password'] = $valueGenerateKey['encryptedKey'];

        // Chamar o método para salvar o log em caso de erro
        GenerateLog::generateLog("error", "E-mail de recuperação de senha não enviado!", ['email' => (string) $this->data['form']['email']]);

        // Criar mensagem de erro para apresentar ao usuário
        $_SESSION['error'] = "E-mail de recuperação não enviado, tente novamente ou entre em contato com o e-mail {$_ENV['EMAIL_ADM']}";

        // Chamar o método para carregar a VIEW
            $this->viewForgotPassword();
            return;
        }

        // Criar a mensagem de sucesso ao editar
        $_SESSION['success'] = "Um email de recuperação foi enviado para o email informado! Acesse sua caixa de e-mail para recuperar a senha.";

        header("Location: {$_ENV['URL_ADM']}login");

        // // Instaciar o repositório para editar o recover_password no banco de dados
        // $userUpdate = new ResetPasswordRepository();
        // $result = $userUpdate->updateForgotPassword($this->data['form']);


        // // Acessa o IF se o repositório retornou TRUE
        // if ($result) {
        //     // Criar a mensagem de sucesso ao editar
        //     $_SESSION['success'] = "Um email de recuperação foi enviado para o email informado! - {$_ENV['URL_ADM']}reset-password/{$this->data['form']['key']}";

        //     // Redirecionar o usuário para a página de login
        //      header("Location: {$_ENV['URL_ADM']}login");
        // }else {
        //     // Criar a mensagem de erro ao tentar editar
        //     $this->data['errors'][] = "E-mail de recuperação não enviado, tente novamente ou entre em contato com o e-mail {$_ENV['EMAIL_ADM']}";

        //     // Chamar o método carregar a view
        //     $this->viewForgotPassword();
        // }
    }
}