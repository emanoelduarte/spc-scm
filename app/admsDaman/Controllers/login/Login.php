<?php

namespace App\admsDaman\Controllers\login;

use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller Login
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 */
class Login 
{

/** @var array|string|null $dados Rece os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;


    public function index()
    {
        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/login/login", $this->data);
        $loadView->loadView();
    }
}