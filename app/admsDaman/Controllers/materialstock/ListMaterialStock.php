<?php

namespace App\admsDaman\Controllers\materialstock;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\CategoriesRepository;
use App\admsDaman\Models\Repository\MaterialStockRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ListMaterialStock
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados para ser usado na paginação*/
    private int $limitResult = 10;

    public function index(string|int $page = 1)
    {
        // Pegar dados para filtar por item
        $this->data['search'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Instanciar o Repository para recuperar os registros do banco de dados
        $listMaterial = new MaterialStockRepository();

        // Capturanto todos os itens
        $this->data['materialStock'] = $listMaterial->getAllMaterialStock(
            (int) $page,
            (int) $this->limitResult,
            $this->data['search']
        );

        if (is_array($this->data['materialStock']) && isset($this->data['materialStock']['no_project'])) {
            $_SESSION['error'] = "Você não possui obra vinculada. Entre em contato com o administrador.";
            header('Location: ' . $_ENV['URL_ADM'] . 'dashboard');
            exit;
        }

        $this->data['pagination'] = PaginationService::generatePagination(
            (int) $listMaterial->getAmountMaterials(),
            (int) $this->limitResult,
            (int) $page,
            'list-material-stock'
        );

        // Instanciar o repositório para preencher os selects.
        $getProjectSelect = new CategoriesRepository();
        $this->data['getAllCategoriesSelect'] = $getProjectSelect->getAllCategoriesSelect();

        // Instanciar o repositório para preencher os selects.
        $getAllProjectsSelectActive = new ProjectsRepository();
        $this->data['getAllProjectsSelectActive'] = $getAllProjectsSelectActive->getAllProjectsSelectActive();

        // Solicitar do repositório de níveis de acesso do usuário os níveis do usuário logado para configurar o conteúdo que ele tem acesso para manipular saídas do estoque
        $userAccessLevel = new UsersAccessLevelsRepository();
        $this->data['userAccessLevelsArray'] = $userAccessLevel->getUsersAccessLevels($_SESSION['user_id']);

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Listar Material do Estoque",
            'menu' => "list-material-stock",
            'buttonPermissions' => ["CreateMaterialStock", "CreateStockMovement", "ViewMaterialStock", "UpdateMaterialStock"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/materialstock/list", $this->data);
        $loadView->loadView();
    }
}
