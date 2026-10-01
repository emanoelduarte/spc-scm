<?php

$group =
    $data['group']
    ?? 'project';

$filters =
    $data['filters']
    ?? [];

$report =
    $data['report']
    ?? [];

$totalOpenAmount =
    (float) (
        $data['totalOpenAmount']
        ?? 0
    );

$periodLabel =
    $data['periodLabel']
    ?? 'Todo o período';

$generatedAt =
    $data['generatedAt']
    ?? new DateTimeImmutable();


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


$title =
    $group === 'supplier'
        ? 'Valor Devido por Fornecedor'
        : 'Valor Devido por Obra';

?>
<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>
        <?= htmlspecialchars($title); ?>
    </title>


    <style>

        @page {
            margin: 30px 35px 40px 35px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222222;
            background: #ffffff;
        }

        .header {
            border-bottom: 2px solid #333333;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .header-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }

        .header-subtitle {
            font-size: 10px;
            color: #666666;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .meta-table td {
            width: 50%;
            padding: 4px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
        }

        .summary {
            width: 100%;
            border: 1px solid #dddddd;
            background: #f5f5f5;
            padding: 12px;
            margin-bottom: 18px;
        }

        .summary-label {
            font-size: 9px;
            color: #666666;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: bold;
        }

        .report-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table thead th {
            background: #333333;
            color: #ffffff;
            padding: 8px 6px;
            border: 1px solid #333333;
            text-align: left;
        }

        .report-table tbody td {
            padding: 7px 6px;
            border: 1px solid #dddddd;
        }

        .report-table tbody tr:nth-child(even) {
            background: #f7f7f7;
        }

        .report-table tfoot td {
            padding: 8px 6px;
            border: 1px solid #cccccc;
            font-weight: bold;
            background: #eeeeee;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .entity-name {
            font-weight: bold;
        }

        .empty {
            text-align: center;
            color: #777777;
            padding: 20px !important;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            color: #888888;
            font-size: 8px;
        }

    </style>

</head>


<body>


    <!-- ====================================================== -->
    <!-- CABEÇALHO                                              -->
    <!-- ====================================================== -->

    <div class="header">

        <div class="header-title">

            Relatório de Contas a Pagar

        </div>

        <div class="header-subtitle">

            <?= htmlspecialchars($title); ?>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- INFORMAÇÕES DO RELATÓRIO                               -->
    <!-- ====================================================== -->

    <table class="meta-table">

        <tr>

            <td>

                <span class="label">
                    Período:
                </span>

                <?= htmlspecialchars(
                    $periodLabel
                ); ?>

            </td>

            <td class="text-right">

                <span class="label">
                    Emitido em:
                </span>

                <?= htmlspecialchars(
                    $generatedAt
                        ->format(
                            'd/m/Y H:i'
                        )
                ); ?>

            </td>

        </tr>

    </table>


    <!-- ====================================================== -->
    <!-- TOTAL                                                  -->
    <!-- ====================================================== -->

    <div class="summary">

        <div class="summary-label">

            TOTAL DEVIDO

        </div>

        <div class="summary-value">

            <?= $formatMoney(
                $totalOpenAmount
            ); ?>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- TÍTULO DA TABELA                                       -->
    <!-- ====================================================== -->

    <div class="report-title">

        <?= htmlspecialchars(
            $title
        ); ?>

    </div>


    <!-- ====================================================== -->
    <!-- POR OBRA                                               -->
    <!-- ====================================================== -->

    <?php if ($group === 'project'): ?>

        <table class="report-table">

            <thead>

                <tr>

                    <th>
                        Obra
                    </th>

                    <th
                        class="text-center"
                        style="width: 90px;">

                        Documentos

                    </th>

                    <th
                        class="text-center"
                        style="width: 80px;">

                        Parcelas

                    </th>

                    <th
                        class="text-right"
                        style="width: 120px;">

                        Valor Devido

                    </th>

                </tr>

            </thead>


            <tbody>

                <?php if (!empty($report)): ?>


                    <?php foreach (
                        $report as $row
                    ): ?>

                        <tr>

                            <td class="entity-name">

                                <?= htmlspecialchars(
                                    $row['project_name']
                                    ?? '-'
                                ); ?>

                            </td>


                            <td class="text-center">

                                <?= (int) (
                                    $row['documents_count']
                                    ?? 0
                                ); ?>

                            </td>


                            <td class="text-center">

                                <?= (int) (
                                    $row['installments_count']
                                    ?? 0
                                ); ?>

                            </td>


                            <td class="text-right">

                                <?= $formatMoney(
                                    $row['open_amount']
                                    ?? 0
                                ); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                <?php else: ?>

                    <tr>

                        <td
                            colspan="4"
                            class="empty">

                            Nenhum valor devido encontrado
                            para os filtros selecionados.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>


            <?php if (!empty($report)): ?>

                <tfoot>

                    <tr>

                        <td
                            colspan="3"
                            class="text-right">

                            TOTAL

                        </td>

                        <td class="text-right">

                            <?= $formatMoney(
                                $totalOpenAmount
                            ); ?>

                        </td>

                    </tr>

                </tfoot>

            <?php endif; ?>

        </table>


    <!-- ====================================================== -->
    <!-- POR FORNECEDOR                                         -->
    <!-- ====================================================== -->

    <?php else: ?>

        <table class="report-table">

            <thead>

                <tr>

                    <th>
                        Fornecedor
                    </th>

                    <th style="width: 125px;">
                        CNPJ
                    </th>

                    <th
                        class="text-center"
                        style="width: 70px;">

                        Documentos

                    </th>

                    <th
                        class="text-center"
                        style="width: 65px;">

                        Parcelas

                    </th>

                    <th
                        class="text-right"
                        style="width: 110px;">

                        Valor Devido

                    </th>

                </tr>

            </thead>


            <tbody>

                <?php if (!empty($report)): ?>


                    <?php foreach (
                        $report as $row
                    ): ?>

                        <tr>

                            <td class="entity-name">

                                <?= htmlspecialchars(
                                    $row['supplier_name']
                                    ?? 'Fornecedor não identificado'
                                ); ?>

                            </td>


                            <td>

                                <?php

                                $supplierTaxId =
                                    $row['supplier_tax_id']
                                    ?? null;

                                ?>

                                <?= !empty($supplierTaxId)
                                    ? htmlspecialchars(
                                        $formatCnpj(
                                            $supplierTaxId
                                        )
                                    )
                                    : '-'; ?>

                            </td>


                            <td class="text-center">

                                <?= (int) (
                                    $row['documents_count']
                                    ?? 0
                                ); ?>

                            </td>


                            <td class="text-center">

                                <?= (int) (
                                    $row['installments_count']
                                    ?? 0
                                ); ?>

                            </td>


                            <td class="text-right">

                                <?= $formatMoney(
                                    $row['open_amount']
                                    ?? 0
                                ); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                <?php else: ?>

                    <tr>

                        <td
                            colspan="5"
                            class="empty">

                            Nenhum valor devido encontrado
                            para os filtros selecionados.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>


            <?php if (!empty($report)): ?>

                <tfoot>

                    <tr>

                        <td
                            colspan="4"
                            class="text-right">

                            TOTAL

                        </td>

                        <td class="text-right">

                            <?= $formatMoney(
                                $totalOpenAmount
                            ); ?>

                        </td>

                    </tr>

                </tfoot>

            <?php endif; ?>

        </table>

    <?php endif; ?>


    <div class="footer">

        Relatório gerado pelo Sistema de Controle Financeiro

    </div>


</body>

</html>