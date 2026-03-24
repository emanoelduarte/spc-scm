<?php

namespace App\admsDaman\Controllers\projects;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ViewProject
{
     /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /**
     * Recuperar a obra específicada pelo id enviado pela URL
     * 
     * @return void
     */
    public function index(int|string $id):void
    {
         // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Obra não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Obra não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-projects");

            return;
        }

        // Instanciar o Repository para recuperar os registros do banco de dados
        $viewProject = new ProjectsRepository();
        $this->data['project'] = $viewProject->getProject((int) $id);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['project']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Obra não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Obra não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-projects");

            return;
        }

        // Criar o título da página
        $this->data['title_head'] = "Visualizar Obra";

        // Ativar o item de Menu
        $this->data['menu'] = "list-projects";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/projects/view", $this->data);
        $loadView->loadView();
    }
}