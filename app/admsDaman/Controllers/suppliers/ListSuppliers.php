<?php

namespace App\admsDaman\Controllers\suppliers;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\SuppliersRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller responsável por listar Fornecedores existentes e ativos ou não com paginação
 * 
 */
class ListSuppliers
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados para ser usado na paginação*/
    private int $limitResult = 10;

    /**
     * Recuperar os ultimos Fornecedores
     * 
     * @return void
     */
    public function index(string|int $page = 1): void
    {
        $this->data['search'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Instanciar o Repository para recuperar os registros do banco de dados
        $listSuppliers = new SuppliersRepository();
        $listSuppliers->getAllSuppliers();

        $this->data['suppliers'] = $listSuppliers->getAllSuppliers(
            (int) $page, 
            (int) $this->limitResult,
            $this->data['search']
        );

        $this->data['pagination'] = PaginationService::generatePagination(
            (int) $listSuppliers->getAmountSuppliers($this->data['search']),
            (int) $this->limitResult,
            (int) $page,
            'list-suppliers'
        );

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Listar Fornecedores",
            'menu' => "list-suppliers",
            'buttonPermissions' => ["CreateSupplier", "ViewSupplier", "UpdateSupplier", "DeleteSupplier"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a View do Listar Fornecedores
        $loadView = new LoadViewService("admsDaman/Views/suppliers/list", $this->data);
        $loadView->loadView();
    }
}
