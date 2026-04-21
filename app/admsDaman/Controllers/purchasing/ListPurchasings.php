<?php

namespace App\admsDaman\Controllers\purchasing;

use App\admsDaman\Views\Services\LoadViewService;

class ListPurchasings
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    public function index() 
    {
        // Instanciar o Repository para recuperar os registros do banco de dados
        //$listOrders = new PurchasingRepository();
        echo "Listar Compras";

        // Criar o título da página
        $this->data['title_head'] = "Compras";

        $this->data['menu'] = "list-purchasings";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/purchasing/list", $this->data);
        $loadView->loadView();
    }
}