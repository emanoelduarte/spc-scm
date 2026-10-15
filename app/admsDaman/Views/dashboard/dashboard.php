<?php

$statusCount = $this->data['statusCount'] ?? [];
$ordersAnalysisByProject = $this->data['ordersAnalysisByProject'] ?? [];
$lastPurchasingsWeek = $this->data['getLastPurchasingsWeek'] ?? [];
$lastOrders = $this->data['lastOrders'] ?? [];

$analysisOlderThan7Days = (int) ($this->data['analysisOlderThan7Days'] ?? 0);
$purchasedOlderThan7Days = (int) ($this->data['purchasedOlderThan7Days'] ?? 0);
$incompleteRentals = (int) ($this->data['incompleteRentals'] ?? 0);
$totalPurchasingsWeek = (float) ($this->data['getTotalPurchasingsWeek'] ?? 0);

// Recuperar permissões disponíveis para o usuário
$buttonPermissions = $this->data['buttonPermissions'] ?? [];
$menuPermission = $this->data['menuPermission'] ?? [];

// Verificar permissões utilizadas no Dashboard
$canSeeBuyerContent = in_array('BuyerContent', $buttonPermissions, true);
$canViewOrder = in_array('ViewOrder', $buttonPermissions, true);
$canListOrders = in_array('ListOrders', $menuPermission, true);

// Recuperar o escopo apresentado no Dashboard
$dashboardScope = $this->data['dashboardScope'] ?? 'projects';
$accessibleProjectsCount = $this->data['accessibleProjectsCount'] ?? 0;


// ============================================================
// RESUMO DOS PEDIDOS
// ============================================================

$totalOrders = 0;
$analysisOrders = 0;
$activeStatuses = 0;

foreach ($statusCount as $status) {

    $statusTotal = (int) ($status['total'] ?? 0);

    $totalOrders += $statusTotal;

    if ($statusTotal > 0) {
        $activeStatuses++;
    }

    // Status 1 = Análise
    if ((int) ($status['id'] ?? 0) === 1) {
        $analysisOrders = $statusTotal;
    }
}

$projectsInAnalysis = count($ordersAnalysisByProject);

// ============================================================
// METADADOS DOS STATUS
// ============================================================

$statusMetaById = [];

foreach ($statusCount as $status) {
    $statusMetaById[(int) ($status['id'] ?? 0)] = $status;
}


// ============================================================
// MAIOR QUANTIDADE DE PEDIDOS EM ANÁLISE EM UMA OBRA
// Utilizado para calcular a barra proporcional.
// ============================================================

$maxAnalysisOrders = 0;

foreach ($ordersAnalysisByProject as $project) {
    $projectTotal = (int) ($project['total'] ?? 0);

    if ($projectTotal > $maxAnalysisOrders) {
        $maxAnalysisOrders = $projectTotal;
    }
}

?>

