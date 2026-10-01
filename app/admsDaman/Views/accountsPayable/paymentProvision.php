<?php

$filters =
    $this->data['filters']
    ?? [];

$projects =
    $this->data['projects']
    ?? [];

$suppliers =
    $this->data['suppliers']
    ?? [];

$provision =
    $this->data['provision']
    ?? [];

$weeks =
    $provision['weeks']
    ?? [];

$totalProvision =
    (float) (
        $provision['total_provision']
        ?? 0
    );

$excludedAmount =
    (float) (
        $provision['excluded_amount']
        ?? 0
    );

$grossProjectedAmount =
    (float) (
        $provision['gross_projected_amount']
        ?? 0
    );

$includedSuppliersCount =
    (int) (
        $provision['included_suppliers_count']
        ?? 0
    );

$excludedSuppliersCount =
    (int) (
        $provision['excluded_suppliers_count']
        ?? 0
    );

$excludedSuppliers =
    $provision['excluded_suppliers']
    ?? [];

$excludedItems =
    $provision['excluded_items']
    ?? [];

$projectionPeriodLabel =
    $this->data['projectionPeriodLabel']
    ?? '';

$financialWeekLabel =
    $this->data['financialWeekLabel']
    ?? 'Terça-feira a Segunda-feira';


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


$formatDate =
    static function (
        ?string $date
    ): string {

        if (empty($date)) {
            return '-';
        }

        $dateObject =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $date
            );

        return
            $dateObject !== false
            ? $dateObject->format(
                'd/m/Y'
            )
            : $date;
    };


$excludedSupplierKeys =
    $filters['excluded_supplier_keys']
    ?? [];

$provisionExportQuery =
    http_build_query(
        array_filter(
            [
                'reference_date' =>
                $filters['reference_date']
                    ?? null,

                'weeks' =>
                $filters['weeks']
                    ?? 4,

                'project_id' =>
                $filters['project_id']
                    ?? null,

                'excluded_supplier_keys' =>
                $filters['excluded_supplier_keys']
                    ?? [],
            ],
            static fn(
                mixed $value
            ): bool =>
            $value !== null
                &&
                $value !== ''
                &&
                $value !== []
        )
    );
?>

<style>
    .provision-table {
        width: 100%;
        min-width: 1200px;
        table-layout: fixed;
    }

    .provision-table th,
    .provision-table td {
        vertical-align: middle;
    }

    .provision-table .col-project {
        width: 15%;
    }

    .provision-table .col-document {
        width: 10%;
    }

    .provision-table .col-supplier {
        width: 25%;
    }

    .provision-table .col-purchase-date {
        width: 11%;
    }

    .provision-table .col-document-value {
        width: 12%;
    }

    .provision-table .col-due-date {
        width: 11%;
    }

    .provision-table .col-installment {
        width: 16%;
    }

    .provision-table .supplier-cell {
        white-space: normal;
        overflow-wrap: break-word;
    }

    .excluded-provision-table {
        width: 100%;
        table-layout: fixed;
    }

    .excluded-provision-table .col-project {
        width: 21%;
    }

    .excluded-provision-table .col-document {
        width: 12%;
    }

    .excluded-provision-table .col-supplier {
        width: 40%;
    }

    .excluded-provision-table .col-due-date {
        width: 13%;
    }

    .excluded-provision-table .col-value {
        width: 14%;
    }
</style>

