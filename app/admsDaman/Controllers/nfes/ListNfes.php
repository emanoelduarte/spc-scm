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
     * @return void
     */
    public function index(): void
    {
        // Recuperar NF-e cadastradas no banco.
        $nfeRepository = new NfeRepository();

        // Recuperar informações da última sincronização com a SEFAZ.
        $this->data['nfe_sync'] = $nfeRepository->getSyncData(
            $_ENV['NFE_CNPJ']
        );

        $nfes = $nfeRepository->getAllNfes();


        /*
         * =====================================================
         * RESUMO FINANCEIRO DAS NF-e
         * =====================================================
         *
         * Recuperar os dados financeiros em lote para evitar
         * uma consulta por NF-e (problema N+1).
         *
         * NF-e ainda não lançada receberá:
         *
         * financial = null
         *
         * NF-e lançada receberá um resumo com:
         *
         * - purchase_document_id;
         * - payment_schedule_status;
         * - financial_status;
         * - installments_count;
         * - total_amount;
         * - paid_amount;
         * - remaining_amount.
         */
        $nfeIds =
            array_values(
                array_filter(
                    array_map(
                        'intval',
                        array_column(
                            $nfes,
                            'id'
                        )
                    ),
                    static fn(int $id): bool => $id > 0
                )
            );


        $purchaseDocumentsRepository =
            new PurchaseDocumentsRepository();


        $financialSummaryByNfeId =
            $purchaseDocumentsRepository
                ->getFinancialSummaryByNfeIds(
                    $nfeIds
                );


        foreach ($nfes as &$nfe) {

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


        $this->data['nfes_pending'] = array_filter(
            $nfes,
            fn($nfe) => (int) $nfe['is_checked'] === 0
        );

        $this->data['nfes_checked'] = array_filter(
            $nfes,
            fn($nfe) => (int) $nfe['is_checked'] === 1
        );

        // Configurar elementos da página.
        $pageElements = [
            'title_head' => 'NF-e Recebidas',
            'menu' => 'list-nfes',
            'buttonPermissions' => [],
        ];

        $pageLayoutService = new PageLayoutService();

        $this->data = array_merge(
            $this->data,
            $pageLayoutService->configurePageElements($pageElements)
        );

        // Carregar a View.
        $loadView = new LoadViewService(
            'admsDaman/Views/nfes/list',
            $this->data
        );

        $loadView->loadView();
    }
}
