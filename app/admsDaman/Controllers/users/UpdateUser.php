<?php 

namespace App\admsDaman\Controllers\users;

use App\admsDaman\Controllers\Services\Validation\ValidationUserRakitService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\UsersRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller Editar Usuário
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 */
class UpdateUser 
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Editar o usuário
     * 
     * @param int|string $id id do usuário
     * @return void
     */
    public function index(int|string $id): void
    {
        // Receber os dados do formulário de cadastro de usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_update_user', $this->data['form']['csrf_token'])) {

            // Chamar o método Editar o usuário
            $this->editUser();
        } else {
            // Instanciar o Repository para recuperar o registro do banco de dados
            $viewUser = new UsersRepository();
            $this->data['form'] = $viewUser->getUser((int) $id);
            
            // Verificar se encontrou o registro no banco de dados
            if (!$this->data['form']) {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Usuário não encontrado", ['id' => (int) $id]);

                // Criar a mensagem de erro
                $_SESSION['error'] = "Usuário não encontrado!";

                // Redirecionar o usuário para a página listar
                header("Location: {$_ENV['URL_ADM']}list-users");
                return;
            }
            // Chamar o método carregar a view
            $this->viewUpdateUser();
        }
        
    }

    /**
     * Instanciar a classe responsável em carregar a VIEW e enviar os dados para View.
     */
    private function viewUpdateUser(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Editar Usuário";

        // Ativar o item de Menu
        $this->data['menu'] = "list-users";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/users/update", $this->data);
        $loadView->loadView();
    }

    /**
     * Editar Usuário
     * 
     * Este método realiza a edição de um usuário existente no sistema. Ele valida os dados do formulário usando a
     * Classe `ValidationUserRakitService`, exibe a view com os erros caso existam campos compdados incorretos,
     * Chama o repositório para atualizar o usuário e, depedendo do resultado, redireciona o usuário ou exibe
     * uma mensagem de erro
     * 
     * @return void
     */
    private function editUser(): void
    {

        // Instanciar a classe validar os dados do formulário com Rakit
        $validationUser = new ValidationUserRakitService();
        $this->data['errors'] = $validationUser->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados inclorretos
        if (!empty($this->data['errors'])) {

            // Chama o método carregar a view
            $this->viewUpdateUser();

            return;
        }
        // Instanciar o UsersRepository para chamar o método que faz a edição do usuário
        $userUpdate = new UsersRepository();
        $result = $userUpdate->updateUser($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar
            $_SESSION['success'] = "Usuário editado com sucesso!";

            // Redirecionar o usuário para a página de listar usuário
            header("Location: {$_ENV['URL_ADM']}view-user/" . $this->data['form']['id']);

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar
            $this->data['errors'][] = "Usuário não editado!";

            // Chamar o método carregar a view
            $this->viewUpdateUser();
        }
    }
}