<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Models\Repository\AccountsPayableReportsRepository;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;

class ExportAccountsPayableCompleteExcel
{
    public function index(): void
    {
        $filters =
            $this->getFilters();

        $repository =
            new AccountsPayableReportsRepository();

        $filterLabels =
            $this->getFilterLabels(
                $filters
            );


        /*
         * =====================================================
         * RECUPERAR AS DUAS VISÕES
         * =====================================================
         */
        $reportByProject =
            $repository
            ->getOpenAmountByProject(
                $filters
            );

        $reportBySupplier =
            $repository
            ->getOpenAmountBySupplier(
                $filters
            );


        $totalByProject =
            $this->calculateTotal(
                $reportByProject
            );

        $totalBySupplier =
            $this->calculateTotal(
                $reportBySupplier
            );


        $generatedAt =
            new DateTimeImmutable();


        /*
         * =====================================================
         * PLANILHA
         * =====================================================
         */
        $spreadsheet =
            new Spreadsheet();

        $spreadsheet
            ->getProperties()
            ->setCreator(
                'SPCSCM'
            )
            ->setTitle(
                'Relatório de Contas a Pagar'
            );


        /*
         * =====================================================
         * ABA 1 - POR OBRA
         * =====================================================
         */
        $projectSheet =
            $spreadsheet
            ->getActiveSheet();

        $projectSheet
            ->setTitle(
                'Por Obra'
            );


        $this->buildProjectSheet(
            $projectSheet,
            $reportByProject,
            $totalByProject,
            $filters,
            $filterLabels,
            $generatedAt
        );


        /*
         * =====================================================
         * ABA 2 - POR FORNECEDOR
         * =====================================================
         */
        $supplierSheet =
            new Worksheet(
                $spreadsheet,
                'Por Fornecedor'
            );

        $spreadsheet
            ->addSheet(
                $supplierSheet
            );


        $this->buildSupplierSheet(
            $supplierSheet,
            $reportBySupplier,
            $totalBySupplier,
            $filters,
            $filterLabels,
            $generatedAt
        );


        /*
         * Ao abrir, começar pela aba Por Obra.
         */
        $spreadsheet
            ->setActiveSheetIndex(0);


        /*
         * =====================================================
         * GERAR XLSX
         * =====================================================
         */
        $fileName =
            'relatorio-contas-a-pagar-completo-'
            .
            $generatedAt->format(
                'Y-m-d'
            )
            .
            '.xlsx';


        /*
         * Evitar qualquer saída anterior corrompendo o XLSX.
         */
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

        header(
            'Cache-Control: max-age=0'
        );

        header(
            'Expires: 0'
        );

        header(
            'Pragma: public'
        );


        $writer =
            new Xlsx(
                $spreadsheet
            );

        $writer->save(
            'php://output'
        );


        $spreadsheet
            ->disconnectWorksheets();

        unset(
            $spreadsheet
        );

        exit;
    }


    /**
     * ============================================================
     * ABA POR OBRA
     * ============================================================
     */
    private function buildProjectSheet(
        Worksheet $sheet,
        array $report,
        float $total,
        array $filters,
        array $filterLabels,
        DateTimeImmutable $generatedAt
    ): void {

        $this->configureCommonHeader(
            $sheet,
            'Valor Devido por Obra',
            'D',
            $total,
            $filters,
            $filterLabels,
            $generatedAt
        );


        $headerRow = 10;


        $headers = [
            'Obra',
            'Documentos',
            'Parcelas',
            'Valor Devido',
        ];


        foreach (
            $headers as $index => $header
        ) {

            $column =
                chr(
                    65 + $index
                );

            $sheet->setCellValue(
                $column . $headerRow,
                $header
            );
        }


        $this->styleTableHeader(
            $sheet,
            "A{$headerRow}:D{$headerRow}"
        );


        $rowNumber =
            $headerRow + 1;


        foreach ($report as $row) {

            $sheet->setCellValue(
                'A' . $rowNumber,
                $row['project_name']
                    ?? '-'
            );

            $sheet->setCellValue(
                'B' . $rowNumber,
                (int) (
                    $row['documents_count']
                    ?? 0
                )
            );

            $sheet->setCellValue(
                'C' . $rowNumber,
                (int) (
                    $row['installments_count']
                    ?? 0
                )
            );

            $sheet->setCellValue(
                'D' . $rowNumber,
                (float) (
                    $row['open_amount']
                    ?? 0
                )
            );

            $sheet
                ->getStyle(
                    'D' . $rowNumber
                )
                ->getNumberFormat()
                ->setFormatCode(
                    'R$ #,##0.00'
                );

            $rowNumber++;
        }


        $totalRow =
            $rowNumber;


        $sheet->mergeCells(
            "A{$totalRow}:C{$totalRow}"
        );

        $sheet->setCellValue(
            'A' . $totalRow,
            'TOTAL'
        );

        $sheet->setCellValue(
            'D' . $totalRow,
            $total
        );

        $sheet
            ->getStyle(
                'D' . $totalRow
            )
            ->getNumberFormat()
            ->setFormatCode(
                'R$ #,##0.00'
            );


        $this->styleTotalRow(
            $sheet,
            "A{$totalRow}:D{$totalRow}"
        );


        /*
         * Alinhamentos.
         */
        if ($totalRow > 11) {

            $sheet
                ->getStyle(
                    'B11:C'
                        . ($totalRow - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );
        }


        $sheet
            ->getStyle(
                "D11:D{$totalRow}"
            )
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_RIGHT
            );


        /*
         * Larguras.
         */
        $sheet
            ->getColumnDimension('A')
            ->setWidth(45);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(14);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(12);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(18);


        $this->finishSheet(
            $sheet,
            'D',
            $headerRow,
            $totalRow
        );
    }


