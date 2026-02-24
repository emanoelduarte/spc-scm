<?php 

namespace App\admsDaman\Controllers\users;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\UsersRepository;

class DeleteUser 
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    public function index(): void
    {
        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // var_dump($this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT));

        // Acessar o IF se existir o CSRF e for valido o CSRF
        if (!isset($this->data['form']['csrf_token']) or !CSRFHelper::validateCSRFToken('form_delete_user', $this->data['form']['csrf_token']) or empty($this->data['form']['id'])) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Usuário não encontrado", []);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Usuário não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-users");

            return;
        }

        // Instanciar o Repository para recuperar o registro do banco de dados
        $deleteUser = new UsersRepository();
        $this->data['user'] = $deleteUser->getUser((int) $this->data['form']['id']);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['user']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Usuário não encontrado", ['id' => (int) $this->data['form']['id']]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Usuário não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-users");

            return;
        }

        // Instanciar o Repository para apagar o registro do banco de dados
        $result = $deleteUser->deleteUser($this->data['form']['id']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao apagar
            $_SESSION['success'] = "Usuário apagado com sucesso!";

            // Redirecionar o usuário para a página de listar usuários
            header("Location: {$_ENV['URL_ADM']}list-users");

            return;
        } else {
            // Criar a mensagem de erro ao tentar apagar
            $_SESSION['error'] = "Usuário não apagado!";

            // Redirecionar o usuário para a página de listar usuários
            header("Location: {$_ENV['URL_ADM']}list-users");
        }
    }
}