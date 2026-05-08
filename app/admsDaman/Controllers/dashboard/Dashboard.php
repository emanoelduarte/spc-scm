<?php

namespace App\admsDaman\Controllers\dashboard;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Models\Repository\MenuPermissionUserRepository;
use App\admsDaman\Views\Services\LoadViewService;

class Dashboard
{
    /** @var array $dados Rece os dados que devem ser enviados para a VIEW */
    private array $data = [];

    public function index()
    {
        $pageElements = [
            'title_head' => "Dashboard",
            'menu' => "dashboard",
            'buttonPermissions' => [],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/dashboard/dashboard", $this->data);
        $loadView->loadView();
    }
}