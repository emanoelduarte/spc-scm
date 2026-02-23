<?php

namespace App\admsDaman\Controllers\users;

use App\admsDaman\Models\Repository\UsersRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ListUsers 
{
     /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /**
     * Recuperar os ultimos usuários
     * 
     * @return void
     */
    public function index(): void
    {
        // Instanciar o Repository para recuperar os registros do banco de dados
        $listUsers = new UsersRepository();
        $listUsers->getAllUsers();

        $this->data['users'] = $listUsers->getAllUsers();

        // Carregar a View do Listar Usuários
        $loadView = new LoadViewService("admsDaman/Views/users/list", $this->data);
        $loadView->loadView();
    }
}