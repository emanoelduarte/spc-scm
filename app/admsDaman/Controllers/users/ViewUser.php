<?php

namespace App\admsDaman\Controllers\users;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;
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

        // Instanciar o Repository para recuperar os níveis de acesso do usuário
        // $this->data['userAccessLevels'] = $viewUserAccessLevels->getUsersAccessLevels((int) $id);

        $viewUserAccessLevels = new UsersAccessLevelsRepository();
        $this->data['userAccessLevelsArray'] = $viewUserAccessLevels->getUserAccessLevelsArray((int) $id);

        // Instanciar o Repository para recuperar os níveis de acesso com menor prioridade de maior prioridade do usuário
        $this->data['lowerPriorityAccessLevels'] = $viewUserAccessLevels->getLowerPriorityAccessLevels();

        // Recuperar as obras vinculadas ao usuário
        $viewUserProjectsAssociate = new ProjectsRepository();
        $this->data['userProjectsAssociate'] = $viewUserProjectsAssociate->getUserProjectsAssociateArray((int) $id);

        // Recuperar id e obras gerais ativas
        $this->data['projectsActives'] = $viewUserProjectsAssociate->getAllProjectsSelectActive((int) $id);

        // Chamar o método para salvar o log
        GenerateLog::generateLog("error", "Visualizar o Usuário", ['id' => (int) $id]);

        $pageElements = [
            'title_head' => "Visualizar Usuário",
            'menu' => "list-users",
            'buttonPermissions' => ["ListUsers", "UpdatePasswordUser", "UpdateUser", "DeleteUser", "UpdateUserAccessLevels"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/users/view", $this->data);
        $loadView->loadView();
    }
}