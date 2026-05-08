<?php

namespace App\admsDaman\Controllers\categories;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\CategoriesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para listar Categorias
 *
 * Esta classe é responsável por recuperar e exibir uma lista de Categorias no sistema. Utiliza um repositório
 * para obter dados das Categorias e um serviço de paginação para gerenciar a navegação entre Categorias de resultados.
 * Em seguida, carrega a visualização correspondente com os dados recuperados.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\categories;
 */
class ListCategories
{

    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados */
    private int $limitResult = 10;

    /**
     * Recuperar e listar categorias com paginação.
     * 
     * Este método recupera as categorias a partir do repositório de categorias com base na página atual e no limite
     * de registros por página. Gera os dados de paginação e carrega a visualização para exibir a lista de categorias.
     * 
     * @param string|int $page Página atual para a exibição dos resultados. O padrão é 1.
     * 
     * @return void
     */
    public function index(string|int $page = 1): void
    {
        // Instanciar o Repository para recuperar os registros do banco de dados
        $listCategories = new CategoriesRepository();
        $this->data['categories'] = $listCategories->getAllCategories((int) $page, (int) $this->limitResult);

        $this->data['pagination'] = PaginationService::generatePagination((int) $listCategories->getAmountCategories(), (int) $this->limitResult, (int) $page, 'list-categories');

        $pageElements = [
            'title_head' => "Listar Categorias",
            'menu' => "list-categories",
            'buttonPermissions' => ["CreateCategory", "ViewCategory", "UpdateCategory", "DeleteCategory"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a View
        $loadView = new LoadViewService("admsDaman/Views/categories/list", $this->data);
        $loadView->loadView();
    }

}