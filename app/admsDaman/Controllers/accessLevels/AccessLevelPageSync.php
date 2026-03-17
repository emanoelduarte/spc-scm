<?php

namespace App\admsDaman\Controllers\accessLevels;

use App\admsDaman\Controllers\Services\AccessLevelPageSyncService;

class AccessLevelPageSync
{
    public function index(): void
    {
        // Instanciar a classe de serviço de sincronização de página com nível de acesso
        $accessLevelPage = new AccessLevelPageSyncService();
        $resultAccessLevelPage = $accessLevelPage->accessLevelPageSync();


        // Se a sincronização entre nível de acesso e página for bem-sucedida
        if ($resultAccessLevelPage) {
            // Mensagem de Sucesso
            $_SESSION['success'] = "Sincronização entre nível de acesso e página realizada com sucesso!";
        } else {
            // Mensagem de Erro
            $_SESSION['error'] = "Sincronização entre nível de acesso e página não realizada!";
        }

        // Redirecionar para a página listar nível de acesso
        header("Location: {$_ENV['URL_ADM']}list-access-levels");

        return;
    }
}
?>