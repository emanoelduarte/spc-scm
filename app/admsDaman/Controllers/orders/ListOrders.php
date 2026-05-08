<?php

namespace App\admsDaman\Controllers\orders;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\CategoriesRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\StatusRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller responsável por exibir os pedidos de compra
 */
class ListOrders
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados para ser usado na paginação*/
    private int $limitResult = 10;

    public function index(string|int $page = 1): void
    {
        $this->data['search'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Instanciar o Repository para recuperar os registros do banco de dados
        $listOrders = new OrdersRepository();

        $this->data['orders'] = $listOrders->getAllOrders(
            (int) $page,
            (int) $this->limitResult,
            $this->data['search']
        );

        $this->data['pagination'] = PaginationService::generatePagination(
            (int) $listOrders->getAmountOrders(),
            (int) $this->limitResult,
            (int) $page,
            'list-orders'
        );

        $this->data['order_number'] = $this->data['search'];

        // Instanciar o repositório para preencher os selects.
        $getAllProjectsSelect = new ProjectsRepository();
        $this->data['getAllProjectsSelect'] = $getAllProjectsSelect->getAllProjectsSelect();

        // Instanciar o repositório para preencher os selects.
        $getAllStatusSelect = new StatusRepository();
        $this->data['getAllStatusSelect'] = $getAllStatusSelect->getAllStatusSelect();

        // Instanciar o repositório para preencher os selects.
        $getProjectSelect = new CategoriesRepository();
        $this->data['getAllCategoriesSelect'] = $getProjectSelect->getAllCategoriesSelect();

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Listar Pedidos",
            'menu' => "list-orders",
            'buttonPermissions' => ["CreateOrder", "ViewOrder", "UpdateOrder", "DeleteOrder", "UpdateRentalOrder"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/orders/list", $this->data);
        $loadView->loadView();
    }
}