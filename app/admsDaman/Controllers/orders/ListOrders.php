<?php

namespace App\admsDaman\Controllers\orders;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\CategoriesRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\StatusRepository;
use App\admsDaman\Controllers\Services\ProjectAccessService;
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
        // Recebe filtros do POST (quando aplica filtro) ou do GET (quando pagina)
        $this->data['search'] = $_SERVER['REQUEST_METHOD'] === 'POST'
            ? filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW)
            : filter_input_array(INPUT_GET, FILTER_UNSAFE_RAW);

        // Remove campos internos que não são filtros do usuário
        unset(
            $this->data['search']['url'],
            $this->data['search']['csrf_token'],
            $this->data['search']['submit']
        );

        // Recuperar as obras que o usuário logado pode acessar.
        //
        // null      = acesso global
        // []        = nenhuma obra
        // [1, 2, 3] = somente essas obras
        $projectAccessService = new ProjectAccessService();

        $accessibleProjectIds =
            $projectAccessService->getAccessibleProjectIds(
                (int) $_SESSION['user_id']
            );

        // Instanciar o Repository para recuperar os registros do banco de dados
        $listOrders = new OrdersRepository();

        $this->data['orders'] = $listOrders->getAllOrders(
            (int) $page,
            (int) $this->limitResult,
            $this->data['search'],
            $accessibleProjectIds
        );

        $this->data['pagination'] = PaginationService::generatePagination(
            (int) $listOrders->getAmountOrders(
                $this->data['search'],
                $accessibleProjectIds,
            ),
            (int) $this->limitResult,
            (int) $page,
            'list-orders',
            $this->data['search']
        );

        // Recuperar somente as obras que o usuário
        // possui permissão para consultar
        $projectsRepository = new ProjectsRepository();

        $this->data['getAllProjectsSelect'] = $projectsRepository->getProjectsSelectByIds(
                $accessibleProjectIds
            );

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
