<?php

namespace App\admsDaman\Controllers\materialstock;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\MaterialStockRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
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

        $this->data['pagination'] = PaginationService::generatePagination(
            (int) $listMaterial->getAmountMaterials(),
            (int) $this->limitResult,
            (int) $page,
            'list-material-stock'
        );

        // Instanciar o repositório para preencher os selects.
        $getAllProjectsSelect = new ProjectsRepository();
        $this->data['getAllProjectsSelect'] = $getAllProjectsSelect->getAllProjectsSelect();

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Listar Material do Estoque",
            'menu' => "list-material-stock",
            'buttonPermissions' => ["CreateMaterialStock", "CreateStockMovement", "UpdateMaterialStock", "ViewMaterialStock", "UpdateMaterialStock"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/materialstock/list", $this->data);
        $loadView->loadView();
    }
}
?>