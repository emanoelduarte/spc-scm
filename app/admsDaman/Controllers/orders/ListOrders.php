<?php

namespace App\admsDaman\Controllers\orders;

use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\OrdersRepository;
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
    
    public function index(string|int $page = 1) : void 
    {
        $orderNumber = filter_input(INPUT_POST, 'order_number', FILTER_SANITIZE_NUMBER_INT);

        // Instanciar o Repository para recuperar os registros do banco de dados
        $listOrders = new OrdersRepository();

       $this->data['orders'] = $listOrders->getAllOrders(
        (int) $page, 
        (int) $this->limitResult, 
        $orderNumber
        );

       $this->data['pagination'] = PaginationService::generatePagination(
        (int) $listOrders->getAmountOrders(), 
        (int) $this->limitResult, 
        (int) $page, 
        'list-orders');

        $this->data['order_number'] = $orderNumber;

       // Criar o título da página
        $this->data['title_head'] = "Pedidos";

        $this->data['menu'] = "list-orders";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/orders/list", $this->data);
        $loadView->loadView();
    }
}
?>