<?php

namespace App\admsDaman\Controllers\Services;

use App\admsDaman\Models\Repository\ButtonPermissionUserRepository;
use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;

/**
 * Serviço responsável por configurar os elementos da página, como título, menu ativo e permissões de botões, com base no nível de acesso do usuário.
 */
class PageLayoutService
{
    public function configurePageElements(array $data): array
    {
        // Verificar se o usuário tem o nível de acesso Super Administrador.
        // Nivel de acesso Super Administrador tem acesso a todas as páginas/funcionalidades do sistema, então não é necessário verificar as permissões de botões para este nível de acesso.
        $usersAccessLevels = new UsersAccessLevelsRepository(); // Instanciar o repositório para verificar o nível de acesso do usuário
        $userAcessLevel = $usersAccessLevels->getUsersAccessLevelsArray($_SESSION['user_id']); // Recuperar os níveis de acesso do usuário e armazenar em um array para verificar se o usuário tem o nível de acesso Super Administrador

        if (in_array(1, $userAcessLevel)) { // Verificar se o usuário tem o nível de acesso Super Administrador (id 1)
            return $data;
        }

        // Criar o título da página
        $pageElements['title_head'] = $data['title_head'] ?? "";

        // Ativar o item de Menu
        $pageElements['menu'] = $data['menu'] ?? "";

        // Apresentar ou ocutar botão
        $buttonPermission = new ButtonPermissionUserRepository();
        $pageElements['buttonPermissions'] = $buttonPermission->buttonPermission($data['buttomPermissions'] ?? []);

        return $pageElements;
    }
}
