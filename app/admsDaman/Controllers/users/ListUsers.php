<?php

namespace App\admsDaman\Controllers\users;

use App\admsDaman\Models\Repository\UsersRepository;

class ListUsers 
{
    public function index()
    {
        // Carregar a página users
        echo "Pagina Listar usuários<br><br>";

        $listUsers = new UsersRepository();
        $listUsers->getAllUsers();
    }
}