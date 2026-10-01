<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Models\Repository\AccountsPayableReportsRepository;
use DateTimeImmutable;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExportAccountsPayablePdf
{
    /**
     * ============================================================
     * EXPORTAR RELATÓRIO DO CONTAS A PAGAR EM PDF
     * ============================================================
     *
     * group:
     *
     * project  = Valor devido por obra
     * supplier = Valor devido por fornecedor
     */
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
         * RECUPERAR OS DADOS
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


        /*
         * =====================================================
         * TOTAL
         * =====================================================
         */
        $totalOpenAmount =
            $this->calculateTotalOpenAmount(
                $report
            );


        /*
         * =====================================================
         * DADOS DA VIEW
         * =====================================================
         */
        $data = [

            'group' =>
                $group,

            'filters' =>
                $filters,

            'report' =>
                $report,

            'totalOpenAmount' =>
                $totalOpenAmount,

            'periodLabel' =>
                $this->getPeriodLabel(
                    $filters
                ),

            'generatedAt' =>
                new DateTimeImmutable(),

        ];


        /*
         * =====================================================
         * GERAR HTML
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


        /*
         * Não precisamos de conteúdo remoto neste primeiro
         * relatório, mas deixamos fontes HTML5 habilitadas.
         */
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
         * A4 retrato é suficiente para esta primeira
         * versão resumida.
         */
        $dompdf->setPaper(
            'A4',
            'portrait'
        );


        $dompdf->render();


        /*
         * =====================================================
         * NOME DO ARQUIVO
         * =====================================================
         */
        $fileName =
            $group === 'supplier'
                ? 'contas-a-pagar-por-fornecedor'
                : 'contas-a-pagar-por-obra';


        $fileName .=
            '-'
            .
            date(
                'Y-m-d'
            )
            .
            '.pdf';


        /*
         * Attachment = true
         *
         * força download em vez de abrir inline.
         */
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
         * Fornecedor.
         */
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


        /*
         * Datas.
         */
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


        /*
         * Corrigir datas invertidas.
         */
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


    /**
     * Validar data Y-m-d.
     */
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


    /**
     * Somar os valores do relatório.
     */
    private function calculateTotalOpenAmount(
        array $report
    ): float {

        $total =
            0.0;


        foreach (
            $report as $row
        ) {

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


    /**
     * Criar descrição amigável do período.
     */
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


    /**
     * Converter Y-m-d para d/m/Y.
     */
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
     * Renderizar a View específica do PDF.
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
            '/Views/accountsPayable/reportsPdf.php';


        if (!is_file($viewPath)) {

            throw new \RuntimeException(
                'View do relatório PDF não encontrada.'
            );
        }


        ob_start();


        require $viewPath;


        $html =
            ob_get_clean();


        if ($html === false) {

            throw new \RuntimeException(
                'Não foi possível renderizar o relatório PDF.'
            );
        }


        return $html;
    }
}