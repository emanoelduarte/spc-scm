<?php

namespace App\admsDaman\Controllers\suppliers;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationSupplierService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\AddressesRepository;
use App\admsDaman\Models\Repository\SuppliersRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller responsável por criar novos fornecedores.
 */
class CreateSupplier
{
    private array|string|null $data = null;

    public function index(): void
    {
        $this->data['form'] =
            filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        if (
            isset($this->data['form']['csrf_token'])
            && CSRFHelper::validateCSRFToken(
                'form_create_supplier',
                $this->data['form']['csrf_token']
            )
        ) {
            $this->addSupplier();
            return;
        }

        $this->viewCreateSupplier();
    }

    private function addSupplier(): void
    {
        $validationSupplier = new ValidationSupplierService();
        $this->data['errors'] =
            $validationSupplier->validate($this->data['form']);

        if (!empty($this->data['errors'])) {
            $this->viewCreateSupplier();
            return;
        }

        $supplierRepository = new SuppliersRepository();
        $supplierId =
            $supplierRepository->createSupplier($this->data['form']);

        if (!$supplierId) {
            $this->data['errors'][] = 'Fornecedor não cadastrado!';
            $this->viewCreateSupplier();
            return;
        }

        $addressRepository = new AddressesRepository();
        $addressCreated =
            $addressRepository->createAddress(
                $this->data['form'],
                (int) $supplierId,
                'supplier'
            );

        if (!$addressCreated) {
            $this->data['errors'][] =
                'Fornecedor cadastrado, porém o endereço não pôde ser salvo.';
            $this->viewCreateSupplier();
            return;
        }

        $_SESSION['success'] = 'Fornecedor cadastrado com sucesso!';

        header(
            "Location: {$_ENV['URL_ADM']}view-supplier/{$supplierId}"
        );
        return;
    }

    private function viewCreateSupplier(): void
    {
        /*
         * Tipos de fornecedor são carregados do banco.
         * Dessa forma novos tipos, como Obrigação Financeira,
         * não precisam ser incluídos manualmente na View.
         */
        $suppliersRepository = new SuppliersRepository();
        $this->data['getAllTypesSuppliersSelect'] =
            $suppliersRepository->getAllTypesSuppliersSelect();

        $pageElements = [
            'title_head' => 'Cadastrar Fornecedor',
            'menu' => 'list-suppliers',
            'buttonPermissions' => ['ListSuppliers'],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge(
            $this->data,
            $pageLayoutService->configurePageElements($pageElements)
        );

        $loadView = new LoadViewService(
            'admsDaman/Views/suppliers/create',
            $this->data
        );
        $loadView->loadView();
    }
}
