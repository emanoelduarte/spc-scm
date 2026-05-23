<?php

namespace App\admsDaman\Controllers\purchasing;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\PurchasingQuoteRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ListPurchasingQuotes
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados para ser usado na paginação*/
    private int $limitResult = 10;

    public function index(string|int $page = 1): void
    {
        $this->data['search'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Instanciar o Repository para recuperar os registros do banco de dados
        $listPurchasingsQuotes = new PurchasingQuoteRepository();
        $listPurchasingsQuotes->getAllPurchasingsQuotes();

        $this->data['purchasingsQuote'] = $listPurchasingsQuotes->getAllPurchasingsQuotes(
            (int) $page,
            (int) $this->limitResult,
            $this->data['search']
        );

        $this->data['pagination'] = PaginationService::generatePagination(
            (int) $listPurchasingsQuotes->getAmountPurchasingQuotes(),
            (int) $this->limitResult,
            (int) $page,
            'list-purchasing-quotes'
        );

        $this->data['purchasing_number'] = $this->data['search'];

        // Instanciar o repositório para preencher os selects.
        $getAllProjectsSelect = new ProjectsRepository();
        $this->data['getAllProjectsSelect'] = $getAllProjectsSelect->getAllProjectsSelect();

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Listar Compras",
            'menu' => "list-purchasing-quotes",
            'buttonPermissions' => ["ViewPurchasingQuote"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/purchasing/listQuotes", $this->data);
        $loadView->loadView();
    }
}
