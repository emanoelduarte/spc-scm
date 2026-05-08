<?php

namespace App\admsDaman\Controllers\projects;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationProjectService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para editar Obra
 *
 * Esta classe é responsável por gerenciar a edição de informações de uma Obra existente. Inclui a validação dos dados
 * do formulário, a atualização das informações da Obra no repositório e a renderização da visualização apropriada.
 * Caso haja algum problema, como uma Obra não encontrado ou dados inválidos, mensagens de erro são exibidas e registradas.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\projects;
 */
class UpdateProject
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Editar o Obra.
     *
     * Este método gerencia o processo de edição de uma Obra. Recebe os dados do formulário, valida o CSRF token e
     * a existência da Obra, e chama o método adequado para editar a Obra ou carregar a visualização de edição.
     *
     * @param int|string $id ID do Obra a ser editado.
     * 
     * @return void
     */
    public function index(int|string $id): void
    {
        // Receber os dados do formulário de cadastro de Obra
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_update_project', $this->data['form']['csrf_token'])) {

            // Chamar o método Editar o Obra
            $this->editProject();
        } else {
            // Instanciar o Repository para recuperar o registro do banco de dados
            $viewProject = new ProjectsRepository();
            $this->data['form'] = $viewProject->getProject((int) $id);

            // Verificar se encontrou o registro no banco de dados
            if (!$this->data['form']) {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Obra não encontrada", ['id' => (int) $id]);

                // Criar a mensagem de erro
                $_SESSION['error'] = "Obra não encontrada!";

                // Redirecionar o usuário para a página listar
                header("Location: {$_ENV['URL_ADM']}list-projects");

                return;
            }

            $this->viewUpdateProject();
        }
    }

    /**
     * Instanciar a classe responsável em carregar a VIEW e enviar os dados para View.
     * 
     * @return void
     */
    private function viewUpdateProject(): void
    {

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Editar Obra",
            'menu' => "list-projects",
            'buttonPermissions' => ["ListProjects", "ViewProject"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));
        
        // Criar o título da página
        $this->data['title_head'] = "Editar Obra";

        // Ativar o item de Menu
        $this->data['menu'] = "list-projects";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/projects/update", $this->data);
        $loadView->loadView();
    }

    /**
     * Editar Obra
     * 
     * Este método realiza a edição de uma Obra existente no sistema. Ele valida os dados do formulário usando a
     * Classe `ValidationProjectService`, exibe a view com os erros caso existam campos compdados incorretos,
     * Chama o repositório para atualizar o Obra e, depedendo do resultado, redireciona o Obra ou exibe
     * uma mensagem de erro
     * 
     * @return void
     */
    private function editProject(): void
    {
        // Instanciar a classe validar os dados do formulário com Rakit
        $validationProject = new ValidationProjectService();
        $this->data['errors'] = $validationProject->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados incorretos
        if (!empty($this->data['errors'])) {

            // Chama o método carregar a view
            $this->viewUpdateProject();

            return;
        }

        // Instanciar o ProjectsRepository para chamar o método que faz a edição da Obra
        $projectUpdate = new ProjectsRepository();
        $result = $projectUpdate->updateProject($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar
            $_SESSION['success'] = "Obra editada com sucesso!";

            // Redirecionar o usuário para a página de visualizar Obra
            header("Location: {$_ENV['URL_ADM']}view-project/{$this->data['form']['id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar
            $this->data['errors'][] = "Obra não editada!";

            // Chamar o método carregar a view
            $this->viewUpdateProject();
        }
    }
}