    /**
     * ============================================================
     * ABA POR FORNECEDOR
     * ============================================================
     */
    private function buildSupplierSheet(
        Worksheet $sheet,
        array $report,
        float $total,
        array $filters,
        array $filterLabels,
        DateTimeImmutable $generatedAt
    ): void {

        $this->configureCommonHeader(
            $sheet,
            'Valor Devido por Fornecedor',
            'E',
            $total,
            $filters,
            $filterLabels,
            $generatedAt
        );


        $headerRow = 10;


        $headers = [
            'Fornecedor',
            'CNPJ',
            'Documentos',
            'Parcelas',
            'Valor Devido',
        ];


        foreach (
            $headers as $index => $header
        ) {

            $column =
                chr(
                    65 + $index
                );

            $sheet->setCellValue(
                $column . $headerRow,
                $header
            );
        }


        $this->styleTableHeader(
            $sheet,
            "A{$headerRow}:E{$headerRow}"
        );


        $rowNumber =
            $headerRow + 1;


        foreach ($report as $row) {

            $sheet->setCellValue(
                'A' . $rowNumber,
                $row['supplier_name']
                    ?? 'Fornecedor não identificado'
            );


            /*
             * CNPJ como texto para preservar zero inicial.
             */
            $sheet->setCellValueExplicit(
                'B' . $rowNumber,
                $this->formatCnpj(
                    $row['supplier_tax_id']
                        ?? null
                ),
                DataType::TYPE_STRING
            );


            $sheet->setCellValue(
                'C' . $rowNumber,
                (int) (
                    $row['documents_count']
                    ?? 0
                )
            );


            $sheet->setCellValue(
                'D' . $rowNumber,
                (int) (
                    $row['installments_count']
                    ?? 0
                )
            );


            $sheet->setCellValue(
                'E' . $rowNumber,
                (float) (
                    $row['open_amount']
                    ?? 0
                )
            );


            $sheet
                ->getStyle(
                    'E' . $rowNumber
                )
                ->getNumberFormat()
                ->setFormatCode(
                    'R$ #,##0.00'
                );


            $rowNumber++;
        }


        $totalRow =
            $rowNumber;


        $sheet->mergeCells(
            "A{$totalRow}:D{$totalRow}"
        );

        $sheet->setCellValue(
            'A' . $totalRow,
            'TOTAL'
        );

        $sheet->setCellValue(
            'E' . $totalRow,
            $total
        );


        $sheet
            ->getStyle(
                'E' . $totalRow
            )
            ->getNumberFormat()
            ->setFormatCode(
                'R$ #,##0.00'
            );


        $this->styleTotalRow(
            $sheet,
            "A{$totalRow}:E{$totalRow}"
        );


        if ($totalRow > 11) {

            $sheet
                ->getStyle(
                    'C11:D'
                        . ($totalRow - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );
        }


        $sheet
            ->getStyle(
                "E11:E{$totalRow}"
            )
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_RIGHT
            );


        $sheet
            ->getColumnDimension('A')
            ->setWidth(42);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(22);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(14);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(12);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(18);


        $this->finishSheet(
            $sheet,
            'E',
            $headerRow,
            $totalRow
        );
    }


