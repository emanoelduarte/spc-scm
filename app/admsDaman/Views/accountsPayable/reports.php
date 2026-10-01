<?php

$filters =
    $this->data['filters']
    ?? [];


$completeExportQuery =
    http_build_query(
        array_filter(
            [
                'project_id' =>
                $filters['project_id']
                    ?? null,

                'supplier_key' =>
                $filters['supplier_key']
                    ?? null,

                'due_date_start' =>
                $filters['due_date_start']
                    ?? null,

                'due_date_end' =>
                $filters['due_date_end']
                    ?? null,
            ],
            static fn($value): bool =>
            $value !== null
                &&
                $value !== ''
        )
    );


$projects =
    $this->data['projects']
    ?? [];

$suppliers =
    $this->data['suppliers']
    ?? [];

$reportByProject =
    $this->data['reportByProject']
    ?? [];

$reportBySupplier =
    $this->data['reportBySupplier']
    ?? [];

$totalOpenAmount =
    (float) (
        $this->data['totalOpenAmount']
        ?? 0
    );

$totalOpenAmountBySupplier =
    (float) (
        $this->data['totalOpenAmountBySupplier']
        ?? 0
    );

$projectsCount =
    (int) (
        $this->data['projectsCount']
        ?? 0
    );

$suppliersCount =
    (int) (
        $this->data['suppliersCount']
        ?? 0
    );

$periodLabel =
    $this->data['periodLabel']
    ?? 'Todo o período';

$reportTotalsMatch =
    (bool) (
        $this->data['reportTotalsMatch']
        ?? true
    );


/*
 * ============================================================
 * FORMATADORES
 * ============================================================
 */
$formatMoney =
    static function (
        float|string|int|null $value
    ): string {

        return
            'R$ '
            .
            number_format(
                (float) $value,
                2,
                ',',
                '.'
            );
    };


$formatCnpj =
    static function (
        ?string $cnpj
    ): string {

        $cnpj =
            preg_replace(
                '/\D/',
                '',
                (string) $cnpj
            );


        if (strlen($cnpj) !== 14) {
            return $cnpj;
        }


        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );
    };


/*
 * ============================================================
 * ATALHOS DE PERÍODO
 * ============================================================
 */
$today =
    new DateTimeImmutable(
        'today'
    );


$monday =
    $today->modify(
        'monday this week'
    );


$sunday =
    $monday->modify(
        '+6 days'
    );


$nextSevenDays =
    $today->modify(
        '+7 days'
    );


$firstDayMonth =
    $today->modify(
        'first day of this month'
    );


$lastDayMonth =
    $today->modify(
        'last day of this month'
    );


/*
 * Manter obra e fornecedor ao utilizar
 * os atalhos de período.
 */
$baseQuickFilters = [];

if (!empty($filters['project_id'])) {

    $baseQuickFilters['project_id'] =
        (int) $filters['project_id'];
}

if (!empty($filters['supplier_key'])) {

    $baseQuickFilters['supplier_key'] =
        (string) $filters['supplier_key'];
}


$currentWeekQuery =
    http_build_query(
        array_merge(
            $baseQuickFilters,
            [
                'due_date_start' =>
                $monday->format('Y-m-d'),

                'due_date_end' =>
                $sunday->format('Y-m-d'),
            ]
        )
    );


$nextSevenDaysQuery =
    http_build_query(
        array_merge(
            $baseQuickFilters,
            [
                'due_date_start' =>
                $today->format('Y-m-d'),

                'due_date_end' =>
                $nextSevenDays->format('Y-m-d'),
            ]
        )
    );


$currentMonthQuery =
    http_build_query(
        array_merge(
            $baseQuickFilters,
            [
                'due_date_start' =>
                $firstDayMonth->format('Y-m-d'),

                'due_date_end' =>
                $lastDayMonth->format('Y-m-d'),
            ]
        )
    );

?>


