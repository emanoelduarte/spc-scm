<?php

namespace App\admsDaman\Controllers\dashboard;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Models\Repository\MenuPermissionUserRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\PurchasingRepository;
use App\admsDaman\Models\Repository\StatusRepository;
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
            'buttonPermissions' => ["BuyerContent"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Recuperar status dos pedidos com contagem de cada um
        $statusCount = new StatusRepository();
        $this->data['statusCount'] = $statusCount->countStatus();

        // Recuperar pedidos em análise por obra
        $ordersAnalysisByProject = new OrdersRepository();
        $this->data['ordersAnalysisByProject'] = $ordersAnalysisByProject->rankProjectsStatusAnalisys();

        // Recuperar ultimas 10 compras efetivadas na semana 
        $getLastPurchasingsWeek = new PurchasingRepository();
        $this->data['getLastPurchasingsWeek'] = $getLastPurchasingsWeek->getLastPurchasingsWeek();

        // Recuperar o total de todas as compras dos ultimos 7 dias 
        $getTotalPurchasingsWeek = new PurchasingRepository();
        $this->data['getTotalPurchasingsWeek'] = $getTotalPurchasingsWeek->getTotalPurchasingsWeek();

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/dashboard/dashboard", $this->data);
        $loadView->loadView();
    }
}