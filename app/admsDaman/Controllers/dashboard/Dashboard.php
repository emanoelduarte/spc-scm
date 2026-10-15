<?php

namespace App\admsDaman\Controllers\dashboard;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\PurchasingRepository;
use App\admsDaman\Models\Repository\StatusRepository;
use App\admsDaman\Views\Services\LoadViewService;
use App\admsDaman\Controllers\Services\ProjectAccessService;

class Dashboard
{
    /** @var array $dados Rece os dados que devem ser enviados para a VIEW */
    private array $data = [];

    public function index()
    {
        $pageElements = [
            'title_head' => "Dashboard",
            'menu' => "dashboard",
            'buttonPermissions' => ["BuyerContent", "ViewOrder"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Recuperar as obras que o usuário possui acesso
        $projectAccessService = new ProjectAccessService();
        $accessibleProjectIds = $projectAccessService->getAccessibleProjectIds((int) $_SESSION['user_id']);

        // Informar à View qual é o escopo apresentado no Dashboard
        $this->data['dashboardScope'] = $accessibleProjectIds === null ? 'global' : 'projects';
        $this->data['accessibleProjectsCount'] = $accessibleProjectIds === null ? null : count($accessibleProjectIds);

        // Recuperar status dos pedidos respeitando as obras acessíveis
        $statusCount = new StatusRepository();
        $this->data['statusCount'] = $statusCount->countStatus($accessibleProjectIds);

        // Recuperar informações dos pedidos respeitando as obras acessíveis
        $ordersRepository = new OrdersRepository();

        $this->data['ordersAnalysisByProject'] = $ordersRepository->rankProjectsStatusAnalisys($accessibleProjectIds);

        // Recuperar pedidos em análise há mais de 7 dias
        $this->data['analysisOlderThan7Days'] = $ordersRepository->getAmountOrders(
            [
                'adms_daman_acquisition_status_id' => 1,
                'status_older_than_days' => 7
            ],
            $accessibleProjectIds
        );

        // Recuperar pedidos comprados há mais de 7 dias sem atualização para entregue
        $this->data['purchasedOlderThan7Days'] = $ordersRepository->getAmountOrders(
            [
                'adms_daman_acquisition_status_id' => 3,
                'status_older_than_days' => 7
            ],
            $accessibleProjectIds
        );

        // Recuperar locações com locador ou contrato não informados
        $this->data['incompleteRentals'] = $ordersRepository->getAmountOrders(
            [
                'adms_daman_acquisition_types_id' => 2,
                'rental_incomplete' => 1
            ],
            $accessibleProjectIds
        );

        // Recuperar os 5 pedidos mais recentes
        $this->data['lastOrders'] = $ordersRepository->getAllOrders(
            1,
            5,
            [],
            $accessibleProjectIds
        );

        // Valores padrão para usuários sem acesso ao conteúdo de compras
        $this->data['getLastPurchasingsWeek'] = [];
        $this->data['getTotalPurchasingsWeek'] = 0;

        // Recuperar informações de compras somente para usuários autorizados
        if (in_array('BuyerContent', $this->data['buttonPermissions'] ?? [], true)) {
            $purchasingRepository = new PurchasingRepository();

            $this->data['getLastPurchasingsWeek'] = $purchasingRepository->getLastPurchasingsWeek();
            $this->data['getTotalPurchasingsWeek'] = $purchasingRepository->getTotalPurchasingsWeek();
        }

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/dashboard/dashboard", $this->data);
        $loadView->loadView();
    }
}
