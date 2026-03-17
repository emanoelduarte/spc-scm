<?php

namespace App\admsDaman\Controllers\Services;

use App\admsDaman\Models\Repository\AccessLevelsRepository;
use App\admsDaman\Models\Repository\PagesRepository;

/**
 * Classe de Sincronização de Nível de acesso com a página
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\Services
 */
class AccessLevelPageSyncService
{
    //
    public function accessLevelPageSync(): bool
    {
        // Instanciar o Repository 'PagesRepository' e recuperar todas as páginas em um array do banco de dados, somente id.
        $pages = new PagesRepository();
        $resultPages = $pages->getPagesArray();

        var_dump($resultPages);

        // Instanciar o Repository 'AccessLevelsRepository' e recuperar todas os níveis de acessso em um array do banco de dados.
        $accessLevelsPages = new AccessLevelsRepository();
        $resultAccessLevels = $accessLevelsPages->getAllAccessLevelsSelect();

        var_dump($resultAccessLevels);


        return true;
    }
}
?>