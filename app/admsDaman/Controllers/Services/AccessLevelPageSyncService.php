<?php

namespace App\admsDaman\Controllers\Services;

use App\admsDaman\Models\Repository\AccessLevelsPagesRepository;
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
        $accessLevels = new AccessLevelsRepository();
        $resultAccessLevels = $accessLevels->getAllAccessLevelsSelect();

        // Array para armazenar as páginas do nível de acesso.
        $accessLevelPages = [];

        // Percorrer os níveis de acesso e recuperar as permissões cadastradas no banco de dados
        foreach ($resultAccessLevels as $accessLevel) {
            extract($accessLevel);

            // Recuperar todas as páginas do nível de acesso em um array.
            $accessLevelsPages = new AccessLevelsPagesRepository();
            $resultAccessLevelsPages = $accessLevelsPages->getPagesAccessLevelsArray($id);

            // Atribuir no array as páginas do nível de acesso
            $accessLevelPages[$id] = $resultAccessLevelsPages ? $resultAccessLevelsPages : [];
        }

        var_dump($accessLevelPages);

        // Percorrer as páginas do nível de acesso e verificar se o nível de acesso tem permissão para cadastrar página
        foreach ($accessLevelPages as $acessLevelId => $accessLevelPages) {
            // Comprar as páginas que o nível de acesso não possui permissão e criar o array com essas páginas
            $noAccessLevelPages = array_values(array_diff($resultPages, $accessLevelPages));

            var_dump($noAccessLevelPages);
        }

        var_dump($resultAccessLevels);

        // Chamar o método do repositório cadastrar página para nível de acesso

        return false;
    }
}
?>