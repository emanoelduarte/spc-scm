<?php

namespace App\admsDaman\Controllers\groupsPages;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\GroupsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para listar grupos
 *
 * Esta classe é responsável por recuperar e exibir uma lista de grupos no sistema. Utiliza um repositório
 * para obter dados dos grupos e um serviço de paginação para gerenciar a navegação entre páginas de resultados.
 * Em seguida, carrega a visualização correspondente com os dados recuperados.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\groupsPages;
 */
class ListGroupsPages
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados */
    private int $limitResult = 10;

    /**
     * Recuperar e listar grupos com paginação.
     * 
     * Este método recupera os grupos a partir do repositório de grupos com base na página atual e no limite
     * de registros por página. Gera os dados de paginação e carrega a visualização para exibir a lista de grupos.
     * 
     * @param string|int $page Página atual para a exibição dos resultados. O padrão é 1.
     * 
     * @return void
     */
    public function index(string|int $page = 1): void
    {
        // Instanciar o Repository para recuperar os registros do banco de dados
        $listGroups = new GroupsRepository();
        $this->data['groups'] = $listGroups->getAllGroups((int) $page, (int) $this->limitResult);

        $this->data['pagination'] = PaginationService::generatePagination((int) $listGroups->getAmountGroups(), (int) $this->limitResult, (int) $page, 'list-groups-pages');

        $pageElements = [
            'title_head' => "Listar Grupos",
            'menu' => "list-groups-pages",
            'buttonPermissions' => ["CreateGroupPage", "ListGroupsPages", "ViewGroupPage", "UpdateGroupPage", "DeleteGroupPage"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Criar o título da página
        $this->data['title_head'] = "Listar Grupos";

        // Ativar o item de Menu
        $this->data['menu'] = "list-groups-pages";

        // Carregar a View
        $loadView = new LoadViewService("admsDaman/Views/groupsPages/list", $this->data);
        $loadView->loadView();
    }
}