<div class="container-fluid px-4 dashboard-page">

    <!-- ====================================================== -->
    <!-- CABEÇALHO                                               -->
    <!-- ====================================================== -->

    <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 mt-3 mb-4">

        <div>

            <h2 class="dashboard-header-title">
                Dashboard
            </h2>

            <p class="dashboard-header-description">
                Visão geral dos pedidos e operações disponíveis para o seu acesso.
            </p>

        </div>


        <div class="ms-md-auto">

            <?php if ($dashboardScope === 'global'): ?>

                <div class="dashboard-scope">
                    <i class="fa-solid fa-earth-americas me-1"></i>
                    Visão geral da empresa · Todas as obras
                </div>

            <?php else: ?>

                <div class="dashboard-scope">
                    <i class="fa-solid fa-building me-1"></i>

                    <?php if ($accessibleProjectsCount === 0): ?>

                        Nenhuma obra vinculada

                    <?php else: ?>

                        <?= $accessibleProjectsCount; ?>
                        obra<?= $accessibleProjectsCount !== 1 ? 's' : ''; ?>
                        vinculada<?= $accessibleProjectsCount !== 1 ? 's' : ''; ?>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <?php include './app/admsDaman/Views/partials/alerts.php'; ?>


    <!-- ====================================================== -->
    <!-- INDICADORES PRINCIPAIS                                 -->
    <!-- ====================================================== -->

    <div class="row g-3 mb-4">

        <div class="<?= $canSeeBuyerContent ? 'col-xl-3' : 'col-xl-4'; ?> col-md-6">

            <div class="card dashboard-kpi">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="dashboard-kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>

                    <div>

                        <div class="dashboard-kpi-title">
                            Total de pedidos
                        </div>

                        <div class="dashboard-kpi-value">
                            <?= $totalOrders; ?>
                        </div>

                        <div class="dashboard-kpi-subtitle">
                            Dentro do seu escopo de acesso
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="<?= $canSeeBuyerContent ? 'col-xl-3' : 'col-xl-4'; ?> col-md-6">

            <div class="card dashboard-kpi">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="dashboard-kpi-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>

                    <div>

                        <div class="dashboard-kpi-title">
                            Em análise
                        </div>

                        <div class="dashboard-kpi-value">
                            <?= $analysisOrders; ?>
                        </div>

                        <div class="dashboard-kpi-subtitle">
                            Pedidos aguardando andamento
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="<?= $canSeeBuyerContent ? 'col-xl-3' : 'col-xl-4'; ?> col-md-6">

            <div class="card dashboard-kpi">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="dashboard-kpi-icon bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-helmet-safety"></i>
                    </div>

                    <div>

                        <div class="dashboard-kpi-title">
                            Obras com pedidos em análise
                        </div>

                        <div class="dashboard-kpi-value">
                            <?= $projectsInAnalysis; ?>
                        </div>

                        <div class="dashboard-kpi-subtitle">
                            Obras que exigem acompanhamento
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <?php if ($canSeeBuyerContent): ?>

            <div class="col-xl-3 col-md-6">

                <div class="card dashboard-kpi">

                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="dashboard-kpi-icon bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>

                        <div>

                            <div class="dashboard-kpi-title">
                                Compras nos últimos 7 dias
                            </div>

                            <div class="dashboard-kpi-value">
                                R$ <?= number_format($totalPurchasingsWeek, 2, ',', '.'); ?>
                            </div>

                            <div class="dashboard-kpi-subtitle">
                                <?= count($lastPurchasingsWeek); ?>
                                compra<?= count($lastPurchasingsWeek) !== 1 ? 's' : ''; ?>
                                registrada<?= count($lastPurchasingsWeek) !== 1 ? 's' : ''; ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

    <!-- ======================================================== -->
    <!-- BLOCO DE ALERTA DE PEDIDOS EM ANÁLISE HÁ MAIS DE 7 DIAS  -->
    <!-- ======================================================== -->
    <?php if ($analysisOlderThan7Days > 0 || $purchasedOlderThan7Days > 0  || $incompleteRentals > 0): ?>

        <div class="small fw-semibold text-warning text-uppercase mb-2">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Requer atenção
        </div>


        <?php if ($analysisOlderThan7Days > 0): ?>

            <div class="card dashboard-attention mb-3">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="dashboard-attention-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>

                    <div class="flex-grow-1">

                        <div class="fw-semibold">
                            <?= $analysisOlderThan7Days; ?>
                            pedido<?= $analysisOlderThan7Days !== 1 ? 's' : ''; ?>
                            em análise há mais de 7 dias
                        </div>

                        <div class="small dashboard-attention-description">
                            Existem pedidos em análise há mais de 7 dias aguardando andamento.
                        </div>

                    </div>

                    <?php if ($canListOrders): ?>

                        <a href="<?= $_ENV['URL_ADM']; ?>list-orders/1?<?= http_build_query([
                                                                            'adms_daman_acquisition_status_id' => 1,
                                                                            'status_older_than_days' => 7
                                                                        ]); ?>"
                            class="btn btn-sm btn-outline-warning">

                            Ver pedidos
                            <i class="fa-solid fa-arrow-right ms-1"></i>

                        </a>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>


        <?php if ($purchasedOlderThan7Days > 0): ?>

            <div class="card dashboard-attention mb-4">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="dashboard-attention-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>

                    <div class="flex-grow-1">

                        <div class="fw-semibold">
                            <?= $purchasedOlderThan7Days; ?>
                            pedido<?= $purchasedOlderThan7Days !== 1 ? 's' : ''; ?>
                            comprado<?= $purchasedOlderThan7Days !== 1 ? 's' : ''; ?>
                            há mais de 7 dias
                        </div>

                        <div class="small dashboard-attention-description">
                            Existem pedidos comprados há mais de 7 dias que ainda não foram atualizados para entregue.
                        </div>

                    </div>

                    <?php if ($canListOrders): ?>

                        <a href="<?= $_ENV['URL_ADM']; ?>list-orders/1?<?= http_build_query([
                                                                            'adms_daman_acquisition_status_id' => 3,
                                                                            'status_older_than_days' => 7
                                                                        ]); ?>"
                            class="btn btn-sm btn-outline-warning">

                            Ver pedidos
                            <i class="fa-solid fa-arrow-right ms-1"></i>

                        </a>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>

        <?php if ($incompleteRentals > 0): ?>

            <div class="card dashboard-attention mb-4">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="dashboard-attention-icon">
                        <i class="fa-solid fa-file-circle-exclamation"></i>
                    </div>

                    <div class="flex-grow-1">

                        <div class="fw-semibold">
                            <?= $incompleteRentals; ?>
                            <?= $incompleteRentals === 1 ? 'locação' : 'locações'; ?>
                            com cadastro incompleto
                        </div>

                        <div class="small dashboard-attention-description">
                            Existem pedidos de locação sem informação de locador e/ou contrato.
                        </div>

                    </div>

                    <?php if ($canListOrders): ?>

                        <a href="<?= $_ENV['URL_ADM']; ?>list-orders/1?<?= http_build_query([
                                                                            'adms_daman_acquisition_types_id' => 2,
                                                                            'rental_incomplete' => 1
                                                                        ]); ?>"
                            class="btn btn-sm btn-outline-warning">

                            Ver pedidos
                            <i class="fa-solid fa-arrow-right ms-1"></i>

                        </a>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>

    <?php endif; ?>


    <!-- ====================================================== -->
    <!-- STATUS DOS PEDIDOS                                     -->
    <!-- ====================================================== -->

    <div class="card dashboard-section mb-4">

        <div class="card-header d-flex align-items-center">

            <div>
                <i class="fa-solid fa-chart-simple text-primary me-2"></i>
                Status dos Pedidos
            </div>


            <?php if ($canListOrders): ?>

                <a href="<?= $_ENV['URL_ADM']; ?>list-orders"
                    class="btn btn-sm btn-outline-primary ms-auto">

                    Ver pedidos

                    <i class="fa-solid fa-arrow-right ms-1"></i>

                </a>

            <?php endif; ?>

        </div>


        <div class="card-body">

            <?php if (!empty($statusCount)): ?>

                <div class="dashboard-status-grid">

                    <?php foreach ($statusCount as $status): ?>
                        <?php

                        $statusColor = $status['color'] ?? '#6c757d';
                        $statusTotal = (int) ($status['total'] ?? 0);
                        $statusId = (int) ($status['id'] ?? 0);

                        $statusUrl = $_ENV['URL_ADM'] . 'list-orders?' . http_build_query([
                            'adms_daman_acquisition_status_id' => $statusId
                        ]);

                        ?>


                        <?php if ($canListOrders): ?>

                            <a href="<?= $statusUrl; ?>" class="dashboard-status-link">

                            <?php endif; ?>


                            <div
                                class="dashboard-status <?= $canListOrders ? 'dashboard-status-clickable' : ''; ?>"
                                style="background: <?= htmlspecialchars($statusColor); ?>14;">

                                <div
                                    class="dashboard-status-header"
                                    style="color: <?= htmlspecialchars($statusColor); ?>;">

                                    <i class="fa-solid <?= htmlspecialchars($status['icon'] ?? 'fa-circle'); ?>"></i>

                                    <?= htmlspecialchars($status['name'] ?? 'Status'); ?>

                                </div>

                                <div
                                    class="dashboard-status-value"
                                    style="color: <?= htmlspecialchars($statusColor); ?>;">

                                    <?= $statusTotal; ?>

                                </div>

                            </div>


                            <?php if ($canListOrders): ?>

                            </a>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="dashboard-empty">

                    <i class="fa-solid fa-chart-simple"></i>

                    <div class="fw-semibold">
                        Nenhum pedido encontrado
                    </div>

                    <div class="small mt-1">
                        Não existem pedidos disponíveis no seu escopo de acesso.
                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- ANÁLISE POR OBRA / COMPRAS                             -->
    <!-- ====================================================== -->

    <div class="row g-4">

        <div class="<?= $canSeeBuyerContent ? 'col-xl-7' : 'col-12'; ?>">

            <div class="card dashboard-section h-100">

                <div class="card-header d-flex align-items-center">

                    <div>
                        <i class="fa-solid fa-building text-warning me-2"></i>
                        Pedidos em análise por obra
                    </div>

                    <span class="badge bg-warning text-dark ms-auto">
                        <?= $analysisOrders; ?>
                    </span>

                </div>


                <div class="card-body p-0">

                    <?php if (!empty($ordersAnalysisByProject)): ?>

                        <?php foreach ($ordersAnalysisByProject as $index => $project): ?>

                            <?php

                            $projectTotal = (int) ($project['total'] ?? 0);

                            $percentage = $maxAnalysisOrders > 0
                                ? ($projectTotal / $maxAnalysisOrders) * 100
                                : 0;

                            ?>

                            <div class="dashboard-project-row">

                                <div class="d-flex align-items-center">

                                    <div class="dashboard-project-position">
                                        <?= $index + 1; ?>°
                                    </div>

                                    <div class="dashboard-project-name">
                                        <?= htmlspecialchars($project['project_name'] ?? 'Obra'); ?>
                                    </div>

                                    <span class="badge bg-warning text-dark ms-auto">
                                        <?= $projectTotal; ?>
                                        pedido<?= $projectTotal !== 1 ? 's' : ''; ?>
                                    </span>

                                </div>


                                <div class="progress dashboard-project-progress">

                                    <div
                                        class="progress-bar bg-warning"
                                        role="progressbar"
                                        style="width: <?= $percentage; ?>%;"
                                        aria-valuenow="<?= $percentage; ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="dashboard-empty">

                            <i class="fa-solid fa-circle-check text-success"></i>

                            <div class="fw-semibold">
                                Nenhum pedido em análise
                            </div>

                            <div class="small mt-1">
                                Não existem pendências de análise no momento.
                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <?php if ($canSeeBuyerContent): ?>

            <div class="col-xl-5">

                <div class="card dashboard-section h-100">

                    <div class="card-header d-flex align-items-center">

                        <div>
                            <i class="fa-solid fa-cart-shopping text-success me-2"></i>
                            Últimas compras
                        </div>

                        <span class="badge bg-success ms-auto">
                            <?= count($lastPurchasingsWeek); ?>
                        </span>

                    </div>


                    <div class="card-body">

                        <?php if (!empty($lastPurchasingsWeek)): ?>

                            <div class="mb-3">

                                <div class="small text-muted">
                                    Total nos últimos 7 dias
                                </div>

                                <div class="fs-4 fw-bold text-success">
                                    R$ <?= number_format($totalPurchasingsWeek, 2, ',', '.'); ?>
                                </div>

                            </div>


                            <?php foreach ($lastPurchasingsWeek as $purchased): ?>

                                <div class="dashboard-purchase">

                                    <div class="d-flex gap-3">

                                        <div class="dashboard-purchase-icon bg-success bg-opacity-10 text-success">
                                            <i class="fa-solid fa-cart-shopping"></i>
                                        </div>


                                        <div class="flex-grow-1">

                                            <div class="d-flex justify-content-between gap-3">

                                                <div>

                                                    <div class="fw-semibold">
                                                        <?= htmlspecialchars($purchased['project_name'] ?? 'Obra'); ?>
                                                    </div>

                                                    <div class="small text-muted">
                                                        <?= htmlspecialchars($purchased['service'] ?? ''); ?>
                                                    </div>

                                                    <?php if (!empty($purchased['supplier_name'])): ?>

                                                        <div class="small text-muted">
                                                            <?= htmlspecialchars($purchased['supplier_name']); ?>
                                                        </div>

                                                    <?php endif; ?>

                                                </div>


                                                <div class="text-end">

                                                    <div class="fw-semibold">
                                                        R$ <?= number_format(
                                                                (float) ($purchased['total_final'] ?? 0),
                                                                2,
                                                                ',',
                                                                '.'
                                                            ); ?>
                                                    </div>

                                                    <div class="small text-muted">
                                                        <?= !empty($purchased['created_at'])
                                                            ? date('d/m/Y', strtotime($purchased['created_at']))
                                                            : ''; ?>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="dashboard-empty">

                                <i class="fa-solid fa-circle-check text-success"></i>

                                <div class="fw-semibold">
                                    Nenhuma compra encontrada
                                </div>

                                <div class="small mt-1">
                                    Não houve compras registradas nos últimos 7 dias.
                                </div>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

    <!-- ====================================================== -->
    <!-- ÚLTIMOS PEDIDOS                                        -->
    <!-- ====================================================== -->

    <div class="card dashboard-section mt-4">

        <div class="card-header d-flex align-items-center">

            <div>
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>
                Últimos pedidos
            </div>

            <?php if ($canListOrders): ?>

                <a href="<?= $_ENV['URL_ADM']; ?>list-orders"
                    class="btn btn-sm btn-outline-primary ms-auto">

                    Ver todos

                    <i class="fa-solid fa-arrow-right ms-1"></i>

                </a>

            <?php endif; ?>

        </div>


        <div class="card-body p-0">

            <?php if (!empty($lastOrders)): ?>

                <?php foreach ($lastOrders as $order): ?>

                    <?php

                    $orderId = (int) ($order['pedido_id'] ?? 0);
                    $orderStatusId = (int) ($order['status_id'] ?? 0);

                    $orderStatus = $statusMetaById[$orderStatusId] ?? [];

                    $orderStatusColor = $orderStatus['color'] ?? '#6c757d';
                    $orderStatusIcon = $orderStatus['icon'] ?? 'fa-circle';

                    $orderDate = !empty($order['created_at'])
                        ? date('d/m/Y H:i', strtotime($order['created_at']))
                        : '-';

                    ?>


                    <?php if ($canViewOrder): ?>

                        <a href="<?= $_ENV['URL_ADM']; ?>view-order/<?= $orderId; ?>"
                            class="dashboard-order-row">

                        <?php else: ?>

                            <div class="dashboard-order-row">

                            <?php endif; ?>


                            <div class="dashboard-order-number">

                                <i class="fa-solid fa-file-lines"></i>

                            </div>


                            <div class="dashboard-order-content">

                                <div class="d-flex align-items-center gap-2 flex-wrap">

                                    <span class="fw-semibold">
                                        Pedido #<?= $orderId; ?>
                                    </span>

                                    <span
                                        class="dashboard-order-status"
                                        style="
                                color: <?= htmlspecialchars($orderStatusColor); ?>;
                                background: <?= htmlspecialchars($orderStatusColor); ?>18;
                                border-color: <?= htmlspecialchars($orderStatusColor); ?>55;
                            ">

                                        <i class="fa-solid <?= htmlspecialchars($orderStatusIcon); ?> me-1"></i>

                                        <?= htmlspecialchars($order['status_name'] ?? 'Status'); ?>

                                    </span>

                                </div>


                                <div class="dashboard-order-project">

                                    <i class="fa-solid fa-helmet-safety me-1"></i>

                                    <?= htmlspecialchars($order['project_name'] ?? 'Obra'); ?>

                                </div>


                                <div class="dashboard-order-meta">

                                    <?= htmlspecialchars($order['name_tape'] ?? ''); ?>

                                    <?php if (!empty($order['category_name'])): ?>

                                        <span class="mx-1">·</span>

                                        <?= htmlspecialchars($order['category_name']); ?>

                                    <?php endif; ?>

                                </div>

                            </div>


                            <div class="dashboard-order-date">

                                <i class="fa-regular fa-clock me-1"></i>

                                <?= $orderDate; ?>

                                <?php if ($canViewOrder): ?>

                                    <i class="fa-solid fa-chevron-right ms-3"></i>

                                <?php endif; ?>

                            </div>


                            <?php if ($canViewOrder): ?>

                        </a>

                    <?php else: ?>

        </div>

    <?php endif; ?>


<?php endforeach; ?>

<?php else: ?>

    <div class="dashboard-empty">

        <i class="fa-solid fa-file-circle-xmark"></i>

        <div class="fw-semibold">
            Nenhum pedido encontrado
        </div>

        <div class="small mt-1">
            Não existem pedidos disponíveis no seu escopo de acesso.
        </div>

    </div>

<?php endif; ?>

    </div>

</div>

</div>