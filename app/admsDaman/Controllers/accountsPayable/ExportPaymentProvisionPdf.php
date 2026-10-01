<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Models\Repository\AccountsPayableReportsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;
use DateTimeImmutable;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExportPaymentProvisionPdf
{
    public function index(): void
    {
        /*
         * =====================================================
         * FILTROS
         * =====================================================
         */
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
         * LABELS DOS FILTROS
         * =====================================================
         */
        $filterLabels =
            $this->getFilterLabels(
                $filters
            );


        /*
         * =====================================================
         * EXCLUÍDO POR SEMANA
         * =====================================================
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
         * DADOS DA VIEW
         * =====================================================
         */
        $data = [

            'filters' =>
                $filters,

            'filterLabels' =>
                $filterLabels,

            'provision' =>
                $provision,

            'excludedByWeek' =>
                $excludedByWeek,

            'generatedAt' =>
                new DateTimeImmutable(),

        ];


        /*
         * =====================================================
         * HTML
         * =====================================================
         */
        $html =
            $this->renderPdfView(
                $data
            );


        /*
         * =====================================================
         * DOMPDF
         * =====================================================
         */
        $options =
            new Options();


        $options->set(
            'isHtml5ParserEnabled',
            true
        );


        $dompdf =
            new Dompdf(
                $options
            );


        $dompdf->loadHtml(
            $html,
            'UTF-8'
        );


        /*
         * Paisagem porque temos 7 colunas
         * no detalhamento.
         */
        $dompdf->setPaper(
            'A4',
            'landscape'
        );


        $dompdf->render();


        /*
         * =====================================================
         * NOME DO ARQUIVO
         * =====================================================
         */
        $fileName =
            'provisao-pagamentos-'
            .
            $provision['start_date']
            .
            '-'
            .
            $provision['end_date']
            .
            '.pdf';


        $dompdf->stream(
            $fileName,
            [
                'Attachment' =>
                    true,
            ]
        );


        exit;
    }


    /**
     * ============================================================
     * RENDERIZAR VIEW
     * ============================================================
     */
    private function renderPdfView(
        array $data
    ): string {

        $viewPath =
            dirname(
                __DIR__,
                2
            )
            .
            '/Views/accountsPayable/paymentProvisionPdf.php';


        if (!is_file($viewPath)) {

            throw new \RuntimeException(
                'View do PDF da provisão não encontrada.'
            );
        }


        ob_start();


        require $viewPath;


        $html =
            ob_get_clean();


        if ($html === false) {

            throw new \RuntimeException(
                'Não foi possível renderizar o PDF da provisão.'
            );
        }


        return $html;
    }


    /**
     * ============================================================
     * VALORES EXCLUÍDOS POR SEMANA
     * ============================================================
     */
    private function calculateExcludedByWeek(
        array $weeks,
        array $excludedItems
    ): array {

        $totals = [];


        foreach ($weeks as $week) {

            $weekNumber =
                (int) (
                    $week['week_number']
                    ?? 0
                );


            $totals[$weekNumber] =
                0.0;
        }


        foreach ($excludedItems as $item) {

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

        /*
         * Obra.
         */
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


    /**
     * ============================================================
     * FILTROS
     * ============================================================
     */
    private function getFilters(): array
    {
        $getFilters =
            filter_input_array(
                INPUT_GET,
                FILTER_UNSAFE_RAW
            ) ?? [];


        /*
         * Data.
         */
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


        /*
         * Semanas.
         */
        $weeks =
            isset(
                $getFilters['weeks']
            )
                ? (int)
                    $getFilters['weeks']
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


        /*
         * Obra.
         */
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


        /*
         * Fornecedores excluídos.
         */
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


        $excludedSupplierKeys =
            array_values(
                array_filter(
                    array_map(
                        static fn(
                            mixed $supplierKey
                        ): string =>
                            trim(
                                (string) $supplierKey
                            ),

                        $excludedSupplierKeys
                    ),

                    static fn(
                        string $supplierKey
                    ): bool =>
                        $supplierKey !== ''
                )
            );


        return [

            'reference_date' =>
                $referenceDate,

            'weeks' =>
                $weeks,

            'project_id' =>
                $projectId,

            'excluded_supplier_keys' =>
                $excludedSupplierKeys,
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
}