    /**
     * Cabeçalho comum das duas abas.
     */
    private function configureCommonHeader(
        Worksheet $sheet,
        string $subtitle,
        string $lastColumn,
        float $total,
        array $filters,
        array $filterLabels,
        DateTimeImmutable $generatedAt
    ): void {

        $sheet->mergeCells(
            "A1:{$lastColumn}1"
        );

        $sheet->setCellValue(
            'A1',
            'RELATÓRIO DE CONTAS A PAGAR'
        );

        $sheet
            ->getStyle('A1')
            ->getFont()
            ->setBold(true)
            ->setSize(16);


        $sheet->mergeCells(
            "A2:{$lastColumn}2"
        );

        $sheet->setCellValue(
            'A2',
            $subtitle
        );


        /*
         * Período.
         */
        $sheet->setCellValue(
            'A4',
            'Período:'
        );

        $sheet
            ->getStyle('A4')
            ->getFont()
            ->setBold(true);


        $sheet->setCellValue(
            'B4',
            $this->getPeriodLabel(
                $filters
            )
        );


        /*
         * Emissão.
         */
        $emittedLabelColumn =
            $lastColumn === 'E'
            ? 'D'
            : 'C';

        $emittedValueColumn =
            $lastColumn;


        $sheet->setCellValue(
            $emittedLabelColumn . '4',
            'Emitido em:'
        );

        $sheet
            ->getStyle(
                $emittedLabelColumn . '4'
            )
            ->getFont()
            ->setBold(true);


        $sheet->setCellValue(
            $emittedValueColumn . '4',
            $generatedAt->format(
                'd/m/Y H:i'
            )
        );

        /*
 * =====================================================
 * OBRA
 * =====================================================
 */
        $sheet->setCellValue(
            'A5',
            'Obra:'
        );

        $sheet
            ->getStyle('A5')
            ->getFont()
            ->setBold(true);

        $sheet->setCellValue(
            'B5',
            $filterLabels['project_name']
                ?? 'Todas as obras'
        );


        /*
        * =====================================================
        * FORNECEDOR
        * =====================================================
        */
        $supplierLabelColumn =
            $lastColumn === 'E'
            ? 'D'
            : 'C';

        $supplierValueColumn =
            $lastColumn;


        $sheet->setCellValue(
            $supplierLabelColumn . '5',
            'Fornecedor:'
        );

        $sheet
            ->getStyle(
                $supplierLabelColumn . '5'
            )
            ->getFont()
            ->setBold(true);

        $sheet->setCellValue(
            $supplierValueColumn . '5',
            $filterLabels['supplier_name']
                ?? 'Todos os fornecedores'
        );


        /*
 * =====================================================
 * TOTAL
 * =====================================================
 */
        $sheet->mergeCells(
            "A7:{$lastColumn}7"
        );

        $sheet->setCellValue(
            'A7',
            'TOTAL DEVIDO'
        );

        $sheet
            ->getStyle('A7')
            ->getFont()
            ->setBold(true);


        $sheet->mergeCells(
            "A8:{$lastColumn}8"
        );

        $sheet->setCellValue(
            'A8',
            $total
        );

        $sheet
            ->getStyle('A8')
            ->getFont()
            ->setBold(true)
            ->setSize(15);

        $sheet
            ->getStyle('A8')
            ->getNumberFormat()
            ->setFormatCode(
                'R$ #,##0.00'
            );

        $sheet
            ->getStyle('A8')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_RIGHT
            );

