<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Models\Repository\AccountsPayableReportsRepository;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportAccountsPayableExcel
{
    /**
     * Exportar relatório do Contas a Pagar em XLSX.
     *
     * group:
     * project  = Valor devido por obra
     * supplier = Valor devido por fornecedor
     */
    public function index(): void
    {
        $filters =
            $this->getFilters();


        /*
         * =====================================================
         * AGRUPAMENTO
         * =====================================================
         */
        $group =
            strtolower(
                trim(
                    (string) (
                        filter_input(
                            INPUT_GET,
                            'group',
                            FILTER_UNSAFE_RAW
                        )
                        ?? 'project'
                    )
                )
            );


        if (
            !in_array(
                $group,
                [
                    'project',
                    'supplier',
                ],
                true
            )
        ) {
            $group = 'project';
        }


        /*
         * =====================================================
         * RECUPERAR DADOS
         * =====================================================
         */
        $reportsRepository =
            new AccountsPayableReportsRepository();


        if ($group === 'supplier') {

            $report =
                $reportsRepository
                ->getOpenAmountBySupplier(
                    $filters
                );

        } else {

            $report =
                $reportsRepository
                ->getOpenAmountByProject(
                    $filters
                );
        }


        $totalOpenAmount =
            $this->calculateTotalOpenAmount(
                $report
            );


        /*
         * =====================================================
         * CRIAR PLANILHA
         * =====================================================
         */
        $spreadsheet =
            new Spreadsheet();


        $sheet =
            $spreadsheet
            ->getActiveSheet();


        $sheet->setTitle(
            $group === 'supplier'
                ? 'Por Fornecedor'
                : 'Por Obra'
        );


        /*
         * =====================================================
         * CONFIGURAÇÕES GERAIS
         * =====================================================
         */
        $spreadsheet
            ->getDefaultStyle()
            ->getFont()
            ->setName('Arial')
            ->setSize(10);


        /*
         * =====================================================
         * CABEÇALHO
         * =====================================================
         */
        $lastColumn =
            $group === 'supplier'
                ? 'E'
                : 'D';


        $sheet->mergeCells(
            "A1:{$lastColumn}1"
        );


        $sheet->setCellValue(
            'A1',
            'RELATÓRIO DE CONTAS A PAGAR'
        );


        $sheet->getStyle(
            'A1'
        )
            ->getFont()
            ->setBold(true)
            ->setSize(16);


        $sheet->mergeCells(
            "A2:{$lastColumn}2"
        );


        $sheet->setCellValue(
            'A2',
            $group === 'supplier'
                ? 'Valor Devido por Fornecedor'
                : 'Valor Devido por Obra'
        );


        $sheet->getStyle(
            'A2'
        )
            ->getFont()
            ->setSize(11);


        /*
         * =====================================================
         * PERÍODO
         * =====================================================
         */
        $sheet->setCellValue(
            'A4',
            'Período:'
        );


        $sheet->setCellValue(
            'B4',
            $this->getPeriodLabel(
                $filters
            )
        );


        $sheet->setCellValue(
            $group === 'supplier'
                ? 'D4'
                : 'C4',
            'Emitido em:'
        );


        $sheet->setCellValue(
            $group === 'supplier'
                ? 'E4'
                : 'D4',
            (new DateTimeImmutable())
                ->format(
                    'd/m/Y H:i'
                )
        );


        $sheet->getStyle(
            'A4'
        )
            ->getFont()
            ->setBold(true);


        $sheet->getStyle(
            $group === 'supplier'
                ? 'D4'
                : 'C4'
        )
            ->getFont()
            ->setBold(true);


        /*
         * =====================================================
         * TOTAL DEVIDO
         * =====================================================
         */
        $sheet->mergeCells(
            "A6:{$lastColumn}6"
        );


        $sheet->setCellValue(
            'A6',
            'TOTAL DEVIDO'
        );


        $sheet->mergeCells(
            "A7:{$lastColumn}7"
        );


        $sheet->setCellValue(
            'A7',
            $totalOpenAmount
        );


        $sheet->getStyle(
            'A6'
        )
            ->getFont()
            ->setBold(true);


        $sheet->getStyle(
            'A7'
        )
            ->getFont()
            ->setBold(true)
            ->setSize(15);


        $sheet->getStyle(
            'A7'
        )
            ->getNumberFormat()
            ->setFormatCode(
                'R$ #,##0.00'
            );


        $sheet->getStyle(
            "A6:{$lastColumn}7"
        )
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setRGB(
                'F2F2F2'
            );


        /*
         * =====================================================
         * TABELA
         * =====================================================
         */
        $headerRow = 10;


        if ($group === 'supplier') {

            $headers = [
                'Fornecedor',
                'CNPJ',
                'Documentos',
                'Parcelas',
                'Valor Devido',
            ];

        } else {

            $headers = [
                'Obra',
                'Documentos',
                'Parcelas',
                'Valor Devido',
            ];
        }


        $column = 'A';


        foreach ($headers as $header) {

            $sheet->setCellValue(
                $column . $headerRow,
                $header
            );

            $column++;
        }


        /*
         * Estilo do cabeçalho.
         */
        $sheet->getStyle(
            "A{$headerRow}:{$lastColumn}{$headerRow}"
        )
            ->getFont()
            ->setBold(true)
            ->getColor()
            ->setRGB(
                'FFFFFF'
            );


        $sheet->getStyle(
            "A{$headerRow}:{$lastColumn}{$headerRow}"
        )
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setRGB(
                '333333'
            );


        $sheet->getStyle(
            "A{$headerRow}:{$lastColumn}{$headerRow}"
        )
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );


        /*
         * =====================================================
         * DADOS
         * =====================================================
         */
        $rowNumber =
            $headerRow + 1;


        foreach ($report as $row) {

            if ($group === 'supplier') {

                $sheet->setCellValue(
                    'A' . $rowNumber,
                    $row['supplier_name']
                    ?? 'Fornecedor não identificado'
                );


                $sheet->setCellValueExplicit(
                    'B' . $rowNumber,
                    $this->formatCnpj(
                        $row['supplier_tax_id']
                        ?? null
                    ),
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
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


                $sheet->getStyle(
                    'E' . $rowNumber
                )
                    ->getNumberFormat()
                    ->setFormatCode(
                        'R$ #,##0.00'
                    );

            } else {

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


                $sheet->getStyle(
                    'D' . $rowNumber
                )
                    ->getNumberFormat()
                    ->setFormatCode(
                        'R$ #,##0.00'
                    );
            }


            $rowNumber++;
        }


        /*
         * =====================================================
         * TOTAL DA TABELA
         * =====================================================
         */
        $totalRow =
            $rowNumber;


        if ($group === 'supplier') {

            $sheet->mergeCells(
                "A{$totalRow}:D{$totalRow}"
            );


            $sheet->setCellValue(
                'A' . $totalRow,
                'TOTAL'
            );


            $sheet->setCellValue(
                'E' . $totalRow,
                $totalOpenAmount
            );


            $sheet->getStyle(
                'E' . $totalRow
            )
                ->getNumberFormat()
                ->setFormatCode(
                    'R$ #,##0.00'
                );

        } else {

            $sheet->mergeCells(
                "A{$totalRow}:C{$totalRow}"
            );


            $sheet->setCellValue(
                'A' . $totalRow,
                'TOTAL'
            );


            $sheet->setCellValue(
                'D' . $totalRow,
                $totalOpenAmount
            );


            $sheet->getStyle(
                'D' . $totalRow
            )
                ->getNumberFormat()
                ->setFormatCode(
                    'R$ #,##0.00'
                );
        }


        $sheet->getStyle(
            "A{$totalRow}:{$lastColumn}{$totalRow}"
        )
            ->getFont()
            ->setBold(true);


        $sheet->getStyle(
            "A{$totalRow}:{$lastColumn}{$totalRow}"
        )
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setRGB(
                'EEEEEE'
            );


        $sheet->getStyle(
            "A{$totalRow}:"
            . $lastColumn
            . $totalRow
        )
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_RIGHT
            );


        /*
         * =====================================================
         * BORDAS
         * =====================================================
         */
        $sheet->getStyle(
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


        /*
         * =====================================================
         * ALINHAMENTO
         * =====================================================
         */
        if ($group === 'supplier') {

            $sheet->getStyle(
                "C" . ($headerRow + 1)
                . ":D"
                . ($totalRow - 1)
            )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            $sheet->getStyle(
                "E" . ($headerRow + 1)
                . ":E{$totalRow}"
            )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_RIGHT
                );

        } else {

            $sheet->getStyle(
                "B" . ($headerRow + 1)
                . ":C"
                . ($totalRow - 1)
            )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            $sheet->getStyle(
                "D" . ($headerRow + 1)
                . ":D{$totalRow}"
            )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_RIGHT
                );
        }


        /*
         * =====================================================
         * LARGURA DAS COLUNAS
         * =====================================================
         */
        $sheet
            ->getColumnDimension('A')
            ->setWidth(
                $group === 'supplier'
                    ? 42
                    : 45
            );


        if ($group === 'supplier') {

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

        } else {

            $sheet
                ->getColumnDimension('B')
                ->setWidth(14);

            $sheet
                ->getColumnDimension('C')
                ->setWidth(12);

            $sheet
                ->getColumnDimension('D')
                ->setWidth(18);
        }


        /*
         * =====================================================
         * CONGELAR CABEÇALHO DA TABELA
         * =====================================================
         */
        $sheet->freezePane(
            'A11'
        );


        /*
         * Filtro do próprio Excel.
         */
        $sheet->setAutoFilter(
            "A{$headerRow}:"
            . $lastColumn
            . $headerRow
        );


        /*
         * =====================================================
         * CONFIGURAÇÃO DE IMPRESSÃO
         * =====================================================
         */
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


        /*
         * =====================================================
         * GERAR ARQUIVO
         * =====================================================
         */
        $fileName =
            $group === 'supplier'
                ? 'contas-a-pagar-por-fornecedor'
                : 'contas-a-pagar-por-obra';


        $fileName .=
            '-'
            .
            date('Y-m-d')
            .
            '.xlsx';


        /*
         * Muito importante:
         *
         * qualquer HTML, espaço ou warning antes do XLSX
         * pode corromper o arquivo.
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
     * Recuperar os mesmos filtros utilizados
     * pela tela e pelo PDF.
     */
    private function getFilters(): array
    {
        $getFilters =
            filter_input_array(
                INPUT_GET,
                FILTER_UNSAFE_RAW
            ) ?? [];


        $projectId =
            !empty(
                $getFilters['project_id']
            )
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
            $dateObject->format(
                'Y-m-d'
            ) !== $date
        ) {
            return null;
        }


        return $date;
    }


    private function calculateTotalOpenAmount(
        array $report
    ): float {

        $total =
            0.0;


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
                $this->formatDateBr(
                    $start
                )
                .
                ' a '
                .
                $this->formatDateBr(
                    $end
                );
        }


        if (!empty($start)) {

            return
                'A partir de '
                .
                $this->formatDateBr(
                    $start
                );
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


        if ($dateObject === false) {
            return $date;
        }


        return $dateObject->format(
            'd/m/Y'
        );
    }


    /**
     * Formatar CNPJ mantendo-o como texto no Excel.
     */
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
}