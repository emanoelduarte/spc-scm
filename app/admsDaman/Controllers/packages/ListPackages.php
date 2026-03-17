<?php

namespace App\admsDaman\Controllers\packages;

use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\PackagesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller responsável por listar pacotes do projeto
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\packages
 */
class ListPackages
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados */
    private int $limitResult = 10;

    /**
     * Recuperar e listar pacotes com paginação.
     * 
     * Este método recupera os pacotes a partir do repositório de pacotes com base na página atual e no limite
     * de registros por página. Gera os dados de paginação e carrega a visualização para exibir a lista de pacotes.
     * 
     * @param string|int $page Página atual para a exibição dos resultados. O padrão é 1.
     * 
     * @return void
     */
    public function index(string|int $page = 1): void
    {
        // Instanciar o Repository para recuperar os registros do banco de dados
         $listPackages = new PackagesRepository();
        $this->data['packages'] = $listPackages->getAllPackages((int) $page, (int) $this->limitResult);

        $this->data['pagination'] = PaginationService::generatePagination((int) $listPackages->getAmountPackages(), (int) $this->limitResult, (int) $page, 'list-packages');

        // Criar o título da página
        $this->data['title_head'] = "Listar Pacotes";

        // Ativar o item de Menu
        $this->data['menu'] = "list-packages";

        // Carregar a View
        $loadView = new LoadViewService("admsDaman/Views/packages/list", $this->data);
        $loadView->loadView();
    }
}