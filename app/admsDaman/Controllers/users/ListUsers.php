<?php

namespace App\admsDaman\Controllers\users;

use App\admsDaman\Controllers\Services\PaginationService;
use App\admsDaman\Models\Repository\ButtonPermissionUserRepository;
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

        // Criar o título da página
        $this->data['title_head'] = "Listar Usuários";

        // Ativar o item de Menu
        $this->data['menu'] = "list-users";

        // Apresentar ou ocutar botão
        $button = ['CreateUser', "ViewUser", "UpdateUser", "DeleteUser"];
        $buttonPermission = new ButtonPermissionUserRepository(); 
        $this->data['buttonPermissions'] = $buttonPermission->buttonPermission($button);

        // Carregar a View do Listar Usuários
        $loadView = new LoadViewService("admsDaman/Views/users/list", $this->data);
        $loadView->loadView();
    }
}