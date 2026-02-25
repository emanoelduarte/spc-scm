<?php

namespace App\admsDaman\Controllers\orders;

use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller responsável por exibir os pedidos de compra
 */
class ListOrders 
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;
    
    public function index() : void 
    {
        $this->viewListOrders();
    }

    /**
     * Instanciar a classe responsável em carregar a VIEW e enviar os dados para View.
     * 
     * @return void
     */
    private function viewListOrders(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Editar Nível de Acesso";

        $this->data['menu'] = "list-orders";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/orders/list", $this->data);
        $loadView->loadView();
    }
}
?>