<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\financial;

use App\admsDaman\Models\Repository\FinancialDisbursementsRepository;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportFinancialDisbursementsExcel
{
    /**
     * Exportar os desembolsos respeitando os mesmos filtros da tela.
     */
    public function index(): void
    {
        $filters =
            $this->getFilters();

        $repository =
            new FinancialDisbursementsRepository();

        $entries =
            $repository->getAllForExport(
                $filters
            );

        $summary =
            $repository->getSummary(
                $filters
            );

        $generatedAt =
            new DateTimeImmutable();

        $spreadsheet =
            new Spreadsheet();

        $spreadsheet
            ->getProperties()
            ->setCreator('SPCSCM')
            ->setTitle('Relatório de Desembolso de Obras')
            ->setSubject('Conferência de pagamentos por obra e fornecedor');

        /*
         * =====================================================
         * ABA 1 - MOVIMENTAÇÕES
         * =====================================================
         */
        $movementSheet =
            $spreadsheet->getActiveSheet();

        $movementSheet->setTitle(
            'Movimentações'
        );

        $this->buildMovementsSheet(
            $movementSheet,
            $entries,
            $filters,
            (float) (
                $summary['total_amount']
                ?? 0
            ),
            $generatedAt
        );

        /*
         * =====================================================
         * ABA 2 - POR OBRA
         * =====================================================
         */
        $projectSheet =
            new Worksheet(
                $spreadsheet,
                'Por Obra'
            );

        $spreadsheet->addSheet(
            $projectSheet
        );

        $this->buildProjectSheet(
            $projectSheet,
            $this->groupByProject(
                $entries
            ),
            $filters,
            $generatedAt
        );

        /*
         * =====================================================
         * ABA 3 - POR FORNECEDOR
         * =====================================================
         */
        $supplierSheet =
            new Worksheet(
                $spreadsheet,
                'Por Fornecedor'
            );

        $spreadsheet->addSheet(
            $supplierSheet
        );

        $this->buildSupplierSheet(
            $supplierSheet,
            $this->groupBySupplier(
                $entries
            ),
            $filters,
            $generatedAt
        );

        /*
         * =====================================================
         * ABA 4 - OBRA X FORNECEDOR
         * =====================================================
         */
        $crossSheet =
            new Worksheet(
                $spreadsheet,
                'Obra x Fornecedor'
            );

        $spreadsheet->addSheet(
            $crossSheet
        );

        $this->buildProjectSupplierSheet(
            $crossSheet,
            $this->groupByProjectSupplier(
                $entries
            ),
            $filters,
            $generatedAt
        );

        $spreadsheet->setActiveSheetIndex(0);

        $fileName =
            'desembolso-obras-'
            . $generatedAt->format('Y-m-d-His')
            . '.xlsx';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header(
            'Content-Type: '
                . 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        header(
            'Content-Disposition: attachment; filename="'
                . $fileName
                . '"'
        );

        header('Cache-Control: max-age=0');
        header('Expires: 0');
        header('Pragma: public');

        $writer =
            new Xlsx(
                $spreadsheet
            );

        $writer->save(
            'php://output'
        );

        $spreadsheet->disconnectWorksheets();

        unset($spreadsheet);

        exit;
    }


    /**
     * Aba detalhada para conferência linha a linha.
     */
    private function buildMovementsSheet(
        Worksheet $sheet,
        array $entries,
        array $filters,
        float $total,
        DateTimeImmutable $generatedAt
    ): void {

        $this->buildCommonHeader(
            $sheet,
            'Relatório de Desembolso de Obras - Movimentações',
            'N',
            $filters,
            $total,
            $generatedAt
        );

        $headerRow = 7;

        $headers = [
            'Data',
            'Obra',
            'Fornecedor / Credor',
            'Descrição',
            'Origem',
            'Documento',
            'Parcela',
            'Forma de Pagamento',
            'Principal',
            'Juros',
            'Multa',
            'Desconto',
            'Total Desembolsado',
            'Observação',
        ];

        foreach ($headers as $index => $header) {
            $column =
                $this->columnFromIndex(
                    $index + 1
                );

            $sheet->setCellValue(
                $column . $headerRow,
                $header
            );
        }

        $this->styleTableHeader(
            $sheet,
            'A' . $headerRow . ':N' . $headerRow
        );

        $row = $headerRow + 1;

        foreach ($entries as $entry) {

            $date =
                !empty($entry['event_date'])
                    ? date(
                        'd/m/Y',
                        strtotime(
                            (string) $entry['event_date']
                        )
                    )
                    : '-';

            $document =
                trim(
                    (string) (
                        $entry['document_type']
                        ?? ''
                    )
                    . ' '
                    . (string) (
                        $entry['document_number']
                        ?? ''
                    )
                );

            if ($document === '') {
                $document = '-';
            }

            $sheet->setCellValue(
                'A' . $row,
                $date
            );

            $sheet->setCellValue(
                'B' . $row,
                $entry['project_name']
                    ?? '-'
            );

            $sheet->setCellValue(
                'C' . $row,
                $entry['counterparty']
                    ?? '-'
            );

            $sheet->setCellValue(
                'D' . $row,
                $entry['report_description']
                    ?? '-'
            );

            $sheet->setCellValue(
                'E' . $row,
                $entry['origin_label']
                    ?? '-'
            );

            $sheet->setCellValueExplicit(
                'F' . $row,
                $document,
                DataType::TYPE_STRING
            );

            $sheet->setCellValue(
                'G' . $row,
                !empty($entry['installment_number'])
                    ? (int) $entry['installment_number']
                    : '-'
            );

            $sheet->setCellValue(
                'H' . $row,
                $entry['payment_method_name']
                    ?? '-'
            );

            $sheet->setCellValue(
                'I' . $row,
                (float) (
                    $entry['allocated_principal_amount']
                    ?? 0
                )
            );

            $sheet->setCellValue(
                'J' . $row,
                (float) (
                    $entry['allocated_interest_amount']
                    ?? 0
                )
            );

            $sheet->setCellValue(
                'K' . $row,
                (float) (
                    $entry['allocated_penalty_amount']
                    ?? 0
                )
            );

            $sheet->setCellValue(
                'L' . $row,
                (float) (
                    $entry['allocated_discount_amount']
                    ?? 0
                )
            );

            $sheet->setCellValue(
                'M' . $row,
                (float) (
                    $entry['amount']
                    ?? 0
                )
            );

            $sheet->setCellValue(
                'N' . $row,
                $entry['observation']
                    ?? '-'
            );

            $row++;
        }

        if ($row > $headerRow + 1) {
            $sheet->getStyle(
                'I' . ($headerRow + 1)
                    . ':M'
                    . ($row - 1)
            )
                ->getNumberFormat()
                ->setFormatCode(
                    'R$ #,##0.00'
                );

            $sheet->setAutoFilter(
                'A' . $headerRow
                    . ':N'
                    . ($row - 1)
            );

            $sheet->getStyle(
                'A' . $headerRow
                    . ':N'
                    . ($row - 1)
            )
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(
                    Border::BORDER_THIN
                );
        }

        $sheet->freezePane(
            'A' . ($headerRow + 1)
        );

        $this->configureWidths(
            $sheet,
            [
                'A' => 13,
                'B' => 28,
                'C' => 38,
                'D' => 34,
                'E' => 22,
                'F' => 26,
                'G' => 11,
                'H' => 24,
                'I' => 16,
                'J' => 14,
                'K' => 14,
                'L' => 14,
                'M' => 20,
                'N' => 38,
            ]
        );
    }


    /**
     * Aba consolidada por obra.
     */
    private function buildProjectSheet(
        Worksheet $sheet,
        array $rows,
        array $filters,
        DateTimeImmutable $generatedAt
    ): void {

        $total =
            array_sum(
                array_column(
                    $rows,
                    'total_amount'
                )
            );

        $this->buildCommonHeader(
            $sheet,
            'Desembolso por Obra',
            'C',
            $filters,
            (float) $total,
            $generatedAt
        );

        $headerRow = 7;

        $sheet->fromArray(
            [
                'Obra',
                'Movimentações',
                'Total Desembolsado',
            ],
            null,
            'A' . $headerRow
        );

        $this->styleTableHeader(
            $sheet,
            'A' . $headerRow . ':C' . $headerRow
        );

        $row = $headerRow + 1;

        foreach ($rows as $item) {

            $sheet->setCellValue(
                'A' . $row,
                $item['label']
            );

            $sheet->setCellValue(
                'B' . $row,
                $item['movement_count']
            );

            $sheet->setCellValue(
                'C' . $row,
                $item['total_amount']
            );

            $row++;
        }

        if ($row > $headerRow + 1) {
            $sheet->getStyle(
                'C' . ($headerRow + 1)
                    . ':C'
                    . ($row - 1)
            )
                ->getNumberFormat()
                ->setFormatCode(
                    'R$ #,##0.00'
                );
        }

        $sheet->freezePane(
            'A' . ($headerRow + 1)
        );

        $this->configureWidths(
            $sheet,
            [
                'A' => 38,
                'B' => 18,
                'C' => 22,
            ]
        );
    }


    /**
     * Aba consolidada por fornecedor.
     */
    private function buildSupplierSheet(
        Worksheet $sheet,
        array $rows,
        array $filters,
        DateTimeImmutable $generatedAt
    ): void {

        $total =
            array_sum(
                array_column(
                    $rows,
                    'total_amount'
                )
            );

        $this->buildCommonHeader(
            $sheet,
            'Desembolso por Fornecedor / Credor',
            'C',
            $filters,
            (float) $total,
            $generatedAt
        );

        $headerRow = 7;

        $sheet->fromArray(
            [
                'Fornecedor / Credor',
                'Pagamentos',
                'Total Desembolsado',
            ],
            null,
            'A' . $headerRow
        );

        $this->styleTableHeader(
            $sheet,
            'A' . $headerRow . ':C' . $headerRow
        );

        $row = $headerRow + 1;

        foreach ($rows as $item) {

            $sheet->setCellValue(
                'A' . $row,
                $item['label']
            );

            $sheet->setCellValue(
                'B' . $row,
                $item['payment_count']
            );

            $sheet->setCellValue(
                'C' . $row,
                $item['total_amount']
            );

            $row++;
        }

        if ($row > $headerRow + 1) {
            $sheet->getStyle(
                'C' . ($headerRow + 1)
                    . ':C'
                    . ($row - 1)
            )
                ->getNumberFormat()
                ->setFormatCode(
                    'R$ #,##0.00'
                );
        }

        $sheet->freezePane(
            'A' . ($headerRow + 1)
        );

        $this->configureWidths(
            $sheet,
            [
                'A' => 48,
                'B' => 18,
                'C' => 22,
            ]
        );
    }


    /**
     * Aba cruzada obra x fornecedor.
     */
    private function buildProjectSupplierSheet(
        Worksheet $sheet,
        array $rows,
        array $filters,
        DateTimeImmutable $generatedAt
    ): void {

        $total =
            array_sum(
                array_column(
                    $rows,
                    'total_amount'
                )
            );

        $this->buildCommonHeader(
            $sheet,
            'Desembolso por Obra x Fornecedor',
            'D',
            $filters,
            (float) $total,
            $generatedAt
        );

        $headerRow = 7;

        $sheet->fromArray(
            [
                'Obra',
                'Fornecedor / Credor',
                'Movimentações',
                'Total Desembolsado',
            ],
            null,
            'A' . $headerRow
        );

        $this->styleTableHeader(
            $sheet,
            'A' . $headerRow . ':D' . $headerRow
        );

        $row = $headerRow + 1;

        foreach ($rows as $item) {

            $sheet->setCellValue(
                'A' . $row,
                $item['project_name']
            );

            $sheet->setCellValue(
                'B' . $row,
                $item['supplier_name']
            );

            $sheet->setCellValue(
                'C' . $row,
                $item['movement_count']
            );

            $sheet->setCellValue(
                'D' . $row,
                $item['total_amount']
            );

            $row++;
        }

        if ($row > $headerRow + 1) {
            $sheet->getStyle(
                'D' . ($headerRow + 1)
                    . ':D'
                    . ($row - 1)
            )
                ->getNumberFormat()
                ->setFormatCode(
                    'R$ #,##0.00'
                );
        }

        $sheet->freezePane(
            'A' . ($headerRow + 1)
        );

        $this->configureWidths(
            $sheet,
            [
                'A' => 38,
                'B' => 48,
                'C' => 18,
                'D' => 22,
            ]
        );
    }


    /**
     * Cabeçalho comum.
     */
    private function buildCommonHeader(
        Worksheet $sheet,
        string $title,
        string $lastColumn,
        array $filters,
        float $total,
        DateTimeImmutable $generatedAt
    ): void {

        $sheet->mergeCells(
            'A1:' . $lastColumn . '1'
        );

        $sheet->setCellValue(
            'A1',
            $title
        );

        $sheet->getStyle(
            'A1:' . $lastColumn . '1'
        )
            ->getFont()
            ->setBold(true)
            ->setSize(14);

        $sheet->mergeCells(
            'A2:' . $lastColumn . '2'
        );

        $sheet->setCellValue(
            'A2',
            'Período: '
                . $this->formatPeriod(
                    $filters
                )
        );

        $sheet->mergeCells(
            'A3:' . $lastColumn . '3'
        );

        $sheet->setCellValue(
            'A3',
            'Total filtrado: R$ '
                . number_format(
                    $total,
                    2,
                    ',',
                    '.'
                )
        );

        $sheet->mergeCells(
            'A4:' . $lastColumn . '4'
        );

        $sheet->setCellValue(
            'A4',
            'Gerado em: '
                . $generatedAt->format(
                    'd/m/Y H:i:s'
                )
        );

        $sheet->getStyle(
            'A1:' . $lastColumn . '4'
        )
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );

        $sheet->getStyle(
            'A1:' . $lastColumn . '4'
        )
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB(
                'FFE9ECEF'
            );
    }


    private function styleTableHeader(
        Worksheet $sheet,
        string $range
    ): void {

        $sheet->getStyle(
            $range
        )
            ->getFont()
            ->setBold(true);

        $sheet->getStyle(
            $range
        )
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB(
                'FFD9EAF7'
            );

        $sheet->getStyle(
            $range
        )
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );
    }


    private function configureWidths(
        Worksheet $sheet,
        array $widths
    ): void {

        foreach ($widths as $column => $width) {
            $sheet
                ->getColumnDimension(
                    $column
                )
                ->setWidth(
                    $width
                );
        }

        $sheet->getStyle(
            $sheet->calculateWorksheetDimension()
        )
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_TOP
            )
            ->setWrapText(true);
    }


    /**
     * Agrupar por obra.
     */
    private function groupByProject(
        array $entries
    ): array {

        $groups = [];

        foreach ($entries as $entry) {

            $projectId =
                (int) (
                    $entry['project_id']
                    ?? 0
                );

            $key =
                $projectId > 0
                    ? (string) $projectId
                    : '0';

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'label' =>
                        $entry['project_name']
                        ?? 'Obra não informada',

                    'movement_keys' =>
                        [],

                    'total_amount' =>
                        0.0,
                ];
            }

            $groups[$key]['movement_keys'][
                (string) (
                    $entry['row_key']
                    ?? uniqid('row_', true)
                )
            ] = true;

            $groups[$key]['total_amount'] +=
                (float) (
                    $entry['amount']
                    ?? 0
                );
        }

        $result = [];

        foreach ($groups as $group) {
            $result[] = [
                'label' =>
                    $group['label'],

                'movement_count' =>
                    count(
                        $group['movement_keys']
                    ),

                'total_amount' =>
                    $group['total_amount'],
            ];
        }

        usort(
            $result,
            static fn(array $a, array $b): int =>
                $b['total_amount']
                <=>
                $a['total_amount']
        );

        return $result;
    }


    /**
     * Agrupar por fornecedor.
     *
     * O total usa as parcelas já apropriadas por obra.
     * A quantidade de pagamentos é deduplicada por source_payment_key,
     * evitando contar duas vezes um pagamento rateado entre obras.
     */
    private function groupBySupplier(
        array $entries
    ): array {

        $groups = [];

        foreach ($entries as $entry) {

            $key =
                (string) (
                    $entry['supplier_key']
                    ?? ''
                );

            if ($key === '') {
                $key = '-';
            }

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'label' =>
                        $entry['counterparty']
                        ?? '-',

                    'payment_keys' =>
                        [],

                    'total_amount' =>
                        0.0,
                ];
            }

            $paymentKey =
                (string) (
                    $entry['source_payment_key']
                    ?? $entry['row_key']
                    ?? ''
                );

            if ($paymentKey !== '') {
                $groups[$key]['payment_keys'][
                    $paymentKey
                ] = true;
            }

            $groups[$key]['total_amount'] +=
                (float) (
                    $entry['amount']
                    ?? 0
                );
        }

        $result = [];

        foreach ($groups as $group) {
            $result[] = [
                'label' =>
                    $group['label'],

                'payment_count' =>
                    count(
                        $group['payment_keys']
                    ),

                'total_amount' =>
                    $group['total_amount'],
            ];
        }

        usort(
            $result,
            static fn(array $a, array $b): int =>
                $b['total_amount']
                <=>
                $a['total_amount']
        );

        return $result;
    }


    /**
     * Agrupar por obra e fornecedor.
     */
    private function groupByProjectSupplier(
        array $entries
    ): array {

        $groups = [];

        foreach ($entries as $entry) {

            $projectName =
                (string) (
                    $entry['project_name']
                    ?? 'Obra não informada'
                );

            $supplierName =
                (string) (
                    $entry['counterparty']
                    ?? '-'
                );

            $key =
                $projectName
                . '|'
                . (
                    $entry['supplier_key']
                    ?? '-'
                );

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'project_name' =>
                        $projectName,

                    'supplier_name' =>
                        $supplierName,

                    'movement_keys' =>
                        [],

                    'total_amount' =>
                        0.0,
                ];
            }

            $groups[$key]['movement_keys'][
                (string) (
                    $entry['row_key']
                    ?? uniqid('row_', true)
                )
            ] = true;

            $groups[$key]['total_amount'] +=
                (float) (
                    $entry['amount']
                    ?? 0
                );
        }

        $result = [];

        foreach ($groups as $group) {
            $result[] = [
                'project_name' =>
                    $group['project_name'],

                'supplier_name' =>
                    $group['supplier_name'],

                'movement_count' =>
                    count(
                        $group['movement_keys']
                    ),

                'total_amount' =>
                    $group['total_amount'],
            ];
        }

        usort(
            $result,
            static function (
                array $a,
                array $b
            ): int {

                $projectCompare =
                    strcasecmp(
                        $a['project_name'],
                        $b['project_name']
                    );

                if ($projectCompare !== 0) {
                    return $projectCompare;
                }

                return
                    $b['total_amount']
                    <=>
                    $a['total_amount'];
            }
        );

        return $result;
    }


    /**
     * Recuperar e validar filtros GET.
     */
    private function getFilters(): array
    {
        $getFilters =
            filter_input_array(
                INPUT_GET,
                FILTER_UNSAFE_RAW
            ) ?? [];

        $origin =
            strtolower(
                trim(
                    (string) (
                        $getFilters['origin']
                        ?? ''
                    )
                )
            );

        $allowedOrigins = [
            'purchase',
            'financial_obligation',
            'direct',
        ];

        $filters = [
            'project_id' =>
                !empty($getFilters['project_id'])
                    ? (int) $getFilters['project_id']
                    : null,

            'origin' =>
                in_array(
                    $origin,
                    $allowedOrigins,
                    true
                )
                    ? $origin
                    : null,

            'category_key' =>
                $this->normalizeCategoryKey(
                    $getFilters['category_key']
                    ?? null
                ),

            'supplier_key' =>
                $this->normalizeSupplierKey(
                    $getFilters['supplier_key']
                    ?? null
                ),

            'payment_method_id' =>
                !empty($getFilters['payment_method_id'])
                    ? (int) $getFilters['payment_method_id']
                    : null,

            'date_start' =>
                $this->normalizeDate(
                    $getFilters['date_start']
                    ?? null
                ),

            'date_end' =>
                $this->normalizeDate(
                    $getFilters['date_end']
                    ?? null
                ),

            'search' =>
                !empty($getFilters['search'])
                    ? trim(
                        (string) $getFilters['search']
                    )
                    : null,
        ];


        if (
            $filters['category_key']
            === 'purchase'
        ) {
            $filters['origin'] =
                'purchase';

        } elseif (
            str_starts_with(
                (string) $filters['category_key'],
                'direct:'
            )
        ) {
            $filters['origin'] =
                'direct';
        }


        return $filters;
    }


    private function normalizeDate(
        mixed $date
    ): ?string {

        $date =
            trim(
                (string) $date
            );

        if (
            !preg_match(
                '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',
                $date
            )
        ) {
            return null;
        }

        return $date;
    }


    private function normalizeCategoryKey(
        mixed $categoryKey
    ): ?string {

        $categoryKey =
            trim(
                (string) $categoryKey
            );

        if (
            $categoryKey === 'purchase'
            ||
            preg_match(
                '/^direct:[1-9][0-9]*$/',
                $categoryKey
            )
        ) {
            return $categoryKey;
        }

        return null;
    }


    private function normalizeSupplierKey(
        mixed $supplierKey
    ): ?string {

        $supplierKey =
            trim(
                (string) $supplierKey
            );

        if (
            preg_match(
                '/^(tax:[0-9]{8,20}|supplier:[1-9][0-9]*)$/',
                $supplierKey
            )
        ) {
            return $supplierKey;
        }

        return null;
    }


    private function formatPeriod(
        array $filters
    ): string {

        $dateStart =
            $filters['date_start']
            ?? null;

        $dateEnd =
            $filters['date_end']
            ?? null;

        if (
            empty($dateStart)
            &&
            empty($dateEnd)
        ) {
            return 'Todos os períodos';
        }

        $from =
            !empty($dateStart)
                ? date(
                    'd/m/Y',
                    strtotime(
                        (string) $dateStart
                    )
                )
                : 'início';

        $to =
            !empty($dateEnd)
                ? date(
                    'd/m/Y',
                    strtotime(
                        (string) $dateEnd
                    )
                )
                : 'hoje';

        return
            $from
            . ' a '
            . $to;
    }


    private function columnFromIndex(
        int $index
    ): string {

        $column = '';

        while ($index > 0) {

            $index--;

            $column =
                chr(
                    65 + (
                        $index % 26
                    )
                )
                . $column;

            $index =
                intdiv(
                    $index,
                    26
                );
        }

        return $column;
    }
}
