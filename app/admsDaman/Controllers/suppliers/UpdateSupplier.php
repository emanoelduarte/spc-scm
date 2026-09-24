<?php

namespace App\admsDaman\Controllers\suppliers;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationSupplierService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\AddressesRepository;
use App\admsDaman\Models\Repository\SuppliersRepository;
use App\admsDaman\Views\Services\LoadViewService;

class UpdateSupplier
{
    private array|string|null $data = null;

    public function index(int|string $id): void
    {
        $this->data['form'] =
            filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        if (
            isset($this->data['form']['csrf_token'])
            && CSRFHelper::validateCSRFToken(
                'form_update_supplier',
                $this->data['form']['csrf_token']
            )
        ) {
            $this->editSupplier();
            return;
        }

        $suppliersRepository = new SuppliersRepository();
        $this->data['form'] =
            $suppliersRepository->getSupplier((int) $id);

        if (!$this->data['form']) {
            GenerateLog::generateLog(
                'error',
                'Fornecedor não encontrado',
                ['id' => (int) $id]
            );

            $_SESSION['error'] = 'Fornecedor não encontrado!';
            header("Location: {$_ENV['URL_ADM']}list-suppliers");
            return;
        }

        /*
         * O endereço fica em tabela própria. Recuperamos os campos
         * e adicionamos ao mesmo array do formulário para manter a
         * View e a validação simétricas ao cadastro.
         */
        $addressRepository = new AddressesRepository();
        $address =
            $addressRepository->getAddressByEntity(
                (int) $id,
                'supplier'
            );

        if ($address) {
            foreach (
                [
                    'zip_code',
                    'street',
                    'number',
                    'complement',
                    'neighborhood',
                    'city',
                    'state',
                ] as $field
            ) {
                $this->data['form'][$field] =
                    $address[$field] ?? null;
            }
        }

        $this->viewUpdateSupplier();
    }

    private function viewUpdateSupplier(): void
    {
        $suppliersRepository = new SuppliersRepository();
        $this->data['getAllTypesSuppliersSelect'] =
            $suppliersRepository->getAllTypesSuppliersSelect();

        $pageElements = [
            'title_head' => 'Editar Fornecedor',
            'menu' => 'list-suppliers',
            'buttonPermissions' => [
                'ListSuppliers',
                'ViewSupplier',
            ],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge(
            $this->data,
            $pageLayoutService->configurePageElements($pageElements)
        );

        $loadView = new LoadViewService(
            'admsDaman/Views/suppliers/update',
            $this->data
        );
        $loadView->loadView();
    }

    private function editSupplier(): void
    {
        $validationSupplier = new ValidationSupplierService();
        $this->data['errors'] =
            $validationSupplier->validate($this->data['form']);

        if (!empty($this->data['errors'])) {
            $this->viewUpdateSupplier();
            return;
        }

        $supplierId = (int) ($this->data['form']['id'] ?? 0);

        $suppliersRepository = new SuppliersRepository();
        $supplierUpdated =
            $suppliersRepository->updateSupplier($this->data['form']);

        if (!$supplierUpdated) {
            $this->data['errors'][] = 'Fornecedor não editado!';
            $this->viewUpdateSupplier();
            return;
        }

        $addressRepository = new AddressesRepository();
        $addressUpdated =
            $addressRepository->updateAddress(
                $this->data['form'],
                $supplierId,
                'supplier'
            );

        if (!$addressUpdated) {
            $this->data['errors'][] =
                'Os dados do fornecedor foram salvos, mas o endereço não pôde ser atualizado.';
            $this->viewUpdateSupplier();
            return;
        }

        $_SESSION['success'] = 'Fornecedor editado com sucesso!';

        header(
            "Location: {$_ENV['URL_ADM']}view-supplier/{$supplierId}"
        );
        return;
    }
}