        $sheet
            ->getStyle(
                "A7:{$lastColumn}8"
            )
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setRGB(
                'F2F2F2'
            );
    }


    private function styleTableHeader(
        Worksheet $sheet,
        string $range
    ): void {

        $sheet
            ->getStyle($range)
            ->getFont()
            ->setBold(true)
            ->getColor()
            ->setRGB(
                'FFFFFF'
            );

        $sheet
            ->getStyle($range)
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setRGB(
                '333333'
            );
    }


    private function styleTotalRow(
        Worksheet $sheet,
        string $range
    ): void {

        $sheet
            ->getStyle($range)
            ->getFont()
            ->setBold(true);

        $sheet
            ->getStyle($range)
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setRGB(
                'EEEEEE'
            );

        $sheet
            ->getStyle($range)
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_RIGHT
            );
    }


    private function finishSheet(
        Worksheet $sheet,
        string $lastColumn,
        int $headerRow,
        int $totalRow
    ): void {

        $sheet
            ->getStyle(
                "A{$headerRow}:"
                    . $lastColumn
                    . $totalRow
            )
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            )
            ->getColor()
            ->setRGB(
                'D9D9D9'
            );


        $sheet->setAutoFilter(
            "A{$headerRow}:"
                . $lastColumn
                . $headerRow
        );


        $sheet->freezePane(
            'A11'
        );


        $sheet
            ->getPageSetup()
            ->setFitToWidth(1)
            ->setFitToHeight(0);


        $sheet
            ->getPageMargins()
            ->setTop(0.5)
            ->setBottom(0.5)
            ->setLeft(0.5)
            ->setRight(0.5);
    }


    private function calculateTotal(
        array $report
    ): float {

        $total = 0.0;

        foreach ($report as $row) {

            $total +=
                (float) (
                    $row['open_amount']
                    ?? 0
                );
        }

        return round(
            $total,
            2
        );
    }


    private function getFilters(): array
    {
        $getFilters =
            filter_input_array(
                INPUT_GET,
                FILTER_UNSAFE_RAW
            ) ?? [];


        $projectId =
            !empty($getFilters['project_id'])
            ? (int)
            $getFilters['project_id']
            : null;


        if (
            $projectId !== null
            &&
            $projectId <= 0
        ) {
            $projectId = null;
        }


        $supplierKey =
            trim(
                (string) (
                    $getFilters['supplier_key']
                    ?? ''
                )
            );


        if ($supplierKey === '') {
            $supplierKey = null;
        }


        $dueDateStart =
            $this->normalizeDate(
                $getFilters['due_date_start']
                    ?? null
            );


        $dueDateEnd =
            $this->normalizeDate(
                $getFilters['due_date_end']
                    ?? null
            );


        if (
            $dueDateStart !== null
            &&
            $dueDateEnd !== null
            &&
            $dueDateStart > $dueDateEnd
        ) {

            [
                $dueDateStart,
                $dueDateEnd
            ] = [
                $dueDateEnd,
                $dueDateStart
            ];
        }


        return [
            'project_id' =>
            $projectId,

            'supplier_key' =>
            $supplierKey,

            'due_date_start' =>
            $dueDateStart,

            'due_date_end' =>
            $dueDateEnd,
        ];
    }


    private function normalizeDate(
        mixed $date
    ): ?string {

        $date =
            trim(
                (string) $date
            );


        if ($date === '') {
            return null;
        }


        $dateObject =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $date
            );


        if (
            $dateObject === false
            ||
            $dateObject->format('Y-m-d')
            !== $date
        ) {
            return null;
        }


        return $date;
    }


    private function getPeriodLabel(
        array $filters
    ): string {

        $start =
            $filters['due_date_start']
            ?? null;

        $end =
            $filters['due_date_end']
            ?? null;


        if (
            empty($start)
            &&
            empty($end)
        ) {
            return 'Todo o período';
        }


        if (
            !empty($start)
            &&
            !empty($end)
        ) {

            return
                $this->formatDateBr($start)
                .
                ' a '
                .
                $this->formatDateBr($end);
        }


        if (!empty($start)) {

            return
                'A partir de '
                .
                $this->formatDateBr($start);
        }


        return
            'Até '
            .
            $this->formatDateBr(
                (string) $end
            );
    }


    private function formatDateBr(
        string $date
    ): string {

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
    }


    private function formatCnpj(
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
    }

    /**
     * Recuperar os nomes amigáveis dos filtros selecionados.
     */
    private function getFilterLabels(
        array $filters
    ): array {

        /*
     * =====================================================
     * OBRA
     * =====================================================
     */
        $projectName =
            'Todas as obras';


        if (!empty($filters['project_id'])) {

            $projectsRepository =
                new ProjectsRepository();

            $projects =
                $projectsRepository
                ->getAllProjectsSelect();


            foreach ($projects as $project) {

                if (
                    (int) $project['id']
                    ===
                    (int) $filters['project_id']
                ) {

                    $projectName =
                        (string) $project['name'];

                    break;
                }
            }
        }


        /*
     * =====================================================
     * FORNECEDOR
     * =====================================================
     */
        $supplierName =
            'Todos os fornecedores';


        if (!empty($filters['supplier_key'])) {

            $purchaseDocumentsRepository =
                new PurchaseDocumentsRepository();

            $suppliers =
                $purchaseDocumentsRepository
                ->getPurchaseSuppliersSelect();


            foreach ($suppliers as $supplier) {

                if (
                    (string) $supplier['supplier_key']
                    ===
                    (string) $filters['supplier_key']
                ) {

                    $supplierName =
                        (string) $supplier['supplier_name'];

                    break;
                }
            }
        }


        return [

            'project_name' =>
            $projectName,

            'supplier_name' =>
            $supplierName,

        ];
    }
}
