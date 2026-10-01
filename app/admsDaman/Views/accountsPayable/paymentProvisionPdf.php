<?php

$provision =
    $data['provision']
    ?? [];

$filterLabels =
    $data['filterLabels']
    ?? [];

$excludedByWeek =
    $data['excludedByWeek']
    ?? [];

$generatedAt =
    $data['generatedAt']
    ?? new DateTimeImmutable();


$weeks =
    $provision['weeks']
    ?? [];

$excludedItems =
    $provision['excluded_items']
    ?? [];

$excludedSuppliers =
    $provision['excluded_suppliers']
    ?? [];


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

?>
<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>
        Provisão de Pagamentos
    </title>


    <style>

        @page {
            margin: 24px 28px 32px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #222;
        }

        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .title {
            font-size: 19px;
            font-weight: bold;
        }

        .subtitle {
            color: #666;
            margin-top: 3px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .meta td {
            width: 50%;
            padding: 3px 4px 3px 0;
            vertical-align: top;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .summary-grid td {
            width: 33.333%;
            border: 1px solid #ddd;
            background: #f5f5f5;
            padding: 9px;
        }

        .summary-label {
            color: #666;
            font-size: 8px;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 15px;
            font-weight: bold;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 14px 0 6px 0;
        }

        .week-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .week-block {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th {
            background: #333;
            color: #fff;
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }

        table.report td {
            border: 1px solid #ddd;
            padding: 5px;
            vertical-align: middle;
        }

        table.report tfoot td {
            background: #eee;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .muted {
            color: #777;
        }

        .empty {
            text-align: center;
            color: #777;
            padding: 12px !important;
        }

        .summary-week-table {
            margin-bottom: 16px;
        }

        .excluded-section {
            margin-top: 18px;
        }

        .excluded-alert {
            border: 1px solid #d0a000;
            background: #fff8db;
            padding: 8px;
            margin-bottom: 8px;
        }

        .footer {
            position: fixed;
            bottom: -18px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #888;
        }

        .page-break {
            page-break-before: always;
        }

    </style>

</head>


<body>


    <!-- ====================================================== -->
    <!-- CABEÇALHO                                              -->
    <!-- ====================================================== -->

    <div class="header">

        <div class="title">

            Provisão de Pagamentos

        </div>

        <div class="subtitle">

            Projeção semanal das obrigações financeiras
            e necessidade futura de caixa

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- FILTROS / IDENTIFICAÇÃO                                -->
    <!-- ====================================================== -->

    <table class="meta">

        <tr>

            <td>

                <strong>
                    Período projetado:
                </strong>

                <?= $formatDate(
                    $provision['start_date']
                    ?? null
                ); ?>

                a

                <?= $formatDate(
                    $provision['end_date']
                    ?? null
                ); ?>

            </td>


            <td class="text-right">

                <strong>
                    Emitido em:
                </strong>

                <?= htmlspecialchars(
                    $generatedAt->format(
                        'd/m/Y H:i'
                    )
                ); ?>

            </td>

        </tr>


        <tr>

            <td>

                <strong>
                    Semana financeira:
                </strong>

                Terça-feira a Segunda-feira

            </td>


            <td>

                <strong>
                    Obra:
                </strong>

                <?= htmlspecialchars(
                    $filterLabels[
                        'project_name'
                    ]
                    ?? 'Todas as obras'
                ); ?>

            </td>

        </tr>


        <tr>

            <td colspan="2">

                <strong>
                    Fornecedores excluídos:
                </strong>

                <?php if (
                    !empty(
                        $filterLabels[
                            'excluded_supplier_names'
                        ]
                    )
                ): ?>

                    <?= htmlspecialchars(
                        implode(
                            ', ',
                            $filterLabels[
                                'excluded_supplier_names'
                            ]
                        )
                    ); ?>

                <?php else: ?>

                    Nenhum

                <?php endif; ?>

            </td>

        </tr>

    </table>


    <!-- ====================================================== -->
    <!-- TOTAIS                                                -->
    <!-- ====================================================== -->

    <table class="summary-grid">

        <tr>

            <td>

                <div class="summary-label">

                    PROVISÃO CONSIDERADA

                </div>

                <div class="summary-value">

                    <?= $formatMoney(
                        $provision[
                            'total_provision'
                        ]
                        ?? 0
                    ); ?>

                </div>

            </td>


            <td>

                <div class="summary-label">

                    FORA DA PROVISÃO

                </div>

                <div class="summary-value">

                    <?= $formatMoney(
                        $provision[
                            'excluded_amount'
                        ]
                        ?? 0
                    ); ?>

                </div>

            </td>


            <td>

                <div class="summary-label">

                    PROJEÇÃO BRUTA

                </div>

                <div class="summary-value">

                    <?= $formatMoney(
                        $provision[
                            'gross_projected_amount'
                        ]
                        ?? 0
                    ); ?>

                </div>

            </td>

        </tr>

    </table>


    <!-- ====================================================== -->
    <!-- RESUMO POR SEMANA                                     -->
    <!-- ====================================================== -->

    <div class="section-title">

        Resumo da Provisão por Semana

    </div>


    <table
        class="report
               summary-week-table">

        <thead>

            <tr>

                <th>
                    Semana
                </th>

                <th>
                    Período
                </th>

                <th class="text-right">
                    Provisionado
                </th>

                <th class="text-right">
                    Excluído
                </th>

                <th class="text-right">
                    Projeção Bruta
                </th>

            </tr>

        </thead>


        <tbody>

            <?php foreach (
                $weeks as $week
            ): ?>

                <?php

                $weekNumber =
                    (int) (
                        $week['week_number']
                        ?? 0
                    );


                $considered =
                    (float) (
                        $week['total']
                        ?? 0
                    );


                $excluded =
                    (float) (
                        $excludedByWeek[
                            $weekNumber
                        ]
                        ?? 0
                    );


                $gross =
                    $considered
                    +
                    $excluded;

                ?>

                <tr>

                    <td>

                        Semana
                        <?= $weekNumber; ?>

                    </td>

                    <td>

                        <?= $formatDate(
                            $week['start_date']
                            ?? null
                        ); ?>

                        a

                        <?= $formatDate(
                            $week['end_date']
                            ?? null
                        ); ?>

                    </td>

                    <td class="text-right">

                        <?= $formatMoney(
                            $considered
                        ); ?>

                    </td>

                    <td class="text-right">

                        <?= $formatMoney(
                            $excluded
                        ); ?>

                    </td>

                    <td class="text-right fw-bold">

                        <?= $formatMoney(
                            $gross
                        ); ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>


        <tfoot>

            <tr>

                <td
                    colspan="2"
                    class="text-right">

                    TOTAL

                </td>

                <td class="text-right">

                    <?= $formatMoney(
                        $provision[
                            'total_provision'
                        ]
                        ?? 0
                    ); ?>

                </td>

                <td class="text-right">

                    <?= $formatMoney(
                        $provision[
                            'excluded_amount'
                        ]
                        ?? 0
                    ); ?>

                </td>

                <td class="text-right">

                    <?= $formatMoney(
                        $provision[
                            'gross_projected_amount'
                        ]
                        ?? 0
                    ); ?>

                </td>

            </tr>

        </tfoot>

    </table>


    <!-- ====================================================== -->
    <!-- DETALHAMENTO                                          -->
    <!-- ====================================================== -->

    <div class="section-title">

        Detalhamento da Provisão

    </div>


    <?php foreach (
        $weeks as $week
    ): ?>


        <?php

        $items =
            $week['items']
            ?? [];

        ?>


        <div class="week-block">

            <div class="week-title">

                Semana
                <?= (int) (
                    $week['week_number']
                    ?? 0
                ); ?>

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

                —

                <?= $formatMoney(
                    $week['total']
                    ?? 0
                ); ?>

            </div>


            <table class="report">

                <thead>

                    <tr>

                        <th style="width: 15%;">
                            Obra
                        </th>

                        <th style="width: 9%;">
                            Documento
                        </th>

                        <th style="width: 25%;">
                            Fornecedor
                        </th>

                        <th
                            style="width: 11%;"
                            class="text-center">

                            Data Compra

                        </th>

                        <th
                            style="width: 13%;"
                            class="text-right">

                            Valor Total

                        </th>

                        <th
                            style="width: 11%;"
                            class="text-center">

                            Vencimento

                        </th>

                        <th
                            style="width: 16%;"
                            class="text-right">

                            Parcela a Pagar

                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (!empty($items)): ?>


                        <?php foreach (
                            $items as $item
                        ): ?>

                            <tr>

                                <td class="fw-bold">

                                    <?= htmlspecialchars(
                                        $item[
                                            'project_name'
                                        ]
                                        ?? '-'
                                    ); ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $item[
                                            'document_number'
                                        ]
                                        ?? '-'
                                    ); ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $item[
                                            'supplier_name'
                                        ]
                                        ?? '-'
                                    ); ?>

                                </td>


                                <td class="text-center">

                                    <?= $formatDate(
                                        $item[
                                            'purchase_date'
                                        ]
                                        ?? null
                                    ); ?>

                                </td>


                                <td class="text-right">

                                    <?= $formatMoney(
                                        $item[
                                            'document_total_amount'
                                        ]
                                        ??
                                        $item[
                                            'allocated_document_amount'
                                        ]
                                        ??
                                        0
                                    ); ?>

                                </td>


                                <td class="text-center">

                                    <?= $formatDate(
                                        $item[
                                            'due_date'
                                        ]
                                        ?? null
                                    ); ?>

                                </td>


                                <td class="text-right fw-bold">

                                    <?= $formatMoney(
                                        $item[
                                            'provision_amount'
                                        ]
                                        ?? 0
                                    ); ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                class="empty">

                                Nenhum pagamento previsto
                                para esta semana.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>


                <?php if (!empty($items)): ?>

                    <tfoot>

                        <tr>

                            <td
                                colspan="6"
                                class="text-right">

                                TOTAL DA SEMANA

                            </td>

                            <td class="text-right">

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

    <?php endforeach; ?>


    <!-- ====================================================== -->
    <!-- VALORES EXCLUÍDOS                                     -->
    <!-- ====================================================== -->

    <?php if (!empty($excludedItems)): ?>

        <div class="excluded-section">

            <div class="section-title">

                Valores Retirados da Provisão

            </div>


            <div class="excluded-alert">

                <strong>
                    Total excluído:
                </strong>

                <?= $formatMoney(
                    $provision[
                        'excluded_amount'
                    ]
                    ?? 0
                ); ?>


                <?php if (
                    !empty(
                        $excludedSuppliers
                    )
                ): ?>

                    <br>

                    <strong>
                        Fornecedores:
                    </strong>

                    <?php

                    $excludedNames = [];

                    foreach (
                        $excludedSuppliers
                        as $supplier
                    ) {

                        $excludedNames[] =
                            $supplier[
                                'supplier_name'
                            ]
                            ?? 'Fornecedor';
                    }

                    ?>

                    <?= htmlspecialchars(
                        implode(
                            ', ',
                            $excludedNames
                        )
                    ); ?>

                <?php endif; ?>

            </div>


            <table class="report">

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

                        <th class="text-center">
                            Vencimento
                        </th>

                        <th class="text-right">
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
                                    $item[
                                        'project_name'
                                    ]
                                    ?? '-'
                                ); ?>

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $item[
                                        'document_number'
                                    ]
                                    ?? '-'
                                ); ?>

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $item[
                                        'supplier_name'
                                    ]
                                    ?? '-'
                                ); ?>

                            </td>

                            <td class="text-center">

                                <?= $formatDate(
                                    $item[
                                        'due_date'
                                    ]
                                    ?? null
                                ); ?>

                            </td>

                            <td class="text-right">

                                <?= $formatMoney(
                                    $item[
                                        'provision_amount'
                                    ]
                                    ?? 0
                                ); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>


                <tfoot>

                    <tr>

                        <td
                            colspan="4"
                            class="text-right">

                            TOTAL EXCLUÍDO

                        </td>

                        <td class="text-right">

                            <?= $formatMoney(
                                $provision[
                                    'excluded_amount'
                                ]
                                ?? 0
                            ); ?>

                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>

    <?php endif; ?>


    <div class="footer">

        Provisão de Pagamentos — Sistema de Controle Financeiro

    </div>


</body>

</html>