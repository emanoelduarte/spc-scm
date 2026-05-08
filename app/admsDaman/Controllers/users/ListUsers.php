<?php

namespace App\admsDaman\Controllers\users;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\ButtonPermissionUserRepository;
use App\admsDaman\Models\Repository\MenuPermissionUserRepository;
use App\admsDaman\Models\Repository\UsersRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ListUsers
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /** @var int $page Recebe a quantidade de registros que deve retornar do banco de dados para ser usado na paginação*/
    private int $limitResult = 10;

    /**
     * Recuperar os ultimos usuários
     * 
     * @return void
     */
    public function index(string|int $page = 1): void
    {
        // Instanciar o Repository para recuperar os registros do banco de dados
        $listUsers = new UsersRepository();
        $listUsers->getAllUsers();

        $this->data['users'] = $listUsers->getAllUsers((int) $page, (int) $this->limitResult);

        $this->data['pagination'] = PaginationService::generatePagination((int) $listUsers->getAmountUsers(), (int) $this->limitResult, (int) $page, 'list-users');

        $pageElements = [
            'title_head' => "Listar Usuários",
            'menu' => "list-users",
            'buttonPermissions' => ["CreateUser", "ViewUser", "UpdateUser", "DeleteUser"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a View do Listar Usuários
        $loadView = new LoadViewService("admsDaman/Views/users/list", $this->data);
        $loadView->loadView();
    }
}
