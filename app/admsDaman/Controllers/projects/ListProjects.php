<?php

namespace App\admsDaman\Controllers\projects;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller responsável por listar as obras existentes ativas ou não com paginação
 * 
 */
class ListProjects
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados para ser usado na paginação*/
    private int $limitResult = 10;

    /**
     * Recuperar os ultimos Projetos
     * 
     * @return void
     */
    public function index(string|int $page = 1): void
    {
        $this->data['search'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Instanciar o Repository para recuperar os registros do banco de dados
        $listProjects = new ProjectsRepository();
        $listProjects->getAllProjects();

        $this->data['projects'] = $listProjects->getAllProjects(
            (int) $page,
            (int) $this->limitResult,
            $this->data['search']
        );

        $this->data['pagination'] = PaginationService::generatePagination(
            (int) $listProjects->getAmountProjects($this->data['search']),
            (int) $this->limitResult,
            (int) $page,
            'list-projects'
        );

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Listar Obras",
            'menu' => "list-projects",
            'buttonPermissions' => ["CreateProject", "ViewProject", "UpdateProject", "DeleteProject"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Criar o título da página
        $this->data['title_head'] = "Listar Projetos";

        // Ativar o item de Menu
        $this->data['menu'] = "list-projects";

        // Carregar a View do Listar Projetos
        $loadView = new LoadViewService("admsDaman/Views/projects/list", $this->data);
        $loadView->loadView();
    }
}