<div class="container-fluid px-4">


    <!-- ====================================================== -->
    <!-- TÍTULO                                                 -->
    <!-- ====================================================== -->

    <div
        class="mb-1
               d-flex
               flex-column
               flex-sm-row
               gap-2">

        <div>

            <h2 class="mt-3 mb-1">

                Provisão de Pagamentos

            </h2>

            <p class="text-muted mb-3">

                Projeção semanal das obrigações financeiras
                e necessidade futura de caixa

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
                    href="<?= $_ENV['URL_ADM']; ?>dashboard"
                    class="text-decoration-none">

                    Dashboard

                </a>

            </li>

            <li
                class="breadcrumb-item active"
                aria-current="page">

                Provisão

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

    <!-- ====================================================== -->
    <!-- FILTROS                                                -->
    <!-- ====================================================== -->

    <style>
        /*
     * ============================================================
     * FILTROS DA PROVISÃO
     * ============================================================
     */

        .provision-filter-control,
        .provision-supplier-dropdown {
            height: 48px;
            min-height: 48px;
        }


        /*
     * Dropdown de fornecedores.
     */
        .provision-supplier-dropdown {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }


        /*
     * Reserva a mesma altura para os textos auxiliares.
     */
        .provision-filter-help {
            min-height: 20px;
            margin-top: 0.35rem;
        }


        /*
     * Área dos botões.
     */
        .provision-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
        }


        .provision-action-btn {
            height: 48px;
            min-height: 48px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            white-space: nowrap;
        }


        /*
     * O botão principal ocupa o espaço restante.
     */
        .provision-action-primary {
            flex: 1 1 auto;
        }


        /*
     * Mobile.
     */
        @media (max-width: 575.98px) {

            .provision-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .provision-action-btn {
                width: 100%;
            }
        }
    </style>


    <div class="card mb-3 border-light shadow">


        <!-- ====================================================== -->
        <!-- CABEÇALHO DO CARD                                     -->
        <!-- ====================================================== -->

        <div
            class="card-header
               d-flex
               flex-wrap
               justify-content-between
               align-items-center
               gap-2">

            <div>

                <i class="fa-solid fa-filter me-1"></i>

                Parâmetros da Provisão

            </div>


            <div class="d-flex gap-2">

                <a
                    href="<?= $_ENV['URL_ADM']; ?>export-payment-provision-pdf?<?= htmlspecialchars(
                                                                                    $provisionExportQuery
                                                                                ); ?>"
                    class="btn
                       btn-sm
                       btn-outline-danger">

                    <i
                        class="fa-solid
                           fa-file-pdf
                           me-1">
                    </i>

                    PDF

                </a>


                <a
                    href="<?= $_ENV['URL_ADM']; ?>export-payment-provision-excel?<?= htmlspecialchars(
                                                                                        $provisionExportQuery
                                                                                    ); ?>"
                    class="btn
                       btn-sm
                       btn-outline-success">

                    <i
                        class="fa-solid
                           fa-file-excel
                           me-1">
                    </i>

                    Excel

                </a>

            </div>

        </div>


        <!-- ====================================================== -->
        <!-- CORPO DO CARD                                         -->
        <!-- ====================================================== -->

        <div class="card-body">

            <form
                method="GET"
                action="<?= $_ENV['URL_ADM']; ?>payment-provision"
                class="row g-3">


                <!-- ================================================== -->
                <!-- DATA DE REFERÊNCIA                                 -->
                <!-- ================================================== -->

                <div class="col-xl-2 col-md-6">

                    <label
                        for="reference_date"
                        class="form-label">

                        Data de referência

                    </label>


                    <input
                        type="date"
                        name="reference_date"
                        id="reference_date"
                        class="form-control
                           provision-filter-control"
                        value="<?= htmlspecialchars(
                                    $filters['reference_date']
                                        ?? ''
                                ); ?>">


                    <div
                        class="form-text
                           provision-filter-help">

                        Ajustada automaticamente para a terça-feira.

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- SEMANAS                                            -->
                <!-- ================================================== -->

                <div class="col-xl-2 col-md-6">

                    <label
                        for="weeks"
                        class="form-label">

                        Semanas

                    </label>


                    <select
                        name="weeks"
                        id="weeks"
                        class="form-select
                           provision-filter-control">

                        <?php foreach (
                            [
                                4,
                                5,
                                6,
                                8,
                                12,
                            ]
                            as $weeksOption
                        ): ?>

                            <option
                                value="<?= $weeksOption; ?>"
                                <?= (
                                    (int) (
                                        $filters['weeks']
                                        ?? 4
                                    )
                                    ===
                                    $weeksOption
                                )
                                    ? 'selected'
                                    : ''; ?>>

                                <?= $weeksOption; ?> semanas

                            </option>

                        <?php endforeach; ?>

                    </select>


                    <div
                        class="form-text
                           provision-filter-help">
                    </div>

                </div>


                <!-- ================================================== -->
                <!-- OBRA                                               -->
                <!-- ================================================== -->

                <div class="col-xl-2 col-md-6">

                    <label
                        for="project_id"
                        class="form-label">

                        Obra

                    </label>


                    <select
                        name="project_id"
                        id="project_id"
                        class="form-select
                           provision-filter-control">

                        <option value="">

                            Todas as obras

                        </option>


                        <?php foreach (
                            $projects
                            as $project
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


                    <div
                        class="form-text
                           provision-filter-help">
                    </div>

                </div>


                <!-- ================================================== -->
                <!-- FORNECEDORES EXCLUÍDOS                             -->
                <!-- ================================================== -->

                <div class="col-xl-3 col-md-6">

                    <label class="form-label">

                        Fornecedores excluídos

                    </label>


                    <div
                        class="dropdown"
                        data-bs-auto-close="outside">


                        <button
                            type="button"
                            class="btn
                               btn-outline-secondary
                               dropdown-toggle
                               w-100
                               text-start
                               provision-supplier-dropdown"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">


                            <span>

                                <?php if (
                                    count(
                                        $excludedSupplierKeys
                                    ) > 0
                                ): ?>

                                    <?= count(
                                        $excludedSupplierKeys
                                    ); ?>

                                    fornecedor(es) excluído(s)

                                <?php else: ?>

                                    Nenhum fornecedor excluído

                                <?php endif; ?>

                            </span>

                        </button>


                        <div
                            class="dropdown-menu
                               p-3
                               w-100"
                            style="
                            max-height: 350px;
                            overflow-y: auto;
                            min-width: 320px;
                        ">


                            <?php if (!empty($suppliers)): ?>


                                <?php foreach (
                                    $suppliers
                                    as $supplier
                                ): ?>


                                    <?php

                                    $supplierKey =
                                        (string) (
                                            $supplier['supplier_key']
                                            ?? ''
                                        );

                                    ?>


                                    <div class="form-check mb-2">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="excluded_supplier_keys[]"
                                            value="<?= htmlspecialchars(
                                                        $supplierKey
                                                    ); ?>"
                                            id="exclude_<?= md5(
                                                            $supplierKey
                                                        ); ?>"
                                            <?= in_array(
                                                $supplierKey,
                                                $excludedSupplierKeys,
                                                true
                                            )
                                                ? 'checked'
                                                : ''; ?>>


                                        <label
                                            class="form-check-label"
                                            for="exclude_<?= md5(
                                                                $supplierKey
                                                            ); ?>">

                                            <?= htmlspecialchars(
                                                $supplier['supplier_name']
                                                    ?? 'Fornecedor'
                                            ); ?>

                                        </label>

                                    </div>


                                <?php endforeach; ?>


                            <?php else: ?>


                                <span class="text-muted">

                                    Nenhum fornecedor disponível.

                                </span>


                            <?php endif; ?>

                        </div>

                    </div>


                    <div
                        class="form-text
                           provision-filter-help">

                        Os valores continuarão visíveis
                        como excluídos da projeção.

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- AÇÕES                                              -->
                <!-- ================================================== -->

                <div class="col-xl-3 col-md-12">

                    <!--
                    Mantém a mesma altura vertical
                    dos labels dos demais filtros.
                -->
                    <label
                        class="form-label
                           d-block
                           invisible">

                        Ações

                    </label>


                    <div class="provision-actions">


                        <button
                            type="submit"
                            class="btn
                               btn-primary
                               provision-action-btn
                               provision-action-primary">

                            <i
                                class="fa-solid
                                   fa-chart-line
                                   me-1">
                            </i>

                            Gerar Provisão

                        </button>


                        <a
                            href="<?= $_ENV['URL_ADM']; ?>payment-provision"
                            class="btn
                               btn-secondary
                               provision-action-btn">

                            <i
                                class="fa-solid
                                   fa-eraser
                                   me-1">
                            </i>

                            Limpar

                        </a>

                    </div>


                    <div
                        class="form-text
                           provision-filter-help">
                    </div>

                </div>


            </form>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- IDENTIFICAÇÃO DA PROJEÇÃO                              -->
    <!-- ====================================================== -->

    <div class="card mb-3 border-light shadow-sm">

        <div
            class="card-body
                   py-2
                   d-flex
                   flex-wrap
                   justify-content-between
                   align-items-center
                   gap-2">

            <div>

                <i
                    class="fa-regular
                           fa-calendar
                           me-1">
                </i>

                <strong>
                    Período projetado:
                </strong>

                <?= htmlspecialchars(
                    $projectionPeriodLabel
                ); ?>

            </div>


            <div class="text-muted small">

                Semana financeira:

                <strong>
                    <?= htmlspecialchars(
                        $financialWeekLabel
                    ); ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- CARDS GERAIS                                          -->
    <!-- ====================================================== -->

    <div class="row g-3 mb-4">


        <!-- Provisão considerada -->
        <div class="col-xl-3 col-md-6">

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

                                Provisão Considerada

                            </div>

                            <div class="fs-4 fw-bold">

                                <?= $formatMoney(
                                    $totalProvision
                                ); ?>

                            </div>

                        </div>


                        <div class="fs-3 text-primary">

                            <i
                                class="fa-solid
                                       fa-wallet">
                            </i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Fora da provisão -->
        <div class="col-xl-3 col-md-6">

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

                                Fora da Provisão

                            </div>

                            <div class="fs-4 fw-bold">

                                <?= $formatMoney(
                                    $excludedAmount
                                ); ?>

                            </div>

                        </div>


                        <div class="fs-3 text-warning">

                            <i
                                class="fa-solid
                                       fa-filter-circle-xmark">
                            </i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Projeção bruta -->
        <div class="col-xl-3 col-md-6">

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

                                Projeção Bruta

                            </div>

                            <div class="fs-4 fw-bold">

                                <?= $formatMoney(
                                    $grossProjectedAmount
                                ); ?>

                            </div>

                        </div>


                        <div class="fs-3 text-success">

                            <i
                                class="fa-solid
                                       fa-sack-dollar">
                            </i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Fornecedores -->
        <div class="col-xl-3 col-md-6">

            <div
                class="card
                       shadow-sm
                       border-0
                       h-100">

                <div class="card-body">

                    <div class="text-muted small mb-2">

                        Fornecedores

                    </div>


                    <div
                        class="d-flex
                               justify-content-between
                               align-items-end">

                        <div>

                            <div>

                                <strong class="fs-4">

                                    <?= $includedSuppliersCount; ?>

                                </strong>

                                <span class="text-muted small">

                                    considerados

                                </span>

                            </div>


                            <div class="mt-1">

                                <strong>

                                    <?= $excludedSuppliersCount; ?>

                                </strong>

                                <span class="text-muted small">

                                    excluídos

                                </span>

                            </div>

                        </div>


                        <div class="fs-3 text-info">

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
    <!-- RESUMO DAS SEMANAS                                     -->
    <!-- ====================================================== -->

    <div class="card mb-4 border-light shadow">

        <div class="card-header">

            <i
                class="fa-solid
                       fa-calendar-week
                       me-1">
            </i>

            Resumo da Provisão por Semana

        </div>


        <div class="card-body">

            <div class="row g-3">

                <?php foreach (
                    $weeks as $week
                ): ?>

                    <div
                        class="col-xl-3
                               col-lg-4
                               col-md-6">

                        <div
                            class="card
                                   h-100
                                   shadow-sm">

                            <div class="card-body">

                                <div
                                    class="text-muted
                                           small
                                           mb-1">

                                    Semana
                                    <?= (int) (
                                        $week['week_number']
                                        ?? 0
                                    ); ?>

                                </div>


                                <div class="fw-semibold mb-2">

                                    <?= $formatDate(
                                        $week['start_date']
                                            ?? null
                                    ); ?>

                                    a

                                    <?= $formatDate(
                                        $week['end_date']
                                            ?? null
                                    ); ?>

                                </div>


                                <div class="fs-4 fw-bold">

                                    <?= $formatMoney(
                                        $week['total']
                                            ?? 0
                                    ); ?>

                                </div>


                                <div
                                    class="text-muted
                                           small
                                           mt-1">

                                    <?= count(
                                        $week['items']
                                            ?? []
                                    ); ?>

                                    lançamento(s) / apropriação(ões)

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- DETALHAMENTO POR SEMANA                                -->
    <!-- ====================================================== -->

    <?php foreach (
        $weeks as $week
    ): ?>


        <?php

        $weekItems =
            $week['items']
            ?? [];

        ?>


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
                               fa-calendar-days
                               me-1">
                    </i>

                    <strong>

                        Semana
                        <?= (int) (
                            $week['week_number']
                            ?? 0
                        ); ?>

                    </strong>

                    —

                    <?= $formatDate(
                        $week['start_date']
                            ?? null
                    ); ?>

                    a

                    <?= $formatDate(
                        $week['end_date']
                            ?? null
                    ); ?>

                </div>


                <span class="badge bg-primary fs-6">

                    <?= $formatMoney(
                        $week['total']
                            ?? 0
                    ); ?>

                </span>

            </div>


            <div class="card-body p-0">

                <div class="table-responsive">

                    <table
                        class="table
                            table-hover
                            align-middle
                            mb-0
                            provision-table">

                        <colgroup>
                            <col class="col-project">
                            <col class="col-document">
                            <col class="col-supplier">
                            <col class="col-purchase-date">
                            <col class="col-document-value">
                            <col class="col-due-date">
                            <col class="col-installment">
                        </colgroup>

                        <thead>

                            <tr>

                                <th class="ps-3">

                                    Obra

                                </th>

                                <th>

                                    Documento

                                </th>

                                <th>

                                    Fornecedor

                                </th>

                                <th>

                                    Data Compra

                                </th>

                                <th class="text-end">

                                    Valor Total

                                </th>

                                <th class="text-center">

                                    Vencimento

                                </th>

                                <th class="text-end pe-3">

                                    Parcela a Pagar

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (
                                !empty($weekItems)
                            ): ?>


                                <?php foreach (
                                    $weekItems as $item
                                ): ?>

                                    <tr>

                                        <td class="ps-3">

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $item['project_name']
                                                        ?? '-'
                                                ); ?>

                                            </strong>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $item['document_number']
                                                    ?? '-'
                                            ); ?>

                                        </td>


                                        <td class="supplier-cell">

                                            <?= htmlspecialchars(
                                                $item['supplier_name']
                                                    ??
                                                    'Fornecedor não identificado'
                                            ); ?>

                                        </td>


                                        <td>

                                            <?= $formatDate(
                                                $item['purchase_date']
                                                    ?? null
                                            ); ?>

                                        </td>


                                        <td class="text-end">

                                            <?= $formatMoney(
                                                $item['document_total_amount']
                                                    ??
                                                    $item['allocated_document_amount']
                                                    ??
                                                    0
                                            ); ?>

                                        </td>


                                        <td class="text-center">

                                            <?= $formatDate(
                                                $item['due_date']
                                                    ?? null
                                            ); ?>

                                        </td>


                                        <td
                                            class="text-end
                                                   pe-3
                                                   fw-semibold">

                                            <?= $formatMoney(
                                                $item['provision_amount']
                                                    ?? 0
                                            ); ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>


                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="7"
                                        class="text-center
                                               text-muted
                                               py-4">

                                        Nenhum pagamento previsto
                                        para esta semana.

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>


                        <?php if (
                            !empty($weekItems)
                        ): ?>

                            <tfoot>

                                <tr class="fw-bold">

                                    <td
                                        colspan="6"
                                        class="text-end">

                                        TOTAL DA SEMANA

                                    </td>

                                    <td
                                        class="text-end
                                               pe-3">

                                        <?= $formatMoney(
                                            $week['total']
                                                ?? 0
                                        ); ?>

                                    </td>

                                </tr>

                            </tfoot>

                        <?php endif; ?>

                    </table>

                </div>

            </div>

        </div>

    <?php endforeach; ?>


    <!-- ====================================================== -->
    <!-- FORNECEDORES EXCLUÍDOS                                 -->
    <!-- ====================================================== -->

    <?php if (
        $excludedSuppliersCount > 0
    ): ?>

        <div
            class="card
                   mb-4
                   border-warning
                   shadow">

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
                               fa-filter-circle-xmark
                               me-1">
                    </i>

                    Valores retirados da provisão

                </div>


                <strong>

                    <?= $formatMoney(
                        $excludedAmount
                    ); ?>

                </strong>

            </div>


            <div class="card-body">

                <div class="mb-3">

                    <strong>
                        Fornecedores excluídos:
                    </strong>

                    <div
                        class="d-flex
                               flex-wrap
                               gap-2
                               mt-2">

                        <?php foreach (
                            $excludedSuppliers
                            as $supplier
                        ): ?>

                            <span
                                class="badge
                                       text-bg-warning">

                                <?= htmlspecialchars(
                                    $supplier['supplier_name']
                                        ?? 'Fornecedor'
                                ); ?>

                            </span>

                        <?php endforeach; ?>

                    </div>

                </div>


                <?php if (
                    !empty($excludedItems)
                ): ?>

                    <button
                        class="btn
           btn-sm
           btn-outline-warning"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#excludedProvisionItems"
                        aria-expanded="false"
                        aria-controls="excludedProvisionItems">

                        <i
                            class="fa-solid
               fa-eye
               me-1">
                        </i>

                        Ver valores excluídos

                    </button>


                    <div
                        class="collapse mt-3"
                        id="excludedProvisionItems">


                        <!-- ================================================== -->
                        <!-- DESKTOP / TABLET                                   -->
                        <!-- ================================================== -->

                        <div class="d-none d-md-block">

                            <div class="table-responsive">

                                <table
                                    class="table
                       table-sm
                       table-hover
                       align-middle
                       excluded-provision-table">

                                    <colgroup>
                                        <col class="col-project">
                                        <col class="col-document">
                                        <col class="col-supplier">
                                        <col class="col-due-date">
                                        <col class="col-value">
                                    </colgroup>

                                    <thead>

                                        <tr>

                                            <th>
                                                Obra
                                            </th>

                                            <th>
                                                Documento
                                            </th>

                                            <th>
                                                Fornecedor
                                            </th>

                                            <th>
                                                Vencimento
                                            </th>

                                            <th class="text-end">
                                                Valor Excluído
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        <?php foreach (
                                            $excludedItems as $item
                                        ): ?>

                                            <tr>

                                                <td>

                                                    <?= htmlspecialchars(
                                                        $item['project_name']
                                                            ?? '-'
                                                    ); ?>

                                                </td>

                                                <td>

                                                    <?= htmlspecialchars(
                                                        $item['document_number']
                                                            ?? '-'
                                                    ); ?>

                                                </td>

                                                <td>

                                                    <?= htmlspecialchars(
                                                        $item['supplier_name']
                                                            ?? '-'
                                                    ); ?>

                                                </td>

                                                <td>

                                                    <?= $formatDate(
                                                        $item['due_date']
                                                            ?? null
                                                    ); ?>

                                                </td>

                                                <td class="text-end fw-semibold">

                                                    <?= $formatMoney(
                                                        $item['provision_amount']
                                                            ?? 0
                                                    ); ?>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <!-- ================================================== -->
                        <!-- MOBILE                                             -->
                        <!-- ================================================== -->

                        <div class="d-md-none">

                            <?php foreach (
                                $excludedItems as $item
                            ): ?>

                                <div
                                    class="border
                       rounded
                       p-3
                       mb-3">

                                    <div
                                        class="d-flex
                           justify-content-between
                           align-items-start
                           gap-2
                           mb-2">

                                        <div>

                                            <div class="text-muted small">

                                                Obra

                                            </div>

                                            <div class="fw-bold">

                                                <?= htmlspecialchars(
                                                    $item['project_name']
                                                        ?? '-'
                                                ); ?>

                                            </div>

                                        </div>


                                        <div class="text-end">

                                            <div class="text-muted small">

                                                Valor excluído

                                            </div>

                                            <div
                                                class="fw-bold
                                   text-warning">

                                                <?= $formatMoney(
                                                    $item['provision_amount']
                                                        ?? 0
                                                ); ?>

                                            </div>

                                        </div>

                                    </div>


                                    <hr class="my-2">


                                    <div class="row g-2">

                                        <div class="col-6">

                                            <div class="text-muted small">

                                                Documento

                                            </div>

                                            <div>

                                                <?= htmlspecialchars(
                                                    $item['document_number']
                                                        ?? '-'
                                                ); ?>

                                            </div>

                                        </div>


                                        <div class="col-6 text-end">

                                            <div class="text-muted small">

                                                Vencimento

                                            </div>

                                            <div>

                                                <?= $formatDate(
                                                    $item['due_date']
                                                        ?? null
                                                ); ?>

                                            </div>

                                        </div>


                                        <div class="col-12">

                                            <div class="text-muted small">

                                                Fornecedor

                                            </div>

                                            <div>

                                                <?= htmlspecialchars(
                                                    $item['supplier_name']
                                                        ?? '-'
                                                ); ?>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>


                    </div>

                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>


</div>