<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Models\Repository\AccountsPayableReportsRepository;
use DateTimeImmutable;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;

class ExportAccountsPayableCompletePdf
{
    public function index(): void
    {
        $filters =
            $this->getFilters();


        $repository =
            new AccountsPayableReportsRepository();


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

        $filterLabels =
            $this->getFilterLabels(
                $filters
            );


        $data = [

            'filters' =>
            $filters,

            'filterLabels' =>
            $filterLabels,

            'reportByProject' =>
            $reportByProject,

            'reportBySupplier' =>
            $reportBySupplier,

            'totalOpenAmount' =>
            $totalByProject,

            'totalBySupplier' =>
            $totalBySupplier,

            'totalsMatch' =>
            abs(
                $totalByProject
                    -
                    $totalBySupplier
            ) < 0.01,

            'periodLabel' =>
            $this->getPeriodLabel(
                $filters
            ),

            'generatedAt' =>
            new DateTimeImmutable(),
        ];


        $html =
            $this->renderPdfView(
                $data
            );


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

        $dompdf->setPaper(
            'A4',
            'portrait'
        );

        $dompdf->render();


        $fileName =
            'relatorio-contas-a-pagar-completo-'
            .
            date('Y-m-d')
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


    private function renderPdfView(
        array $data
    ): string {

        $viewPath =
            dirname(
                __DIR__,
                2
            )
            .
            '/Views/accountsPayable/reportsCompletePdf.php';


        if (!is_file($viewPath)) {

            throw new \RuntimeException(
                'View do relatório PDF completo não encontrada.'
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
            ? (int) $getFilters['project_id']
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
