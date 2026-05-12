<?php

namespace App\admsDaman\Controllers\suppliers;

use App\admsDaman\Controllers\Services\CreateSupplierService;
use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationSupplierService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\AddressesRepository;
use App\admsDaman\Models\Repository\SuppliersRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller Responsável por criar novos Fornecedores
 */
class CreateSupplier
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    public function index()
    {

        // Receber os dados do formulário de cadastro de usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_create_supplier', $this->data['form']['csrf_token'])) {

            $this->addSupplier();
        } else {
            $this->viewCreateSupplier();
        }
    }

    public function addSupplier()
    {
        // Instaciar a classe que valida os dados do formulário se há algum campo vazio
        $validationSupplier = new ValidationSupplierService();
        $this->data['errors'] = $validationSupplier->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewCreateSupplier();

            return;
        }

        // // Instanciar o Repository para cadastrar o fornecedor
        $supplierCreate = new SuppliersRepository();
        $supplierId = $supplierCreate->createSupplier($this->data['form']);

        // Salvar endereço vinculado ao fornecedor
        $addressRepository = new AddressesRepository();
        $supplierAdress = $addressRepository->createAddress($this->data['form'], $supplierId, 'supplier');

        // Acesso o IF se o repository retornou true
        if ($supplierId && $supplierAdress) {
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Fornecedor cadastrado com sucesso!";

            // Redirecionar o usuário para a página de visualizar usuário
            header("Location: {$_ENV['URL_ADM']}view-supplier/$supplierId");

            return;
        } else {
            // Criar a mensagem de sucesso ao logar
            $this->data['errors'][] = "Fornecedor não cadastrado!";
        }
    }

    public function viewCreateSupplier()
    {

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Cadastrar Fornecedor",
            'menu' => "list-suppliers",
            'buttonPermissions' => ["ListSuppliers"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/suppliers/create", $this->data);
        $loadView->loadView();
    }
}