<?php

$directExpenses =
    $this->data['direct_expenses']
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

$pagination =
    $this->data['pagination']
    ?? [
        'current_page' => 1,
        'total_pages' => 1,
        'total_records' => 0,
        'limit' => 10,
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
                    'page' =>
                    $page,

                    'project_id' =>
                    $filters['project_id']
                        ?? null,

                    'category_id' =>
                    $filters['category_id']
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

                    'description' =>
                    $filters['description']
                        ?? null,
                ],
                static fn($value): bool =>
                $value !== null
                    &&
                    $value !== ''
            );


        return
            $_ENV['URL_ADM']
            . 'list-direct-expenses?'
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
                Despesas Diretas
            </h2>

            <p class="text-muted mb-3">
                Desembolsos realizados fora do fluxo de compras.
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

                Despesas Diretas

            </li>

        </ol>

    </div>


    <?php

    include './app/admsDaman/Views/partials/alerts.php';

    ?>


    <div class="card mb-3 border-light shadow">

        <div class="card-header hstack gap-2">

            <span>

                <i class="fa-solid fa-filter me-1"></i>

                Filtros

            </span>


            <span class="ms-auto">

                <a
                    href="<?= $_ENV['URL_ADM']; ?>create-direct-expense"
                    class="btn btn-primary btn-sm">

                    <i class="fa-solid fa-plus me-1"></i>

                    Nova Despesa

                </a>

            </span>

        </div>


        <div class="card-body">

            <form
                method="GET"
                action="<?= $_ENV['URL_ADM']; ?>list-direct-expenses"
                class="row g-3 align-items-end">


                <div class="col-lg-3 col-md-6">

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


                <div class="col-lg-3 col-md-6">

                    <label
                        for="category_id"
                        class="form-label">

                        Categoria

                    </label>

                    <select
                        name="category_id"
                        id="category_id"
                        class="form-select">

                        <option value="">
                            Todas
                        </option>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?= (int) $category['id']; ?>"
                                <?= (
                                    (int) (
                                        $filters['category_id']
                                        ?? 0
                                    )
                                    ===
                                    (int) $category['id']
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


                <div class="col-lg-3 col-md-6">

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

                        <?php foreach ($paymentMethods as $paymentMethod): ?>

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


                <div class="col-lg-3 col-md-6">

                    <label
                        for="description"
                        class="form-label">

                        Descrição

                    </label>

                    <input
                        type="text"
                        name="description"
                        id="description"
                        class="form-control"
                        placeholder="Pesquisar descrição..."
                        value="<?= htmlspecialchars(
                                    $filters['description']
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
                        href="<?= $_ENV['URL_ADM']; ?>list-direct-expenses"
                        class="btn btn-secondary">

                        <i class="fa-solid fa-eraser me-1"></i>

                        Limpar

                    </a>

                </div>

            </form>

        </div>

    </div>


    <div class="row g-3 mb-3">

        <div class="col-lg-4 col-md-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div
                        class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small mb-1">
                                Total das despesas filtradas
                            </div>

                            <div class="fs-4 fw-bold">

                                R$
                                <?= number_format(
                                    (float) (
                                        $this->data['total_amount']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </div>

                        </div>

                        <div class="fs-3 text-primary">

                            <i class="fa-solid fa-money-bill-wave"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-4 col-md-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div
                        class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small mb-1">
                                Quantidade de lançamentos
                            </div>

                            <div class="fs-4 fw-bold">

                                <?= number_format(
                                    $totalRecords,
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </div>

                        </div>

                        <div class="fs-3 text-secondary">

                            <i class="fa-solid fa-receipt"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="card mb-4 border-light shadow">

        <div class="card-header">

            <i class="fa-solid fa-list me-1"></i>

            Lançamentos

        </div>


        <div class="card-body">

            <?php if (!empty($directExpenses)): ?>

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
                                    Categoria
                                </th>

                                <th>
                                    Descrição
                                </th>

                                <th>
                                    Forma de Pagamento
                                </th>

                                <th class="text-end">
                                    Valor
                                </th>

                                <th class="text-center text-nowrap" style="width: 110px;">
                                    Ações
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($directExpenses as $expense): ?>

                                <tr>

                                    <td class="text-nowrap">

                                        <?= !empty($expense['expense_date'])
                                            ? date(
                                                'd/m/Y',
                                                strtotime(
                                                    $expense['expense_date']
                                                )
                                            )
                                            : '-'; ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $expense['project_name']
                                                ?? '-'
                                        ); ?>

                                    </td>


                                    <td>

                                        <span class="badge bg-secondary">

                                            <?= htmlspecialchars(
                                                $expense['category_name']
                                                    ?? '-'
                                            ); ?>

                                        </span>

                                    </td>


                                    <td>

                                        <div class="fw-semibold">

                                            <?= htmlspecialchars(
                                                $expense['description']
                                                    ?? '-'
                                            ); ?>

                                        </div>


                                        <?php if (!empty($expense['observation'])): ?>

                                            <div class="small text-muted">

                                                <?= htmlspecialchars(
                                                    $expense['observation']
                                                ); ?>

                                            </div>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $expense['payment_method_name']
                                                ?? '-'
                                        ); ?>

                                    </td>


                                    <td class="text-end fw-bold text-nowrap">

                                        R$
                                        <?= number_format(
                                            (float) (
                                                $expense['amount']
                                                ?? 0
                                            ),
                                            2,
                                            ',',
                                            '.'
                                        ); ?>

                                    </td>

                                    <td class="text-center text-nowrap">

                                        <div
                                            class="d-inline-flex align-items-center gap-1">

                                            <!-- EDITAR -->
                                            <a
                                                href="<?= $_ENV['URL_ADM']; ?>edit-direct-expense/<?= (int) $expense['id']; ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Editar despesa"
                                                aria-label="Editar despesa">

                                                <i class="fa-solid fa-pen"></i>

                                            </a>


                                            <!-- CANCELAR -->
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                title="Cancelar despesa"
                                                aria-label="Cancelar despesa"
                                                data-bs-toggle="modal"
                                                data-bs-target="#cancelDirectExpenseModal<?= (int) $expense['id']; ?>">

                                                <i class="fa-solid fa-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <!-- ====================================================== -->
                <!-- MODAIS DE CANCELAMENTO                                 -->
                <!-- ====================================================== -->

                <?php foreach ($directExpenses as $expense): ?>

                    <div
                        class="modal fade"
                        id="cancelDirectExpenseModal<?= (int) $expense['id']; ?>"
                        tabindex="-1"
                        aria-labelledby="cancelDirectExpenseModalLabel<?= (int) $expense['id']; ?>"
                        aria-hidden="true">

                        <div class="modal-dialog modal-dialog-centered">

                            <div class="modal-content">


                                <!-- ================================================== -->
                                <!-- CABEÇALHO                                          -->
                                <!-- ================================================== -->

                                <div class="modal-header">

                                    <h5
                                        class="modal-title"
                                        id="cancelDirectExpenseModalLabel<?= (int) $expense['id']; ?>">

                                        <i
                                            class="fa-solid
                               fa-triangle-exclamation
                               text-danger
                               me-1">
                                        </i>

                                        Cancelar Despesa Direta

                                    </h5>


                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Fechar">
                                    </button>

                                </div>


                                <!-- ================================================== -->
                                <!-- FORMULÁRIO                                         -->
                                <!-- ================================================== -->

                                <form
                                    method="POST"
                                    action="<?= $_ENV['URL_ADM']; ?>cancel-direct-expense/<?= (int) $expense['id']; ?>">


                                    <div class="modal-body">


                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars(
                                                        \App\admsDaman\Helpers\CSRFHelper::generateCSRFToken(
                                                            'form_cancel_direct_expense_'
                                                                . (int) $expense['id']
                                                        )
                                                    ); ?>">


                                        <!-- ================================================== -->
                                        <!-- AVISO                                              -->
                                        <!-- ================================================== -->

                                        <div class="alert alert-warning">

                                            <div class="fw-semibold mb-1">

                                                Esta ação retirará a despesa dos totais
                                                e relatórios ativos.

                                            </div>

                                            <div class="small">

                                                O lançamento não será apagado fisicamente
                                                e permanecerá registrado para fins de histórico.

                                            </div>

                                        </div>


                                        <!-- ================================================== -->
                                        <!-- DADOS                                              -->
                                        <!-- ================================================== -->

                                        <div class="border rounded p-3 mb-3">

                                            <div class="row g-2">


                                                <div class="col-12">

                                                    <div class="text-muted small">
                                                        Descrição
                                                    </div>

                                                    <div class="fw-semibold">

                                                        <?= htmlspecialchars(
                                                            $expense['description']
                                                                ?? '-'
                                                        ); ?>

                                                    </div>

                                                </div>


                                                <div class="col-md-6">

                                                    <div class="text-muted small">
                                                        Obra
                                                    </div>

                                                    <div>

                                                        <?= htmlspecialchars(
                                                            $expense['project_name']
                                                                ?? '-'
                                                        ); ?>

                                                    </div>

                                                </div>


                                                <div class="col-md-6">

                                                    <div class="text-muted small">
                                                        Data
                                                    </div>

                                                    <div>

                                                        <?= !empty($expense['expense_date'])
                                                            ? date(
                                                                'd/m/Y',
                                                                strtotime(
                                                                    $expense['expense_date']
                                                                )
                                                            )
                                                            : '-'; ?>

                                                    </div>

                                                </div>


                                                <div class="col-12">

                                                    <div class="text-muted small">
                                                        Valor
                                                    </div>

                                                    <div class="fw-bold">

                                                        R$
                                                        <?= number_format(
                                                            (float) (
                                                                $expense['amount']
                                                                ?? 0
                                                            ),
                                                            2,
                                                            ',',
                                                            '.'
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>


                                        <!-- ================================================== -->
                                        <!-- MOTIVO                                             -->
                                        <!-- ================================================== -->

                                        <div>

                                            <label
                                                for="cancellation_reason_<?= (int) $expense['id']; ?>"
                                                class="form-label">

                                                Motivo do cancelamento

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>


                                            <textarea
                                                class="form-control"
                                                id="cancellation_reason_<?= (int) $expense['id']; ?>"
                                                name="cancellation_reason"
                                                rows="3"
                                                maxlength="500"
                                                placeholder="Informe por que este lançamento está sendo cancelado..."
                                                required></textarea>


                                            <div class="form-text">

                                                O motivo ficará registrado junto ao lançamento.

                                            </div>

                                        </div>

                                    </div>


                                    <!-- ================================================== -->
                                    <!-- AÇÕES                                              -->
                                    <!-- ================================================== -->

                                    <div class="modal-footer">

                                        <button
                                            type="button"
                                            class="btn btn-secondary"
                                            data-bs-dismiss="modal">

                                            Voltar

                                        </button>


                                        <button
                                            type="submit"
                                            class="btn btn-danger">

                                            <i
                                                class="fa-solid
                                   fa-ban
                                   me-1">
                                            </i>

                                            Confirmar Cancelamento

                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>


                <?php if ($totalPages > 1): ?>

                    <nav
                        class="mt-3"
                        aria-label="Paginação das despesas diretas">

                        <ul
                            class="pagination pagination-sm justify-content-end mb-0">

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

                <div
                    class="alert alert-info mb-0"
                    role="alert">

                    <i class="fa-solid fa-circle-info me-1"></i>

                    Nenhuma despesa direta encontrada.

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>