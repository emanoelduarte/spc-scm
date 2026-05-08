<?php

namespace App\admsDaman\Controllers\accessLevels;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\AccessLevelsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller responsável em listar o níveis de acesso.
 * 
 * Esta classe é responsável por recuperar um lista de níveis de acesso do bancod de dados do sistema. 
 * Utiliza um repositório para obter esses dados.
 * Sem seguida exibe os dados para o usuário ao chamar a visualização correspondente com os dados recuperados
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\accessLevels
 */

class ListAccessLevels
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados */
    private int $limitResult = 10;

    /**
     * Recupera e lista os níveis de acesso com paginação
     * 
     * Este método recupera os níveis de acesso a partir de um repositório de níveis de acesso com base na página atual e no limite de registros por página.
     * Gera os dados de paginação e carrega a visualização para exibir a lista de niveis de acesso recuperada
     * 
     * @param string|int $page Página atual para a exibição dos resultados. O padrão é 1.
     * 
     * @return void
     */
    public function index(string|int $page = 1): void
    {

        // Instaciar o Repository para recueparar os registros do banco de dados
        $listLevels = new AccessLevelsRepository();
        $this->data['levelsAccess'] = $listLevels->getAllAccessLevels((int) $page, (int) $this->limitResult);

        $this->data['pagination'] = PaginationService::generatePagination(
            (int) $listLevels->getAmountAccessLevels(),
            (int) $this->limitResult,
            (int) $page,
            'list-access-levels'
        );

        $pageElements = [
            'title_head' => "Níveis de Acesso",
            'menu' => "list-access-levels",
            'buttonPermissions' => ['CreateAccessLevel', "ViewAccessLevel", "UpdateAccessLevel", "AccessLevelPageSync", "DeleteAccessLevel", "ListAccessLevelsPermissions"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        //Carregar a View Listar níveis de acesso
        $loadViewAccessLevels = new LoadViewService("admsDaman/Views/accessLevels/list", $this->data);
        $loadViewAccessLevels->loadView();
    }
}
