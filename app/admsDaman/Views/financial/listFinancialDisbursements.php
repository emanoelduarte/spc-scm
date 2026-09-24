<?php

$entries =
    $this->data['financial_disbursements']
    ?? [];

$filters =
    $this->data['filters']
    ?? [];

$projects =
    $this->data['projects']
    ?? [];

$categories =
    $this->data['expense_categories']
    ?? [];

$paymentMethods =
    $this->data['financial_payment_methods']
    ?? [];

$summary =
    $this->data['summary']
    ?? [
        'total_amount' => 0,
        'purchase_amount' => 0,
        'direct_amount' => 0,
    ];

$categoryBreakdown =
    $this->data['category_breakdown']
    ?? [];

$paymentMethodBreakdown =
    $this->data['payment_method_breakdown']
    ?? [];

$projectBreakdown =
    $this->data['project_breakdown']
    ?? [];

$monthlyTrend =
    $this->data['monthly_trend']
    ?? [];


$totalFiltered =
    (float) (
        $summary['total_amount']
        ?? 0
    );

$purchasePercentage =
    $totalFiltered > 0
    ? (
        (float) (
            $summary['purchase_amount']
            ?? 0
        )
        / $totalFiltered
    ) * 100
    : 0;

$directPercentage =
    $totalFiltered > 0
    ? (
        (float) (
            $summary['direct_amount']
            ?? 0
        )
        / $totalFiltered
    ) * 100
    : 0;


$maxMonthlyAmount = 0;

foreach ($monthlyTrend as $month) {

    $monthTotal =
        (float) (
            $month['total_amount']
            ?? 0
        );

    if ($monthTotal > $maxMonthlyAmount) {
        $maxMonthlyAmount =
            $monthTotal;
    }
}


$formatMonth =
    static function (
        string $monthKey
    ): string {

        $parts =
            explode(
                '-',
                $monthKey
            );


        if (count($parts) !== 2) {
            return $monthKey;
        }


        $months = [
            '01' => 'Jan',
            '02' => 'Fev',
            '03' => 'Mar',
            '04' => 'Abr',
            '05' => 'Mai',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Ago',
            '09' => 'Set',
            '10' => 'Out',
            '11' => 'Nov',
            '12' => 'Dez',
        ];


        return (
            $months[$parts[1]]
            ?? $parts[1]
        )
            . '/'
            . $parts[0];
    };


$pagination =
    $this->data['pagination']
    ?? [
        'current_page' => 1,
        'total_pages' => 1,
        'total_records' => 0,
        'limit' => 20,
    ];

$currentPage =
    (int) (
        $pagination['current_page']
        ?? 1
    );

$totalPages =
    (int) (
        $pagination['total_pages']
        ?? 1
    );

$totalRecords =
    (int) (
        $pagination['total_records']
        ?? 0
    );


$buildPaginationUrl =
    static function (
        int $page
    ) use (
        $filters
    ): string {

        $params =
            array_filter(
                [
                    'page' => $page,

                    'project_id' =>
                    $filters['project_id']
                        ?? null,

                    'origin' =>
                    $filters['origin']
                        ?? null,

                    'category_key' =>
                    $filters['category_key']
                        ?? null,

                    'payment_method_id' =>
                    $filters['payment_method_id']
                        ?? null,

                    'date_start' =>
                    $filters['date_start']
                        ?? null,

                    'date_end' =>
                    $filters['date_end']
                        ?? null,

                    'search' =>
                    $filters['search']
                        ?? null,
                ],
                static fn($value): bool =>
                $value !== null
                    &&
                    $value !== ''
            );


        return
            $_ENV['URL_ADM']
            . 'list-financial-disbursements?'
            . http_build_query(
                $params
            );
    };


$getPaginationPages =
    static function (
        int $currentPage,
        int $totalPages
    ): array {

        if ($totalPages <= 1) {
            return [];
        }


        $start =
            max(
                1,
                $currentPage - 2
            );

        $end =
            min(
                $totalPages,
                $currentPage + 2
            );


        if ($currentPage <= 3) {
            $end =
                min(
                    $totalPages,
                    5
                );
        }


        if (
            $currentPage
            >= $totalPages - 2
        ) {
            $start =
                max(
                    1,
                    $totalPages - 4
                );
        }


        return range(
            $start,
            $end
        );
    };

?>