<div class="container-fluid px-4">


    <!-- ====================================================== -->
    <!-- TÍTULO / BREADCRUMB                                    -->
    <!-- ====================================================== -->

    <div
        class="mb-1
               d-flex
               flex-column
               flex-sm-row
               gap-2">

        <div>

            <h2 class="mt-3 mb-1">

                Relatórios - Contas a Pagar

            </h2>

            <p class="text-muted mb-3">

                Resumo das obrigações financeiras
                por obra e fornecedor

            </p>

        </div>


        <ol
            class="breadcrumb
                   mb-3
                   mt-0
                   mt-sm-3
                   ms-auto">

            <li class="breadcrumb-item">

                <a
                    class="text-decoration-none"
                    href="<?= $_ENV['URL_ADM']; ?>dashboard">

                    Dashboard

                </a>

            </li>

            <li class="breadcrumb-item">

                <a
                    class="text-decoration-none"
                    href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents">

                    Contas a Pagar

                </a>

            </li>

            <li
                class="breadcrumb-item active"
                aria-current="page">

                Relatórios

            </li>

        </ol>

    </div>


    <?php

    include
        './app/admsDaman/Views/partials/alerts.php';

    ?>


    <!-- ====================================================== -->
    <!-- FILTROS                                                -->
    <!-- ====================================================== -->

    <div class="card mb-3 border-light shadow">

        <div
            class="card-header
           d-flex
           flex-wrap
           justify-content-between
           align-items-center
           gap-2">

            <div>

                <i class="fa-solid fa-filter me-1"></i>

                Filtros do Relatório

            </div>


            <div class="d-flex gap-2">

                <a
                    href="<?= $_ENV['URL_ADM']; ?>export-accounts-payable-complete-pdf<?= $completeExportQuery !== ''
                                                                                            ? '?'
                                                                                            . htmlspecialchars(
                                                                                                $completeExportQuery
                                                                                            )
                                                                                            : ''; ?>"
                    class="btn btn-sm btn-outline-danger">

                    <i class="fa-solid fa-file-pdf me-1"></i>

                    PDF Completo

                </a>


                <a
                    href="<?= $_ENV['URL_ADM']; ?>export-accounts-payable-complete-excel<?= $completeExportQuery !== ''
                                                                                            ? '?'
                                                                                            . htmlspecialchars(
                                                                                                $completeExportQuery
                                                                                            )
                                                                                            : ''; ?>"
                    class="btn btn-sm btn-outline-success">

                    <i class="fa-solid fa-file-excel me-1"></i>

                    Excel Completo

                </a>

            </div>

        </div>


        <div class="card-body">

            <form
                method="GET"
                action="<?= $_ENV['URL_ADM']; ?>reports-accounts-payable"
                class="row g-3 align-items-end">


                <!-- Obra -->
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


                        <?php foreach (
                            $projects as $project
                        ): ?>

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


                <!-- Fornecedor -->
                <div class="col-xl-3 col-md-6">

                    <label
                        for="supplier_key"
                        class="form-label">

                        Fornecedor

                    </label>


                    <select
                        name="supplier_key"
                        id="supplier_key"
                        class="form-select">

                        <option value="">

                            Todos os fornecedores

                        </option>


                        <?php foreach (
                            $suppliers as $supplier
                        ): ?>

                            <option
                                value="<?= htmlspecialchars(
                                            $supplier['supplier_key']
                                        ); ?>"
                                <?= (
                                    (
                                        $filters['supplier_key']
                                        ?? ''
                                    )
                                    ===
                                    $supplier['supplier_key']
                                )
                                    ? 'selected'
                                    : ''; ?>>

                                <?= htmlspecialchars(
                                    $supplier['supplier_name']
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Vencimento inicial -->
                <div class="col-xl-2 col-md-6">

                    <label
                        for="due_date_start"
                        class="form-label">

                        Vencimento de

                    </label>

                    <input
                        type="date"
                        name="due_date_start"
                        id="due_date_start"
                        class="form-control"
                        value="<?= htmlspecialchars(
                                    $filters['due_date_start']
                                        ?? ''
                                ); ?>">

                </div>


                <!-- Vencimento final -->
                <div class="col-xl-2 col-md-6">

                    <label
                        for="due_date_end"
                        class="form-label">

                        Vencimento até

                    </label>

                    <input
                        type="date"
                        name="due_date_end"
                        id="due_date_end"
                        class="form-control"
                        value="<?= htmlspecialchars(
                                    $filters['due_date_end']
                                        ?? ''
                                ); ?>">

                </div>


                <!-- Filtrar -->
                <div class="col-auto">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i
                            class="fa-solid
                                   fa-filter
                                   me-1">
                        </i>

                        Filtrar

                    </button>

                </div>


                <!-- Limpar -->
                <div class="col-auto">

                    <a
                        href="<?= $_ENV['URL_ADM']; ?>reports-accounts-payable"
                        class="btn btn-secondary">

                        <i
                            class="fa-solid
                                   fa-eraser
                                   me-1">
                        </i>

                        Limpar

                    </a>

                </div>


                <!-- Atalhos -->
                <div class="col-12">

                    <div
                        class="d-flex
                               flex-wrap
                               gap-2
                               align-items-center">

                        <span class="text-muted small me-1">

                            Períodos rápidos:

                        </span>


                        <a
                            href="<?= $_ENV['URL_ADM']; ?>reports-accounts-payable?<?= htmlspecialchars(
                                                                                        $currentWeekQuery
                                                                                    ); ?>"
                            class="btn btn-sm btn-outline-primary">

                            Esta semana

                        </a>


                        <a
                            href="<?= $_ENV['URL_ADM']; ?>reports-accounts-payable?<?= htmlspecialchars(
                                                                                        $nextSevenDaysQuery
                                                                                    ); ?>"
                            class="btn btn-sm btn-outline-primary">

                            Próximos 7 dias

                        </a>


                        <a
                            href="<?= $_ENV['URL_ADM']; ?>reports-accounts-payable?<?= htmlspecialchars(
                                                                                        $currentMonthQuery
                                                                                    ); ?>"
                            class="btn btn-sm btn-outline-primary">

                            Este mês

                        </a>


                        <a
                            href="<?= $_ENV['URL_ADM']; ?>reports-accounts-payable<?= !empty($baseQuickFilters)
                                                                                        ? '?'
                                                                                        . htmlspecialchars(
                                                                                            http_build_query(
                                                                                                $baseQuickFilters
                                                                                            )
                                                                                        )
                                                                                        : ''; ?>"
                            class="btn btn-sm btn-outline-secondary">

                            Todo o período

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- RESUMO                                                 -->
    <!-- ====================================================== -->

    <div class="row g-3 mb-4">


        <!-- Total devido -->
        <div class="col-xl-4 col-md-6">

            <div
                class="card
                       shadow-sm
                       border-0
                       h-100">

                <div class="card-body">

                    <div
                        class="d-flex
                               justify-content-between
                               align-items-center">

                        <div>

                            <div class="text-muted small mb-1">

                                Total Devido

                            </div>

                            <div class="fs-4 fw-bold">

                                <?= $formatMoney(
                                    $totalOpenAmount
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


        <!-- Obras -->
        <div class="col-xl-4 col-md-6">

            <div
                class="card
                       shadow-sm
                       border-0
                       h-100">

                <div class="card-body">

                    <div
                        class="d-flex
                               justify-content-between
                               align-items-center">

                        <div>

                            <div class="text-muted small mb-1">

                                Obras com saldo devido

                            </div>

                            <div class="fs-4 fw-bold">

                                <?= $projectsCount; ?>

                            </div>

                        </div>


                        <div class="fs-3 text-success">

                            <i
                                class="fa-solid
                                       fa-building">
                            </i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Fornecedores -->
        <div class="col-xl-4 col-md-6">

            <div
                class="card
                       shadow-sm
                       border-0
                       h-100">

                <div class="card-body">

                    <div
                        class="d-flex
                               justify-content-between
                               align-items-center">

                        <div>

                            <div class="text-muted small mb-1">

                                Fornecedores com saldo devido

                            </div>

                            <div class="fs-4 fw-bold">

                                <?= $suppliersCount; ?>

                            </div>

                        </div>


                        <div class="fs-3 text-warning">

                            <i
                                class="fa-solid
                                       fa-truck">
                            </i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- IDENTIFICAÇÃO DO PERÍODO                               -->
    <!-- ====================================================== -->

    <div
        class="card border-light shadow-sm mb-3">

        <div
            class="card-body py-2
               d-flex flex-wrap
               justify-content-between
               align-items-center gap-2">

            <div>
                <i class="fa-regular fa-calendar me-1"></i>

                <strong>Período:</strong>

                <?= htmlspecialchars($periodLabel); ?>
            </div>

            <div class="text-muted small">
                Valores representam o saldo ainda devido
            </div>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- VERIFICAÇÃO TEMPORÁRIA DE INTEGRIDADE                  -->
    <!-- ====================================================== -->

    <?php if (!$reportTotalsMatch): ?>

        <div class="alert alert-warning">

            <i
                class="fa-solid
                       fa-triangle-exclamation
                       me-1">
            </i>

            <strong>
                Atenção:
            </strong>

            os totais agrupados por obra e fornecedor
            não estão coincidentes.

            Obra:
            <strong>
                <?= $formatMoney(
                    $totalOpenAmount
                ); ?>
            </strong>

            |

            Fornecedor:
            <strong>
                <?= $formatMoney(
                    $totalOpenAmountBySupplier
                ); ?>
            </strong>

        </div>

    <?php endif; ?>


    <!-- ====================================================== -->
    <!-- RELATÓRIO POR OBRA                                     -->
    <!-- ====================================================== -->

    <div class="card mb-4 border-light shadow">

        <div
            class="card-header
                   d-flex
                   flex-wrap
                   justify-content-between
                   align-items-center
                   gap-2">

            <div>

                <i
                    class="fa-solid
                           fa-building
                           me-1">
                </i>

                Valor Devido por Obra

            </div>

            <?php

            $pdfProjectQuery =
                http_build_query(
                    array_filter(
                        [
                            'group' =>
                            'project',

                            'project_id' =>
                            $filters['project_id']
                                ?? null,

                            'supplier_key' =>
                            $filters['supplier_key']
                                ?? null,

                            'due_date_start' =>
                            $filters['due_date_start']
                                ?? null,

                            'due_date_end' =>
                            $filters['due_date_end']
                                ?? null,
                        ],
                        static fn($value): bool =>
                        $value !== null
                            &&
                            $value !== ''
                    )
                );

            ?>


            <!--
                Os botões serão ativados quando
                implementarmos as exportações.
            -->
            <div class="d-flex gap-2">

                <a
                    href="<?= $_ENV['URL_ADM']; ?>export-accounts-payable-pdf?<?= htmlspecialchars(
                                                                                    $pdfProjectQuery
                                                                                ); ?>"
                    class="btn btn-sm btn-outline-danger">

                    <i class="fa-solid fa-file-pdf me-1"> </i>

                    PDF

                </a>


                <?php

                $excelProjectQuery =
                    http_build_query(
                        array_filter(
                            [
                                'group' =>
                                'project',

                                'project_id' =>
                                $filters['project_id']
                                    ?? null,

                                'supplier_key' =>
                                $filters['supplier_key']
                                    ?? null,

                                'due_date_start' =>
                                $filters['due_date_start']
                                    ?? null,

                                'due_date_end' =>
                                $filters['due_date_end']
                                    ?? null,
                            ],
                            static fn($value): bool =>
                            $value !== null
                                &&
                                $value !== ''
                        )
                    );

                ?>


                <a
                    href="<?= $_ENV['URL_ADM']; ?>export-accounts-payable-excel?<?= htmlspecialchars(
                                                                                    $excelProjectQuery
                                                                                ); ?>"
                    class="btn btn-sm btn-outline-success">

                    <i class="fa-solid fa-file-excel me-1"> </i>

                    Excel

                </a>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table
                           table-hover
                           align-middle
                           mb-0">

                    <thead>

                        <tr>

                            <th class="ps-3">

                                Obra

                            </th>

                            <th class="text-center">

                                Lançamentos

                            </th>

                            <th class="text-center">

                                Parcelas

                            </th>

                            <th class="text-end pe-3">

                                Valor Devido

                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (
                            !empty($reportByProject)
                        ): ?>


                            <?php foreach (
                                $reportByProject
                                as $project
                            ): ?>

                                <tr>

                                    <td class="ps-3">

                                        <strong>

                                            <?= htmlspecialchars(
                                                $project['project_name']
                                                    ?? '-'
                                            ); ?>

                                        </strong>

                                    </td>


                                    <td class="text-center">

                                        <?= (int) (
                                            $project['documents_count']
                                            ?? 0
                                        ); ?>

                                    </td>


                                    <td class="text-center">

                                        <?= (int) (
                                            $project['installments_count']
                                            ?? 0
                                        ); ?>

                                    </td>


                                    <td
                                        class="text-end
                                               pe-3
                                               fw-semibold">

                                        <?= $formatMoney(
                                            $project['open_amount']
                                                ?? 0
                                        ); ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="4"
                                    class="text-center
                                           text-muted
                                           py-4">

                                    Nenhum valor devido encontrado
                                    para os filtros selecionados.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>


                    <?php if (
                        !empty($reportByProject)
                    ): ?>

                        <tfoot>

                            <tr class="fw-bold">

                                <td
                                    colspan="3"
                                    class="text-end">

                                    TOTAL

                                </td>

                                <td
                                    class="text-end
                                           pe-3">

                                    <?= $formatMoney(
                                        $totalOpenAmount
                                    ); ?>

                                </td>

                            </tr>

                        </tfoot>

                    <?php endif; ?>

                </table>

            </div>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- RELATÓRIO POR FORNECEDOR                               -->
    <!-- ====================================================== -->

    <div class="card mb-4 border-light shadow">

        <div
            class="card-header
                   d-flex
                   flex-wrap
                   justify-content-between
                   align-items-center
                   gap-2">

            <div>

                <i
                    class="fa-solid
                           fa-truck
                           me-1">
                </i>

                Valor Devido por Fornecedor

            </div>

            <?php

            $pdfSupplierQuery =
                http_build_query(
                    array_filter(
                        [
                            'group' =>
                            'supplier',

                            'project_id' =>
                            $filters['project_id']
                                ?? null,

                            'supplier_key' =>
                            $filters['supplier_key']
                                ?? null,

                            'due_date_start' =>
                            $filters['due_date_start']
                                ?? null,

                            'due_date_end' =>
                            $filters['due_date_end']
                                ?? null,
                        ],
                        static fn($value): bool =>
                        $value !== null
                            &&
                            $value !== ''
                    )
                );

            ?>


            <div class="d-flex gap-2">

                <a
                    href="<?= $_ENV['URL_ADM']; ?>export-accounts-payable-pdf?<?= htmlspecialchars(
                                                                                    $pdfSupplierQuery
                                                                                ); ?>"
                    class="btn btn-sm btn-outline-danger">

                    <i class="fa-solid fa-file-pdf me-1">
                    </i>

                    PDF

                </a>

                <?php

                $excelSupplierQuery =
                    http_build_query(
                        array_filter(
                            [
                                'group' =>
                                'supplier',

                                'project_id' =>
                                $filters['project_id']
                                    ?? null,

                                'supplier_key' =>
                                $filters['supplier_key']
                                    ?? null,

                                'due_date_start' =>
                                $filters['due_date_start']
                                    ?? null,

                                'due_date_end' =>
                                $filters['due_date_end']
                                    ?? null,
                            ],
                            static fn($value): bool =>
                            $value !== null
                                &&
                                $value !== ''
                        )
                    );

                ?>


                <a
                    href="<?= $_ENV['URL_ADM']; ?>export-accounts-payable-excel?<?= htmlspecialchars(
                                                                                    $excelSupplierQuery
                                                                                ); ?>"
                    class="btn btn-sm btn-outline-success">

                    <i class="fa-solid fa-file-excel me-1"> </i>

                    Excel

                </a>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table
                           table-hover
                           align-middle
                           mb-0">

                    <thead>

                        <tr>

                            <th class="ps-3">

                                Fornecedor

                            </th>

                            <th>

                                CNPJ

                            </th>

                            <th class="text-center">

                                Lançamentos

                            </th>

                            <th class="text-center">

                                Parcelas

                            </th>

                            <th class="text-end pe-3">

                                Valor Devido

                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (
                            !empty($reportBySupplier)
                        ): ?>


                            <?php foreach (
                                $reportBySupplier
                                as $supplier
                            ): ?>

                                <tr>

                                    <td class="ps-3">

                                        <strong>

                                            <?= htmlspecialchars(
                                                $supplier['supplier_name']
                                                    ?? 'Fornecedor não identificado'
                                            ); ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?php

                                        $supplierTaxId =
                                            $supplier['supplier_tax_id']
                                            ?? null;

                                        ?>

                                        <?= !empty($supplierTaxId)
                                            ? htmlspecialchars(
                                                $formatCnpj(
                                                    $supplierTaxId
                                                )
                                            )
                                            : '<span class="text-muted">-</span>'; ?>

                                    </td>


                                    <td class="text-center">

                                        <?= (int) (
                                            $supplier['documents_count']
                                            ?? 0
                                        ); ?>

                                    </td>


                                    <td class="text-center">

                                        <?= (int) (
                                            $supplier['installments_count']
                                            ?? 0
                                        ); ?>

                                    </td>


                                    <td
                                        class="text-end
                                               pe-3
                                               fw-semibold">

                                        <?= $formatMoney(
                                            $supplier['open_amount']
                                                ?? 0
                                        ); ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center
                                           text-muted
                                           py-4">

                                    Nenhum valor devido encontrado
                                    para os filtros selecionados.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>


                    <?php if (
                        !empty($reportBySupplier)
                    ): ?>

                        <tfoot>

                            <tr class="fw-bold">

                                <td
                                    colspan="4"
                                    class="text-end">

                                    TOTAL

                                </td>

                                <td
                                    class="text-end
                                           pe-3">

                                    <?= $formatMoney(
                                        $totalOpenAmountBySupplier
                                    ); ?>

                                </td>

                            </tr>

                        </tfoot>

                    <?php endif; ?>

                </table>

            </div>

        </div>

    </div>


</div>