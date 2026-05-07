<?php

namespace App\admsDaman\Controllers\pages;

use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\PagesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para listar Páginas
 *
 * Esta classe é responsável por recuperar e exibir uma lista de Páginas no sistema. Utiliza um repositório
 * para obter dados dos Páginas e um serviço de paginação para gerenciar a navegação entre páginas de resultados.
 * Em seguida, carrega a visualização correspondente com os dados recuperados.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\pages;
 */
class ListPages
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados */
    private int $limitResult = 10;

    /**
     * Recuperar e listar páginas com paginação.
     * 
     * Este método recupera os páginas a partir do repositório de páginas com base na página atual e no limite
     * de registros por página. Gera os dados de paginação e carrega a visualização para exibir a lista de páginas.
     * 
     * @param string|int $page Página atual para a exibição dos resultados. O padrão é 1.
     * 
     * @return void
     */
    public function index(string|int $page = 1): void
    {
        $this->data['search'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Instanciar o Repository para recuperar os registros do banco de dados
        $listPages = new PagesRepository();
        $this->data['pages'] = $listPages->getAllPages(
            (int) $page, 
            (int) $this->limitResult,
            $this->data['search']
        );

        $this->data['pagination'] = PaginationService::generatePagination(
            (int) $listPages->getAmountPages($this->data['search']), 
            (int) $this->limitResult, 
            (int) $page, 'list-pages'
        );

        // Criar o título da página
        $this->data['title_head'] = "Listar Paginas";

        // Ativar o item de Menu
        $this->data['menu'] = "list-pages";

        // Carregar a View
        $loadView = new LoadViewService("admsDaman/Views/pages/list", $this->data);
        $loadView->loadView();
    }
}
