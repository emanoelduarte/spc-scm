<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Models\Repository\AccountsPayableReportsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportPaymentProvisionExcel
{
    public function index(): void
    {
        $filters =
            $this->getFilters();


        /*
         * =====================================================
         * SEMANA FINANCEIRA
         * =====================================================
         */
        $startDate =
            $this->normalizeToFinancialWeekTuesday(
                $filters['reference_date']
            );


        /*
         * =====================================================
         * PROVISÃO
         * =====================================================
         */
        $repository =
            new AccountsPayableReportsRepository();


        $provision =
            $repository
                ->getPaymentProvisionByWeeks(
                    $startDate,
                    $filters['weeks'],
                    [
                        'project_id' =>
                            $filters['project_id'],

                        'excluded_supplier_keys' =>
                            $filters[
                                'excluded_supplier_keys'
                            ],
                    ]
                );


        /*
         * =====================================================
         * NOMES AMIGÁVEIS DOS FILTROS
         * =====================================================
         */
        $filterLabels =
            $this->getFilterLabels(
                $filters
            );


        /*
         * Quanto foi excluído de cada semana.
         */
        $excludedByWeek =
            $this->calculateExcludedByWeek(
                $provision['weeks']
                ?? [],

                $provision['excluded_items']
                ?? []
            );


        /*
         * =====================================================
         * XLSX
         * =====================================================
         */
        $spreadsheet =
            new Spreadsheet();


        $spreadsheet
            ->getProperties()
            ->setCreator('SPCSCM')
            ->setTitle(
                'Provisão de Pagamentos'
            );


        /*
         * =====================================================
         * ABA 1 - RESUMO
         * =====================================================
         */
        $summarySheet =
            $spreadsheet
                ->getActiveSheet();

        $summarySheet
            ->setTitle(
                'Resumo'
            );


        $this->buildSummarySheet(
            $summarySheet,
            $provision,
            $excludedByWeek,
            $filterLabels
        );


        /*
         * =====================================================
         * ABA 2 - PROVISÃO DETALHADA
         * =====================================================
         */
        $detailSheet =
            new Worksheet(
                $spreadsheet,
                'Provisão Detalhada'
            );


        $spreadsheet
            ->addSheet(
                $detailSheet
            );


        $this->buildDetailSheet(
            $detailSheet,
            $provision,
            $filterLabels
        );


        /*
         * =====================================================
         * ABA 3 - VALORES EXCLUÍDOS
         * =====================================================
         */
        if (
            !empty(
                $provision['excluded_items']
                ?? []
            )
        ) {

            $excludedSheet =
                new Worksheet(
                    $spreadsheet,
                    'Valores Excluídos'
                );


            $spreadsheet
                ->addSheet(
                    $excludedSheet
                );


            $this->buildExcludedSheet(
                $excludedSheet,
                $provision,
                $filterLabels
            );
        }


        /*
         * Sempre abrir no Resumo.
         */
        $spreadsheet
            ->setActiveSheetIndex(0);


        /*
         * =====================================================
         * DOWNLOAD
         * =====================================================
         */
        $fileName =
            'provisao-pagamentos-'
            .
            $startDate
            .
            '-'
            .
            ($provision['end_date'] ?? '')
            .
            '.xlsx';


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
     * ABA RESUMO
     * ============================================================
     */
    private function buildSummarySheet(
        Worksheet $sheet,
        array $provision,
        array $excludedByWeek,
        array $filterLabels
    ): void {

        $sheet->mergeCells(
            'A1:E1'
        );

        $sheet->setCellValue(
            'A1',
            'PROVISÃO DE PAGAMENTOS'
        );

        $sheet
            ->getStyle('A1')
            ->getFont()
            ->setBold(true)
            ->setSize(16);


        $sheet->mergeCells(
            'A2:E2'
        );

        $sheet->setCellValue(
            'A2',
            'Resumo da necessidade futura de caixa'
        );


        /*
         * Período.
         */
        $sheet->setCellValue(
            'A4',
            'Período projetado:'
        );

        $sheet
            ->getStyle('A4')
            ->getFont()
            ->setBold(true);

        $sheet->setCellValue(
            'B4',
            $this->formatDateBr(
                $provision['start_date']
            )
            .
            ' a '
            .
            $this->formatDateBr(
                $provision['end_date']
            )
        );


        /*
         * Semana financeira.
         */
        $sheet->setCellValue(
            'D4',
            'Semana financeira:'
        );

        $sheet
            ->getStyle('D4')
            ->getFont()
            ->setBold(true);

        $sheet->setCellValue(
            'E4',
            'Terça-feira a Segunda-feira'
        );


        /*
         * Obra.
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
        );


        /*
         * Fornecedores excluídos.
         */
        $sheet->setCellValue(
            'D5',
            'Fornecedores excluídos:'
        );

        $sheet
            ->getStyle('D5')
            ->getFont()
            ->setBold(true);

        $sheet->setCellValue(
            'E5',
            !empty(
                $filterLabels[
                    'excluded_supplier_names'
                ]
            )
                ? implode(
                    ', ',
                    $filterLabels[
                        'excluded_supplier_names'
                    ]
                )
                : 'Nenhum'
        );


        /*
         * =====================================================
         * TOTAIS GERAIS
         * =====================================================
         */
        $sheet->setCellValue(
            'A7',
            'Provisão Considerada'
        );

        $sheet->setCellValue(
            'B7',
            (float) (
                $provision[
                    'total_provision'
                ]
                ?? 0
            )
        );


        $sheet->setCellValue(
            'C7',
            'Fora da Provisão'
        );

        $sheet->setCellValue(
            'D7',
            (float) (
                $provision[
                    'excluded_amount'
                ]
                ?? 0
            )
        );


        $sheet->setCellValue(
            'A8',
            'Projeção Bruta'
        );

        $sheet->setCellValue(
            'B8',
            (float) (
                $provision[
                    'gross_projected_amount'
                ]
                ?? 0
            )
        );


        $sheet->setCellValue(
            'C8',
            'Fornecedores'
        );

        $sheet->setCellValue(
            'D8',
            sprintf(
                '%d considerados / %d excluídos',
                (int) (
                    $provision[
                        'included_suppliers_count'
                    ]
                    ?? 0
                ),
                (int) (
                    $provision[
                        'excluded_suppliers_count'
                    ]
                    ?? 0
                )
            )
        );


        foreach (
            [
                'B7',
                'D7',
                'B8',
            ]
            as $cell
        ) {

            $sheet
                ->getStyle($cell)
                ->getNumberFormat()
                ->setFormatCode(
                    'R$ #,##0.00'
                );
        }


        $sheet
            ->getStyle('A7:D8')
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
         * RESUMO POR SEMANA
         * =====================================================
         */
        $headerRow = 11;


        $headers = [
            'Semana',
            'Período',
            'Provisionado',
            'Excluído',
            'Projeção Bruta',
        ];


        $column = 'A';

        foreach ($headers as $header) {

            $sheet->setCellValue(
                $column . $headerRow,
                $header
            );

            $column++;
        }


        $this->styleHeader(
            $sheet,
            "A{$headerRow}:E{$headerRow}"
        );


        $rowNumber =
            $headerRow + 1;


        foreach (
            $provision['weeks']
            ?? []
            as $week
        ) {

            $weekNumber =
                (int) $week[
                    'week_number'
                ];


            $excluded =
                (float) (
                    $excludedByWeek[
                        $weekNumber
                    ]
                    ?? 0
                );


            $considered =
                (float) (
                    $week['total']
                    ?? 0
                );


            $gross =
                $considered
                +
                $excluded;


            $sheet->setCellValue(
                'A' . $rowNumber,
                'Semana '
                .
                $weekNumber
            );


            $sheet->setCellValue(
                'B' . $rowNumber,
                $this->formatDateBr(
                    $week['start_date']
                )
                .
                ' a '
                .
                $this->formatDateBr(
                    $week['end_date']
                )
            );


            $sheet->setCellValue(
                'C' . $rowNumber,
                $considered
            );

            $sheet->setCellValue(
                'D' . $rowNumber,
                $excluded
            );

            $sheet->setCellValue(
                'E' . $rowNumber,
                $gross
            );


            $sheet
                ->getStyle(
                    "C{$rowNumber}:E{$rowNumber}"
                )
                ->getNumberFormat()
                ->setFormatCode(
                    'R$ #,##0.00'
                );


            $rowNumber++;
        }


        /*
         * Total.
         */
        $sheet->mergeCells(
            "A{$rowNumber}:B{$rowNumber}"
        );

        $sheet->setCellValue(
            'A' . $rowNumber,
            'TOTAL'
        );

        $sheet->setCellValue(
            'C' . $rowNumber,
            (float) (
                $provision[
                    'total_provision'
                ]
                ?? 0
            )
        );

        $sheet->setCellValue(
            'D' . $rowNumber,
            (float) (
                $provision[
                    'excluded_amount'
                ]
                ?? 0
            )
        );

        $sheet->setCellValue(
            'E' . $rowNumber,
            (float) (
                $provision[
                    'gross_projected_amount'
                ]
                ?? 0
            )
        );


        $this->styleTotal(
            $sheet,
            "A{$rowNumber}:E{$rowNumber}"
        );


        $sheet
            ->getStyle(
                "C{$rowNumber}:E{$rowNumber}"
            )
            ->getNumberFormat()
            ->setFormatCode(
                'R$ #,##0.00'
            );


        /*
         * Larguras.
         */
        $sheet
            ->getColumnDimension('A')
            ->setWidth(20);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(28);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(18);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(18);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(22);


        $sheet->freezePane(
            'A12'
        );
    }


    /**
     * ============================================================
     * ABA DETALHADA
     * ============================================================
     */
    private function buildDetailSheet(
        Worksheet $sheet,
        array $provision,
        array $filterLabels
    ): void {

        $sheet->mergeCells(
            'A1:G1'
        );

        $sheet->setCellValue(
            'A1',
            'PROVISÃO DE PAGAMENTOS - DETALHAMENTO'
        );

        $sheet
            ->getStyle('A1')
            ->getFont()
            ->setBold(true)
            ->setSize(16);


        $sheet->mergeCells(
            'A2:G2'
        );

        $sheet->setCellValue(
            'A2',
            $this->formatDateBr(
                $provision['start_date']
            )
            .
            ' a '
            .
            $this->formatDateBr(
                $provision['end_date']
            )
        );


        $rowNumber = 4;


        foreach (
            $provision['weeks']
            ?? []
            as $week
        ) {

            /*
             * =================================================
             * TÍTULO DA SEMANA
             * =================================================
             */
            $sheet->mergeCells(
                "A{$rowNumber}:G{$rowNumber}"
            );

            $sheet->setCellValue(
                'A' . $rowNumber,
                sprintf(
                    'SEMANA %d — %s a %s',
                    (int) $week[
                        'week_number'
                    ],
                    $this->formatDateBr(
                        $week[
                            'start_date'
                        ]
                    ),
                    $this->formatDateBr(
                        $week[
                            'end_date'
                        ]
                    )
                )
            );


            $sheet
                ->getStyle(
                    'A' . $rowNumber
                )
                ->getFont()
                ->setBold(true);


            $rowNumber++;


            /*
             * Cabeçalho.
             */
            $headers = [
                'Obra',
                'Documento',
                'Fornecedor',
                'Data Compra',
                'Valor Total',
                'Vencimento',
                'Parcela a Pagar',
            ];


            $column = 'A';

            foreach (
                $headers as $header
            ) {

                $sheet->setCellValue(
                    $column . $rowNumber,
                    $header
                );

                $column++;
            }


            $this->styleHeader(
                $sheet,
                "A{$rowNumber}:G{$rowNumber}"
            );


            $rowNumber++;


            /*
             * Linhas.
             */
            $items =
                $week['items']
                ?? [];


            if (empty($items)) {

                $sheet->mergeCells(
                    "A{$rowNumber}:G{$rowNumber}"
                );

                $sheet->setCellValue(
                    'A' . $rowNumber,
                    'Nenhum pagamento previsto para esta semana.'
                );

                $rowNumber++;

            } else {

                foreach (
                    $items as $item
                ) {

                    $sheet->setCellValue(
                        'A' . $rowNumber,
                        $item[
                            'project_name'
                        ]
                        ?? '-'
                    );

                    $sheet->setCellValue(
                        'B' . $rowNumber,
                        $item[
                            'document_number'
                        ]
                        ?? '-'
                    );

                    $sheet->setCellValue(
                        'C' . $rowNumber,
                        $item[
                            'supplier_name'
                        ]
                        ?? '-'
                    );

                    $sheet->setCellValue(
                        'D' . $rowNumber,
                        $this->formatDateBr(
                            $item[
                                'purchase_date'
                            ]
                        )
                    );

                    $sheet->setCellValue(
                        'E' . $rowNumber,
                        (float) (
                            $item[
                                'document_total_amount'
                            ]
                            ?? 0
                        )
                    );

                    $sheet->setCellValue(
                        'F' . $rowNumber,
                        $this->formatDateBr(
                            $item[
                                'due_date'
                            ]
                        )
                    );

                    $sheet->setCellValue(
                        'G' . $rowNumber,
                        (float) (
                            $item[
                                'provision_amount'
                            ]
                            ?? 0
                        )
                    );


                    $sheet
                        ->getStyle(
                            "E{$rowNumber}:G{$rowNumber}"
                        );


                    $sheet
                        ->getStyle(
                            'E' . $rowNumber
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            'R$ #,##0.00'
                        );

                    $sheet
                        ->getStyle(
                            'G' . $rowNumber
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            'R$ #,##0.00'
                        );


                    $rowNumber++;
                }


                /*
                 * Total da semana.
                 */
                $sheet->mergeCells(
                    "A{$rowNumber}:F{$rowNumber}"
                );

                $sheet->setCellValue(
                    'A' . $rowNumber,
                    'TOTAL DA SEMANA'
                );

                $sheet->setCellValue(
                    'G' . $rowNumber,
                    (float) (
                        $week['total']
                        ?? 0
                    )
                );


                $this->styleTotal(
                    $sheet,
                    "A{$rowNumber}:G{$rowNumber}"
                );


                $sheet
                    ->getStyle(
                        'G' . $rowNumber
                    )
                    ->getNumberFormat()
                    ->setFormatCode(
                        'R$ #,##0.00'
                    );


                $rowNumber++;
            }


            /*
             * Espaço entre semanas.
             */
            $rowNumber++;
        }


        /*
         * Larguras.
         */
        $sheet
            ->getColumnDimension('A')
            ->setWidth(28);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(42);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(18);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(20);


        $sheet
            ->getPageSetup()
            ->setFitToWidth(1)
            ->setFitToHeight(0);
    }


    /**
     * ============================================================
     * ABA DOS EXCLUÍDOS
     * ============================================================
     */
    private function buildExcludedSheet(
        Worksheet $sheet,
        array $provision,
        array $filterLabels
    ): void {

        $sheet->mergeCells(
            'A1:E1'
        );

        $sheet->setCellValue(
            'A1',
            'VALORES RETIRADOS DA PROVISÃO'
        );

        $sheet
            ->getStyle('A1')
            ->getFont()
            ->setBold(true)
            ->setSize(16);


        $sheet->setCellValue(
            'A3',
            'Fornecedores excluídos:'
        );

        $sheet
            ->getStyle('A3')
            ->getFont()
            ->setBold(true);


        $sheet->mergeCells(
            'B3:E3'
        );

        $sheet->setCellValue(
            'B3',
            implode(
                ', ',
                $filterLabels[
                    'excluded_supplier_names'
                ]
                ?? []
            )
        );


        $sheet->setCellValue(
            'A5',
            'Obra'
        );

        $sheet->setCellValue(
            'B5',
            'Documento'
        );

        $sheet->setCellValue(
            'C5',
            'Fornecedor'
        );

        $sheet->setCellValue(
            'D5',
            'Vencimento'
        );

        $sheet->setCellValue(
            'E5',
            'Valor Excluído'
        );


        $this->styleHeader(
            $sheet,
            'A5:E5'
        );


        $rowNumber = 6;


        foreach (
            $provision['excluded_items']
            ?? []
            as $item
        ) {

            $sheet->setCellValue(
                'A' . $rowNumber,
                $item[
                    'project_name'
                ]
                ?? '-'
            );

            $sheet->setCellValue(
                'B' . $rowNumber,
                $item[
                    'document_number'
                ]
                ?? '-'
            );

            $sheet->setCellValue(
                'C' . $rowNumber,
                $item[
                    'supplier_name'
                ]
                ?? '-'
            );

            $sheet->setCellValue(
                'D' . $rowNumber,
                $this->formatDateBr(
                    $item['due_date']
                )
            );

            $sheet->setCellValue(
                'E' . $rowNumber,
                (float) (
                    $item[
                        'provision_amount'
                    ]
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


        $sheet->mergeCells(
            "A{$rowNumber}:D{$rowNumber}"
        );

        $sheet->setCellValue(
            'A' . $rowNumber,
            'TOTAL EXCLUÍDO'
        );

        $sheet->setCellValue(
            'E' . $rowNumber,
            (float) (
                $provision[
                    'excluded_amount'
                ]
                ?? 0
            )
        );


        $this->styleTotal(
            $sheet,
            "A{$rowNumber}:E{$rowNumber}"
        );


        $sheet
            ->getStyle(
                'E' . $rowNumber
            )
            ->getNumberFormat()
            ->setFormatCode(
                'R$ #,##0.00'
            );


        $sheet
            ->getColumnDimension('A')
            ->setWidth(30);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(18);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(45);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(18);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(20);
    }


    private function styleHeader(
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

        $sheet
            ->getStyle($range)
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );
    }


    private function styleTotal(
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


        $sheet
            ->getStyle($range)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );
    }


    /**
     * Retorna o valor excluído correspondente a cada semana.
     */
    private function calculateExcludedByWeek(
        array $weeks,
        array $excludedItems
    ): array {

        $totals = [];


        foreach ($weeks as $week) {

            $weekNumber =
                (int) $week[
                    'week_number'
                ];

            $totals[$weekNumber] =
                0.0;
        }


        foreach (
            $excludedItems as $item
        ) {

            $dueDate =
                $item['due_date']
                ?? null;


            if (empty($dueDate)) {
                continue;
            }


            foreach ($weeks as $week) {

                if (
                    $dueDate
                    >=
                    $week['start_date']
                    &&
                    $dueDate
                    <=
                    $week['end_date']
                ) {

                    $weekNumber =
                        (int) $week[
                            'week_number'
                        ];


                    $totals[$weekNumber] +=
                        (float) (
                            $item[
                                'provision_amount'
                            ]
                            ?? 0
                        );


                    break;
                }
            }
        }


        foreach (
            $totals as $weekNumber => $total
        ) {

            $totals[$weekNumber] =
                round(
                    $total,
                    2
                );
        }


        return $totals;
    }


    /**
     * ============================================================
     * NOMES DOS FILTROS
     * ============================================================
     */
    private function getFilterLabels(
        array $filters
    ): array {

        $projectName =
            'Todas as obras';


        if (!empty($filters['project_id'])) {

            $projectsRepository =
                new ProjectsRepository();


            foreach (
                $projectsRepository
                    ->getAllProjectsSelect()
                as $project
            ) {

                if (
                    (int) $project['id']
                    ===
                    (int) $filters[
                        'project_id'
                    ]
                ) {

                    $projectName =
                        (string) $project[
                            'name'
                        ];

                    break;
                }
            }
        }


        /*
         * Fornecedores excluídos.
         */
        $excludedSupplierNames = [];


        $excludedSupplierKeys =
            $filters[
                'excluded_supplier_keys'
            ]
            ?? [];


        if (!empty($excludedSupplierKeys)) {

            $repository =
                new PurchaseDocumentsRepository();


            foreach (
                $repository
                    ->getPurchaseSuppliersSelect()
                as $supplier
            ) {

                if (
                    in_array(
                        (string) $supplier[
                            'supplier_key'
                        ],
                        $excludedSupplierKeys,
                        true
                    )
                ) {

                    $excludedSupplierNames[] =
                        (string) $supplier[
                            'supplier_name'
                        ];
                }
            }
        }


        return [

            'project_name' =>
                $projectName,

            'excluded_supplier_names' =>
                $excludedSupplierNames,
        ];
    }


    private function getFilters(): array
    {
        $getFilters =
            filter_input_array(
                INPUT_GET,
                FILTER_UNSAFE_RAW
            ) ?? [];


        $referenceDate =
            $this->normalizeDate(
                $getFilters[
                    'reference_date'
                ]
                ?? null
            );


        if ($referenceDate === null) {

            $referenceDate =
                (new DateTimeImmutable())
                    ->format(
                        'Y-m-d'
                    );
        }


        $weeks =
            isset($getFilters['weeks'])
                ? (int) $getFilters['weeks']
                : 4;


        if (
            !in_array(
                $weeks,
                [
                    4,
                    5,
                    6,
                    8,
                    12,
                ],
                true
            )
        ) {
            $weeks = 4;
        }


        $projectId =
            !empty(
                $getFilters[
                    'project_id'
                ]
            )
                ? (int)
                    $getFilters[
                        'project_id'
                    ]
                : null;


        if (
            $projectId !== null
            &&
            $projectId <= 0
        ) {
            $projectId = null;
        }


        $excludedSupplierKeys =
            $getFilters[
                'excluded_supplier_keys'
            ]
            ?? [];


        if (
            !is_array(
                $excludedSupplierKeys
            )
        ) {

            $excludedSupplierKeys = [];
        }


        return [

            'reference_date' =>
                $referenceDate,

            'weeks' =>
                $weeks,

            'project_id' =>
                $projectId,

            'excluded_supplier_keys' =>
                array_values(
                    $excludedSupplierKeys
                ),
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


    private function normalizeToFinancialWeekTuesday(
        string $date
    ): string {

        $dateObject =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $date
            );


        if ($dateObject === false) {

            throw new \InvalidArgumentException(
                'Data de referência inválida.'
            );
        }


        $dayOfWeek =
            (int) $dateObject
                ->format('N');


        $daysSinceTuesday =
            (
                $dayOfWeek
                -
                2
                +
                7
            )
            %
            7;


        if ($daysSinceTuesday > 0) {

            $dateObject =
                $dateObject->modify(
                    '-'
                    .
                    $daysSinceTuesday
                    .
                    ' days'
                );
        }


        return
            $dateObject->format(
                'Y-m-d'
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
}