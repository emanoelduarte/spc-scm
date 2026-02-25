<?php

namespace App\admsDaman\Controllers\dashboard;

use App\admsDaman\Views\Services\LoadViewService;

class Dashboard
{
    /** @var array|string|null $dados Rece os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    public function index()
    {
        // Criar o título da página
        $this->data['title_head'] = "Dashboard";

        // Ativar o item de Menu
        $this->data['menu'] = "dashboard";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/dashboard/dashboard", $this->data);
        $loadView->loadView();
    }
}