<?php

namespace App\admsDaman\Controllers\users;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\UsersRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ViewUser 
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

    // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Usuário não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Usuário não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-users");

            return;
        }
        
        // Instanciar o Repository para recuperar os registros do banco de dados
        $viewUser = new UsersRepository();
        $this->data['user'] = $viewUser->getUser((int) $id);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['user']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Usuário não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Usuário não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-users");

            return;
        }

        // Chamar o método para salvar o log
        GenerateLog::generateLog("error", "Visualizar o Usuário", ['id' => (int) $id]);

        // Criar o título da página
        $this->data['title_head'] = "Visualizar Usuário";

        // Ativar o item de Menu
        $this->data['menu'] = "list-users";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/users/view", $this->data);
        $loadView->loadView();
    }
}