<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">

        <div>

            <h2 class="mt-3 mb-1">
                Desembolso de Obras
            </h2>

            <p class="text-muted mb-3">
                Visão consolidada do que efetivamente saiu do caixa.
            </p>

        </div>


        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">

                <a
                    href="<?= $_ENV['URL_ADM']; ?>dashboard"
                    class="text-decoration-none">

                    Dashboard

                </a>

            </li>

            <li
                class="breadcrumb-item active"
                aria-current="page">

                Desembolso de Obras

            </li>

        </ol>

    </div>


    <?php

    include './app/admsDaman/Views/partials/alerts.php';

    ?>


    <div class="card mb-3 border-light shadow">

        <div class="card-header">

            <i class="fa-solid fa-filter me-1"></i>

            Filtros de Pesquisa

        </div>


        <div class="card-body">

            <form
                method="GET"
                action="<?= $_ENV['URL_ADM']; ?>list-financial-disbursements"
                class="row g-3 align-items-end">


                <div class="col-xl-3 col-md-6">

                    <label
                        for="project_id"
                        class="form-label">

                        Obra

                    </label>

                    <select
                        name="project_id"
                        id="project_id"
                        class="form-select">

                        <option value="">
                            Todas as obras
                        </option>

                        <?php foreach ($projects as $project): ?>

                            <option
                                value="<?= (int) $project['id']; ?>"
                                <?= (
                                    (int) (
                                        $filters['project_id']
                                        ?? 0
                                    )
                                    ===
                                    (int) $project['id']
                                )
                                    ? 'selected'
                                    : ''; ?>>

                                <?= htmlspecialchars(
                                    $project['name']
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="col-xl-2 col-md-6">

                    <label
                        for="origin"
                        class="form-label">

                        Origem

                    </label>

                    <select
                        name="origin"
                        id="origin"
                        class="form-select">

                        <option value="">
                            Todas
                        </option>

                        <option
                            value="purchase"
                            <?= (
                                ($filters['origin'] ?? '')
                                === 'purchase'
                            )
                                ? 'selected'
                                : ''; ?>>

                            Compras

                        </option>

                        <option
                            value="financial_obligation"
                            <?= (
                                ($filters['origin'] ?? '')
                                === 'financial_obligation'
                            )
                                ? 'selected'
                                : ''; ?>>

                            Obrigações Financeiras

                        </option>

                        <option
                            value="direct"
                            <?= (
                                ($filters['origin'] ?? '')
                                === 'direct'
                            )
                                ? 'selected'
                                : ''; ?>>

                            Despesas Diretas

                        </option>

                    </select>

                </div>


                <div class="col-xl-2 col-md-6">

                    <label
                        for="category_key"
                        class="form-label">

                        Categoria

                    </label>

                    <select
                        name="category_key"
                        id="category_key"
                        class="form-select">

                        <option value="">
                            Todas
                        </option>

                        <option
                            value="purchase"
                            <?= (
                                ($filters['category_key'] ?? '')
                                === 'purchase'
                            )
                                ? 'selected'
                                : ''; ?>>

                            Compra

                        </option>

                        <?php foreach ($categories as $category): ?>

                            <?php

                            $categoryValue =
                                'direct:'
                                . (int) $category['id'];

                            ?>

                            <option
                                value="<?= htmlspecialchars(
                                            $categoryValue
                                        ); ?>"
                                <?= (
                                    ($filters['category_key'] ?? '')
                                    === $categoryValue
                                )
                                    ? 'selected'
                                    : ''; ?>>

                                <?= htmlspecialchars(
                                    $category['name']
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="col-xl-2 col-md-6">

                    <label
                        for="payment_method_id"
                        class="form-label">

                        Forma de Pagamento

                    </label>

                    <select
                        name="payment_method_id"
                        id="payment_method_id"
                        class="form-select">

                        <option value="">
                            Todas
                        </option>

                        <?php foreach (
                            $paymentMethods
                            as $paymentMethod
                        ): ?>

                            <option
                                value="<?= (int) $paymentMethod['id']; ?>"
                                <?= (
                                    (int) (
                                        $filters['payment_method_id']
                                        ?? 0
                                    )
                                    ===
                                    (int) $paymentMethod['id']
                                )
                                    ? 'selected'
                                    : ''; ?>>

                                <?= htmlspecialchars(
                                    $paymentMethod['name']
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="col-xl-3 col-md-6">

                    <label
                        for="search"
                        class="form-label">

                        Busca

                    </label>

                    <input
                        type="text"
                        name="search"
                        id="search"
                        class="form-control"
                        placeholder="Descrição, documento, fornecedor..."
                        value="<?= htmlspecialchars(
                                    $filters['search']
                                        ?? ''
                                ); ?>">

                </div>


                <div class="col-lg-2 col-md-6">

                    <label
                        for="date_start"
                        class="form-label">

                        Data de

                    </label>

                    <input
                        type="date"
                        name="date_start"
                        id="date_start"
                        class="form-control"
                        value="<?= htmlspecialchars(
                                    $filters['date_start']
                                        ?? ''
                                ); ?>">

                </div>


                <div class="col-lg-2 col-md-6">

                    <label
                        for="date_end"
                        class="form-label">

                        Data até

                    </label>

                    <input
                        type="date"
                        name="date_end"
                        id="date_end"
                        class="form-control"
                        value="<?= htmlspecialchars(
                                    $filters['date_end']
                                        ?? ''
                                ); ?>">

                </div>


                <div class="col-auto">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fa-solid fa-filter me-1"></i>

                        Filtrar

                    </button>

                </div>


                <div class="col-auto">

                    <a
                        href="<?= $_ENV['URL_ADM']; ?>list-financial-disbursements"
                        class="btn btn-secondary">

                        <i class="fa-solid fa-eraser me-1"></i>

                        Limpar

                    </a>

                </div>

            </form>

        </div>

    </div>


    <div class="row g-3 mb-4">

        <div class="col-xl-4 col-md-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div
                        class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small mb-1">
                                Total Desembolsado
                            </div>

                            <div class="fs-4 fw-bold">

                                R$
                                <?= number_format(
                                    (float) (
                                        $summary['total_amount']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </div>

                        </div>

                        <div class="fs-3 text-primary">

                            <i class="fa-solid fa-wallet"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-4 col-md-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div
                        class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small mb-1">
                                Pagamentos de Compras
                            </div>

                            <div class="fs-4 fw-bold">

                                R$
                                <?= number_format(
                                    (float) (
                                        $summary['purchase_amount']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </div>

                            <div class="small text-muted mt-1">

                                <?= number_format(
                                    $purchasePercentage,
                                    1,
                                    ',',
                                    '.'
                                ); ?>%
                                do total filtrado

                            </div>

                        </div>

                        <div class="fs-3 text-success">

                            <i class="fa-solid fa-file-invoice-dollar"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-4 col-md-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div
                        class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small mb-1">
                                Despesas Diretas
                            </div>

                            <div class="fs-4 fw-bold">

                                R$
                                <?= number_format(
                                    (float) (
                                        $summary['direct_amount']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </div>

                            <div class="small text-muted mt-1">

                                <?= number_format(
                                    $directPercentage,
                                    1,
                                    ',',
                                    '.'
                                ); ?>%
                                do total filtrado

                            </div>

                        </div>

                        <div class="fs-3 text-secondary">

                            <i class="fa-solid fa-money-bill-transfer"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- ====================================================== -->
    <!-- INDICADORES GERENCIAIS                                 -->
    <!-- ====================================================== -->

    <div class="row g-3 mb-4">

        <!-- Evolução mensal -->
        <div class="col-xl-7">

            <div class="card border-light shadow h-100">

                <div class="card-header">

                    <i class="fa-solid fa-chart-column me-1"></i>

                    Evolução Mensal

                </div>


                <div class="card-body">

                    <?php if (!empty($monthlyTrend)): ?>

                        <div class="d-flex flex-column gap-3">

                            <?php foreach ($monthlyTrend as $month): ?>

                                <?php

                                $monthTotal =
                                    (float) (
                                        $month['total_amount']
                                        ?? 0
                                    );

                                $monthWidth =
                                    $maxMonthlyAmount > 0
                                    ? (
                                        $monthTotal
                                        / $maxMonthlyAmount
                                    ) * 100
                                    : 0;

                                ?>

                                <div>

                                    <div
                                        class="d-flex
                                               justify-content-between
                                               align-items-center
                                               mb-1">

                                        <span class="fw-semibold">

                                            <?= htmlspecialchars(
                                                $formatMonth(
                                                    (string) (
                                                        $month['month_key']
                                                        ?? ''
                                                    )
                                                )
                                            ); ?>

                                        </span>


                                        <span class="fw-bold">

                                            R$
                                            <?= number_format(
                                                $monthTotal,
                                                2,
                                                ',',
                                                '.'
                                            ); ?>

                                        </span>

                                    </div>


                                    <div
                                        class="progress"
                                        role="progressbar"
                                        aria-valuenow="<?= round($monthWidth, 1); ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                        style="height: 8px;">

                                        <div
                                            class="progress-bar"
                                            style="width: <?= number_format(
                                                                $monthWidth,
                                                                2,
                                                                '.',
                                                                ''
                                                            ); ?>%">
                                        </div>

                                    </div>


                                    <div
                                        class="small text-muted
                                               d-flex gap-3
                                               mt-1">

                                        <span>

                                            Compras:
                                            R$
                                            <?= number_format(
                                                (float) (
                                                    $month['purchase_amount']
                                                    ?? 0
                                                ),
                                                2,
                                                ',',
                                                '.'
                                            ); ?>

                                        </span>

                                        <span>

                                            Diretas:
                                            R$
                                            <?= number_format(
                                                (float) (
                                                    $month['direct_amount']
                                                    ?? 0
                                                ),
                                                2,
                                                ',',
                                                '.'
                                            ); ?>

                                        </span>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="text-muted">
                            Nenhum dado disponível para a evolução mensal.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- Desembolso por categoria -->
        <div class="col-xl-5">

            <div class="card border-light shadow h-100">

                <div class="card-header">

                    <i class="fa-solid fa-chart-pie me-1"></i>

                    Composição por Categoria

                </div>


                <div class="card-body">

                    <?php if (!empty($categoryBreakdown)): ?>

                        <div class="d-flex flex-column gap-3">

                            <?php foreach (
                                $categoryBreakdown
                                as $category
                            ): ?>

                                <?php

                                $categoryAmount =
                                    (float) (
                                        $category['total_amount']
                                        ?? 0
                                    );

                                $categoryPercentage =
                                    $totalFiltered > 0
                                    ? (
                                        $categoryAmount
                                        / $totalFiltered
                                    ) * 100
                                    : 0;

                                ?>

                                <div>

                                    <div
                                        class="d-flex
                                               justify-content-between
                                               align-items-center">

                                        <span>

                                            <?= htmlspecialchars(
                                                $category['label']
                                                    ?? '-'
                                            ); ?>

                                        </span>

                                        <span class="fw-semibold">

                                            R$
                                            <?= number_format(
                                                $categoryAmount,
                                                2,
                                                ',',
                                                '.'
                                            ); ?>

                                        </span>

                                    </div>


                                    <div class="progress mt-1"
                                        style="height: 6px;">

                                        <div
                                            class="progress-bar"
                                            style="width: <?= number_format(
                                                                $categoryPercentage,
                                                                2,
                                                                '.',
                                                                ''
                                                            ); ?>%">
                                        </div>

                                    </div>


                                    <div
                                        class="small
                                               text-muted
                                               text-end
                                               mt-1">

                                        <?= number_format(
                                            $categoryPercentage,
                                            1,
                                            ',',
                                            '.'
                                        ); ?>%

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="text-muted">
                            Nenhuma categoria encontrada.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    <div class="row g-3 mb-4">

        <!-- Forma de pagamento -->
        <div class="col-xl-6">

            <div class="card border-light shadow h-100">

                <div class="card-header">

                    <i class="fa-solid fa-credit-card me-1"></i>

                    Desembolso por Forma de Pagamento

                </div>


                <div class="card-body">

                    <?php if (
                        !empty($paymentMethodBreakdown)
                    ): ?>

                        <div class="table-responsive">

                            <table
                                class="table
                                       table-sm
                                       align-middle
                                       mb-0">

                                <thead>

                                    <tr>

                                        <th>
                                            Forma
                                        </th>

                                        <th class="text-end">
                                            Valor
                                        </th>

                                        <th class="text-end">
                                            %
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php foreach (
                                        $paymentMethodBreakdown
                                        as $paymentMethod
                                    ): ?>

                                        <?php

                                        $paymentAmount =
                                            (float) (
                                                $paymentMethod['total_amount']
                                                ?? 0
                                            );

                                        $paymentPercentage =
                                            $totalFiltered > 0
                                            ? (
                                                $paymentAmount
                                                / $totalFiltered
                                            ) * 100
                                            : 0;

                                        ?>

                                        <tr>

                                            <td>

                                                <?= htmlspecialchars(
                                                    $paymentMethod['label']
                                                        ?? '-'
                                                ); ?>

                                            </td>

                                            <td
                                                class="text-end
                                                       fw-semibold">

                                                R$
                                                <?= number_format(
                                                    $paymentAmount,
                                                    2,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>

                                            <td
                                                class="text-end
                                                       text-muted">

                                                <?= number_format(
                                                    $paymentPercentage,
                                                    1,
                                                    ',',
                                                    '.'
                                                ); ?>%

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="text-muted">
                            Nenhuma forma de pagamento encontrada.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- Desembolso por obra -->
        <div class="col-xl-6">

            <div class="card border-light shadow h-100">

                <div class="card-header">

                    <i class="fa-solid fa-building me-1"></i>

                    Desembolso por Obra

                </div>


                <div class="card-body">

                    <?php if (!empty($projectBreakdown)): ?>

                        <div class="table-responsive">

                            <table
                                class="table
                                       table-sm
                                       align-middle
                                       mb-0">

                                <thead>

                                    <tr>

                                        <th>
                                            Obra
                                        </th>

                                        <th class="text-end">
                                            Valor
                                        </th>

                                        <th class="text-end">
                                            %
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php foreach (
                                        $projectBreakdown
                                        as $projectItem
                                    ): ?>

                                        <?php

                                        $projectAmount =
                                            (float) (
                                                $projectItem['total_amount']
                                                ?? 0
                                            );

                                        $projectPercentage =
                                            $totalFiltered > 0
                                            ? (
                                                $projectAmount
                                                / $totalFiltered
                                            ) * 100
                                            : 0;

                                        ?>

                                        <tr>

                                            <td>

                                                <?= htmlspecialchars(
                                                    $projectItem['label']
                                                        ?? '-'
                                                ); ?>

                                            </td>

                                            <td
                                                class="text-end
                                                       fw-semibold">

                                                R$
                                                <?= number_format(
                                                    $projectAmount,
                                                    2,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>

                                            <td
                                                class="text-end
                                                       text-muted">

                                                <?= number_format(
                                                    $projectPercentage,
                                                    1,
                                                    ',',
                                                    '.'
                                                ); ?>%

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="text-muted">
                            Nenhuma obra encontrada.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    <div class="card mb-4 border-light shadow">

        <div
            class="card-header d-flex
                   justify-content-between
                   align-items-center">

            <span>

                <i class="fa-solid fa-list me-1"></i>

                Movimentações

            </span>

            <span class="text-muted small">

                <?= number_format(
                    $totalRecords,
                    0,
                    ',',
                    '.'
                ); ?>
                linha(s)

            </span>

        </div>


        <div class="card-body">

            <?php if (!empty($entries)): ?>

                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0">

                        <thead>

                            <tr>

                                <th>
                                    Data
                                </th>

                                <th>
                                    Obra
                                </th>

                                <th>
                                    Origem
                                </th>

                                <th>
                                    Categoria
                                </th>

                                <th>
                                    Documento / Descrição
                                </th>

                                <th>
                                    Forma de Pagamento
                                </th>

                                <th class="text-end">
                                    Valor
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($entries as $entry): ?>

                                <?php

                                $isPurchase =
                                    ($entry['origin'] ?? '')
                                    === 'purchase';

                                ?>

                                <tr>

                                    <td class="text-nowrap">

                                        <?= !empty($entry['event_date'])
                                            ? date(
                                                'd/m/Y',
                                                strtotime(
                                                    $entry['event_date']
                                                )
                                            )
                                            : '-'; ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $entry['project_name']
                                                ?? '-'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?php if (($entry['origin'] ?? '') === 'purchase'): ?>

                                            <span class="badge bg-primary">

                                                <i
                                                    class="fa-solid
                       fa-cart-shopping
                       me-1">
                                                </i>

                                                Compra

                                            </span>

                                        <?php elseif (($entry['origin'] ?? '') === 'financial_obligation'): ?>

                                            <span class="badge bg-warning text-dark">

                                                <i
                                                    class="fa-solid
                       fa-file-invoice-dollar
                       me-1">
                                                </i>

                                                Obrigação Financeira

                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">

                                                <i
                                                    class="fa-solid
                       fa-money-bill-transfer
                       me-1">
                                                </i>

                                                Despesa Direta

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $entry['category_name']
                                                ?? '-'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?php if ($isPurchase): ?>

                                            <div class="fw-semibold">

                                                <?= htmlspecialchars(
                                                    (
                                                        $entry['document_type']
                                                        ?? 'Documento'
                                                    )
                                                        .
                                                        (
                                                            !empty($entry['document_number'])
                                                            ? ' '
                                                            . $entry['document_number']
                                                            : ''
                                                        )
                                                ); ?>

                                            </div>


                                            <div class="small text-muted">

                                                <?= htmlspecialchars(
                                                    $entry['counterparty']
                                                        ?? 'Fornecedor não informado'
                                                ); ?>

                                                <?php if (
                                                    !empty($entry['installment_number'])
                                                ): ?>

                                                    —
                                                    <?= (int)
                                                    $entry['installment_number']; ?>ª parcela

                                                <?php endif; ?>

                                            </div>

                                        <?php else: ?>

                                            <div class="fw-semibold">

                                                <?= htmlspecialchars(
                                                    $entry['description']
                                                        ?? '-'
                                                ); ?>

                                            </div>


                                            <?php if (
                                                !empty($entry['observation'])
                                            ): ?>

                                                <div class="small text-muted">

                                                    <?= htmlspecialchars(
                                                        $entry['observation']
                                                    ); ?>

                                                </div>

                                            <?php endif; ?>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $entry['payment_method_name']
                                                ?? 'Não informado'
                                        ); ?>

                                    </td>


                                    <td
                                        class="text-end
                                               fw-bold
                                               text-nowrap">

                                        R$
                                        <?= number_format(
                                            (float) (
                                                $entry['amount']
                                                ?? 0
                                            ),
                                            2,
                                            ',',
                                            '.'
                                        ); ?>


                                        <?php if (
                                            $isPurchase
                                            &&
                                            abs(
                                                (float) (
                                                    $entry['amount']
                                                    ?? 0
                                                )
                                                    -
                                                    (float) (
                                                        $entry['source_total_paid']
                                                        ?? 0
                                                    )
                                            ) > 0.004
                                        ): ?>

                                            <div
                                                class="small
                                                       text-muted
                                                       fw-normal"
                                                title="Valor apropriado proporcionalmente à obra">

                                                rateado

                                            </div>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <?php if ($totalPages > 1): ?>

                    <nav
                        class="mt-3"
                        aria-label="Paginação dos desembolsos">

                        <ul
                            class="pagination
                                   pagination-sm
                                   justify-content-end
                                   mb-0">

                            <li
                                class="page-item
                                <?= $currentPage <= 1
                                    ? 'disabled'
                                    : ''; ?>">

                                <a
                                    class="page-link"
                                    href="<?= $currentPage > 1
                                                ? htmlspecialchars(
                                                    $buildPaginationUrl(
                                                        $currentPage - 1
                                                    )
                                                )
                                                : '#'; ?>">

                                    Anterior

                                </a>

                            </li>


                            <?php foreach (
                                $getPaginationPages(
                                    $currentPage,
                                    $totalPages
                                )
                                as $pageNumber
                            ): ?>

                                <li
                                    class="page-item
                                    <?= $pageNumber === $currentPage
                                        ? 'active'
                                        : ''; ?>">

                                    <a
                                        class="page-link"
                                        href="<?= htmlspecialchars(
                                                    $buildPaginationUrl(
                                                        $pageNumber
                                                    )
                                                ); ?>">

                                        <?= $pageNumber; ?>

                                    </a>

                                </li>

                            <?php endforeach; ?>


                            <li
                                class="page-item
                                <?= $currentPage >= $totalPages
                                    ? 'disabled'
                                    : ''; ?>">

                                <a
                                    class="page-link"
                                    href="<?= $currentPage < $totalPages
                                                ? htmlspecialchars(
                                                    $buildPaginationUrl(
                                                        $currentPage + 1
                                                    )
                                                )
                                                : '#'; ?>">

                                    Próxima

                                </a>

                            </li>

                        </ul>

                    </nav>

                <?php endif; ?>


            <?php else: ?>

                <div class="alert alert-info mb-0">

                    <i class="fa-solid fa-circle-info me-1"></i>

                    Nenhum desembolso encontrado para os filtros informados.

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>