<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\FinancialRenegotiationsRepository;
use App\admsDaman\Models\Services\FinancialRenegotiationService;
use App\admsDaman\Views\Services\LoadViewService;
use Throwable;

class CreateFinancialRenegotiation
{
    private array|string|null $data = null;

    public function index(): void
    {
        $this->data['form'] =
            filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?? [];

        if (!empty($this->data['form'])) {
            $token =
                (string) ($this->data['form']['csrf_token'] ?? '');

            if (
                !CSRFHelper::validateCSRFToken(
                    'form_create_financial_renegotiation',
                    $token
                )
            ) {
                $this->data['errors'][] =
                    'Token de segurança inválido ou expirado.';

                $this->viewRenegotiation();
                return;
            }

            $this->createRenegotiation();
            return;
        }

        $this->viewRenegotiation();
    }

    private function createRenegotiation(): void
    {
        try {
            $this->data['form']['created_by'] =
                (int) ($_SESSION['user_id'] ?? 0);

            $service =
                new FinancialRenegotiationService();

            $purchaseDocumentId =
                $service->create($this->data['form']);

            $_SESSION['success'] =
                'Renegociação financeira cadastrada com sucesso.';

            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'view-purchase-document/'
                    . $purchaseDocumentId
            );
            exit;
        } catch (Throwable $err) {
            $this->data['errors'][] =
                $err->getMessage();

            $this->viewRenegotiation();
        }
    }

    private function viewRenegotiation(): void
    {
        $repository =
            new FinancialRenegotiationsRepository();

        $this->data['suppliers'] =
            $repository->getSuppliersWithEligibleInstallments();

        $pageElements = [
            'title_head' => 'Nova Renegociação Financeira',
            'menu' => 'list-purchase-documents',
            'buttonPermissions' => [],
        ];

        $pageLayoutService =
            new PageLayoutService();

        $this->data = array_merge(
            $this->data ?? [],
            $pageLayoutService->configurePageElements($pageElements)
        );

        $loadView =
            new LoadViewService(
                'admsDaman/Views/accountsPayable/createFinancialRenegotiation',
                $this->data
            );

        $loadView->loadView();
    }
}
