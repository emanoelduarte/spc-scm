<?php

$filterLabels =
    $data['filterLabels']
    ?? [
        'project_name' =>
        'Todas as obras',

        'supplier_name' =>
        'Todos os fornecedores',
    ];

$reportByProject =
    $data['reportByProject']
    ?? [];

$reportBySupplier =
    $data['reportBySupplier']
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

?>
<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>
        Relatório de Contas a Pagar
    </title>

    <style>
        @page {
            margin: 30px 35px 40px 35px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
        }

        .subtitle {
            color: #666;
            margin-top: 4px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .meta td {
            width: 50%;
            padding: 4px 0;
        }

        .summary {
            background: #f3f3f3;
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 20px;
        }

        .summary-label {
            color: #666;
            font-size: 9px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: bold;
            margin-top: 4px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th {
            background: #333;
            color: #fff;
            border: 1px solid #333;
            padding: 7px 6px;
            text-align: left;
        }

        table.report td {
            border: 1px solid #ddd;
            padding: 7px 6px;
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

        .name {
            font-weight: bold;
        }

        .supplier-section {
            /**page-break-before: always; - para começar na próxima página*/
            margin-top: 25px;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            color: #888;
            font-size: 8px;
        }
    </style>

</head>

<body>


    <div class="header">

        <div class="title">
            Relatório de Contas a Pagar
        </div>

        <div class="subtitle">
            Resumo financeiro por obra e fornecedor
        </div>

    </div>


    <table class="meta">

        <tr>

            <td>

                <strong>Período:</strong>

                <?= htmlspecialchars(
                    $periodLabel
                ); ?>

            </td>

            <td class="text-right">

                <strong>Emitido em:</strong>

                <?= htmlspecialchars(
                    $generatedAt->format(
                        'd/m/Y H:i'
                    )
                ); ?>

            </td>

        </tr>


        <tr>

            <td>

                <strong>Obra:</strong>

                <?= htmlspecialchars(
                    $filterLabels['project_name']
                ); ?>

            </td>

            <td>

                <strong>Fornecedor:</strong>

                <?= htmlspecialchars(
                    $filterLabels['supplier_name']
                ); ?>

            </td>

        </tr>

    </table>


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
    <!-- POR OBRA                                               -->
    <!-- ====================================================== -->

    <div class="section-title">
        Valor Devido por Obra
    </div>


    <table class="report">

        <thead>

            <tr>

                <th>
                    Obra
                </th>

                <th class="text-center">
                    Lançamentos
                </th>

                <th class="text-center">
                    Parcelas
                </th>

                <th class="text-right">
                    Valor Devido
                </th>

            </tr>

        </thead>


        <tbody>

            <?php if (!empty($reportByProject)): ?>

                <?php foreach (
                    $reportByProject as $row
                ): ?>

                    <tr>

                        <td class="name">

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
                        class="text-center">

                        Nenhum valor devido encontrado.

                    </td>

                </tr>

            <?php endif; ?>

        </tbody>


        <?php if (!empty($reportByProject)): ?>

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

    <div class="supplier-section">


        <div class="header">

            <div class="title">
                Relatório de Contas a Pagar
            </div>

            <div class="subtitle">
                Resumo financeiro por fornecedor
            </div>

        </div>


        <table class="meta">

            <tr>

                <td>

                    <strong>Período:</strong>

                    <?= htmlspecialchars(
                        $periodLabel
                    ); ?>

                </td>

                <td class="text-right">

                    <strong>Emitido em:</strong>

                    <?= htmlspecialchars(
                        $generatedAt->format(
                            'd/m/Y H:i'
                        )
                    ); ?>

                </td>

            </tr>


            <tr>

                <td>

                    <strong>Obra:</strong>

                    <?= htmlspecialchars(
                        $filterLabels['project_name']
                    ); ?>

                </td>

                <td>

                    <strong>Fornecedor:</strong>

                    <?= htmlspecialchars(
                        $filterLabels['supplier_name']
                    ); ?>

                </td>

            </tr>

        </table>


        <div class="section-title">
            Valor Devido por Fornecedor
        </div>


        <table class="report">

            <thead>

                <tr>

                    <th>
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

                    <th class="text-right">
                        Valor Devido
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php if (!empty($reportBySupplier)): ?>

                    <?php foreach (
                        $reportBySupplier as $row
                    ): ?>

                        <tr>

                            <td class="name">

                                <?= htmlspecialchars(
                                    $row['supplier_name']
                                        ??
                                        'Fornecedor não identificado'
                                ); ?>

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $formatCnpj(
                                        $row['supplier_tax_id']
                                            ?? null
                                    )
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
                            colspan="5"
                            class="text-center">

                            Nenhum valor devido encontrado.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>


            <?php if (!empty($reportBySupplier)): ?>

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

    </div>


    <div class="footer">
        Sistema de Controle Financeiro
    </div>


</body>

</html>