<?php 

namespace App\admsDaman\Controllers\errors;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Views\Services\LoadViewService;

class AccessDeniedController
{
    /** @var array $dados Rece os dados que devem ser enviados para a VIEW */
    private array $data = [];

    public function index()
    {
        $reason = $_GET['reason'] ?? 'restricted';

        if ($reason === 'no_access') {
            $this->data['errorIcon']    = 'fa-clock';
            $this->data['errorColor']   = 'text-warning';
            $this->data['errorTitle']   = 'Conta aguardando liberação';
            $this->data['errorMessage'] = 'Seu acesso ainda não foi configurado. Aguarde ou entre em contato com o administrador.';
        } else {
            $this->data['errorIcon']    = 'fa-lock';
            $this->data['errorColor']   = 'text-danger';
            $this->data['errorTitle']   = 'Acesso restrito';
            $this->data['errorMessage'] = 'Você não tem permissão para acessar esta página.';
        }

        $pageElements = [
            'title_head' => $this->data['errorTitle'],
            'menu'       => '',
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        $this->data['emailAdm'] = $_ENV['EMAIL_ADM'];

        $loadView = new LoadViewService("admsDaman/Views/errors/accessDenied", $this->data);
        $loadView->loadView();
    }
}