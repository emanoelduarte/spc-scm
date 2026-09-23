<?php

namespace App\admsDaman\Controllers\nfes;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Models\Repository\NfeRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para listar as NF-e recebidas.
 *
 * @author Emanoel
 */
class ListNfes
{
    /**
     * Dados enviados para a View.
     *
     * @var array|string|null
     */
    private array|string|null $data = null;


    /**
     * Recuperar e listar as NF-e cadastradas.
     *
     * Os dois accordions possuem paginação independente:
     *
     * - pending_page = NF-e pendentes;
     * - checked_page = NF-e conferidas.
     *
     * @return void
     */
    public function index(): void
    {
        /*
         * Quantidade de registros exibidos
         * em cada accordion.
         */
        $limitResult = 10;


        /*
         * =====================================================
         * PÁGINAS ATUAIS
         * =====================================================
         */
        $pendingPage =
            filter_input(
                INPUT_GET,
                'pending_page',
                FILTER_VALIDATE_INT
            );


        $checkedPage =
            filter_input(
                INPUT_GET,
                'checked_page',
                FILTER_VALIDATE_INT
            );


        $pendingPage =
            $pendingPage && $pendingPage > 0
                ? $pendingPage
                : 1;


        $checkedPage =
            $checkedPage && $checkedPage > 0
                ? $checkedPage
                : 1;


        /*
         * Accordion que deve permanecer aberto
         * após clicar na paginação.
         */
        $activeSection =
            filter_input(
                INPUT_GET,
                'section',
                FILTER_UNSAFE_RAW
            );


        if (
            !in_array(
                $activeSection,
                ['pending', 'checked'],
                true
            )
        ) {
            $activeSection = 'pending';
        }


        $nfeRepository =
            new NfeRepository();


        /*
         * Recuperar informações da última
         * sincronização com a SEFAZ.
         */
        $this->data['nfe_sync'] =
            $nfeRepository->getSyncData(
                $_ENV['NFE_CNPJ']
            );


        /*
         * =====================================================
         * TOTAIS
         * =====================================================
         */
        $pendingTotal =
            $nfeRepository
                ->countNfesByCheckedStatus(0);


        $checkedTotal =
            $nfeRepository
                ->countNfesByCheckedStatus(1);


        $pendingTotalPages =
            max(
                1,
                (int) ceil(
                    $pendingTotal
                    / $limitResult
                )
            );


        $checkedTotalPages =
            max(
                1,
                (int) ceil(
                    $checkedTotal
                    / $limitResult
                )
            );


        /*
         * Evitar página inexistente caso registros
         * tenham sido movidos entre os accordions.
         */
        $pendingPage =
            min(
                $pendingPage,
                $pendingTotalPages
            );


        $checkedPage =
            min(
                $checkedPage,
                $checkedTotalPages
            );


        /*
         * =====================================================
         * NF-e PENDENTES
         * =====================================================
         */
        $nfesPending =
            $nfeRepository
                ->getNfesByCheckedStatus(
                    0,
                    $pendingPage,
                    $limitResult
                );


        /*
         * =====================================================
         * NF-e CONFERIDAS
         * =====================================================
         */
        $nfesChecked =
            $nfeRepository
                ->getNfesByCheckedStatus(
                    1,
                    $checkedPage,
                    $limitResult
                );


        /*
         * =====================================================
         * RESUMO FINANCEIRO DAS NF-e CONFERIDAS
         * =====================================================
         *
         * Recuperar somente o resumo financeiro das NF-e
         * que aparecem na página atual do accordion.
         *
         * Isso evita carregar dados financeiros de todas
         * as notas apenas para exibir uma página.
         */
        $nfeIds =
            array_values(
                array_filter(
                    array_map(
                        'intval',
                        array_column(
                            $nfesChecked,
                            'id'
                        )
                    ),
                    static fn(int $id): bool =>
                        $id > 0
                )
            );


        $purchaseDocumentsRepository =
            new PurchaseDocumentsRepository();


        $financialSummaryByNfeId =
            !empty($nfeIds)
                ? $purchaseDocumentsRepository
                    ->getFinancialSummaryByNfeIds(
                        $nfeIds
                    )
                : [];


        foreach ($nfesChecked as &$nfe) {

            $nfeId =
                (int) (
                    $nfe['id']
                    ?? 0
                );


            $nfe['financial'] =
                $financialSummaryByNfeId[$nfeId]
                ?? null;
        }


        unset($nfe);


        /*
         * =====================================================
         * DADOS DA VIEW
         * =====================================================
         */
        $this->data['nfes_pending'] =
            $nfesPending;


        $this->data['nfes_checked'] =
            $nfesChecked;


        $this->data['pagination_pending'] = [
            'current_page' =>
                $pendingPage,

            'total_pages' =>
                $pendingTotalPages,

            'total_records' =>
                $pendingTotal,

            'limit' =>
                $limitResult,
        ];


        $this->data['pagination_checked'] = [
            'current_page' =>
                $checkedPage,

            'total_pages' =>
                $checkedTotalPages,

            'total_records' =>
                $checkedTotal,

            'limit' =>
                $limitResult,
        ];


        $this->data['active_section'] =
            $activeSection;


        /*
         * Configurar elementos da página.
         */
        $pageElements = [
            'title_head' =>
                'NF-e Recebidas',

            'menu' =>
                'list-nfes',

            'buttonPermissions' =>
                [],
        ];


        $pageLayoutService =
            new PageLayoutService();


        $this->data =
            array_merge(
                $this->data,
                $pageLayoutService
                    ->configurePageElements(
                        $pageElements
                    )
            );


        /*
         * Carregar a View.
         */
        $loadView =
            new LoadViewService(
                'admsDaman/Views/nfes/list',
                $this->data
            );


        $loadView->loadView();
    }
}
