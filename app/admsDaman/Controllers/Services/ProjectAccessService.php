<?php

namespace App\admsDaman\Controllers\Services;

use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;
use App\admsDaman\Models\Repository\UsersProjectsRepository;

class ProjectAccessService
{
    /**
     * Níveis que possuem acesso global às obras.
     *
     * 1 = Super Admin
     * 2 = Admin
     * 5 = Comprador
     * 6 = Almoxarife
     */
    private const GLOBAL_PROJECT_ACCESS_LEVELS = [
        1,
        2,
        5,
        6,
    ];

    /**
     * Verifica se o usuário possui acesso global às obras.
     */
    public function canAccessAllProjects(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }


        $usersAccessLevelsRepository =
            new UsersAccessLevelsRepository();


        /*
     * Recuperar somente os IDs dos níveis
     * vinculados ao usuário.
     *
     * Exemplo:
     * [7, 8]
     */
        $userAccessLevels =
            $usersAccessLevelsRepository
            ->getUserAccessLevelsArray($userId);


        if (empty($userAccessLevels)) {
            return false;
        }


        /*
     * Garantir comparação entre inteiros.
     */
        $userAccessLevels = array_map(
            'intval',
            $userAccessLevels
        );


        foreach (self::GLOBAL_PROJECT_ACCESS_LEVELS as $accessLevelId) {

            if (
                in_array(
                    $accessLevelId,
                    $userAccessLevels,
                    true
                )
            ) {
                return true;
            }
        }


        return false;
    }


    /**
     * Retorna os IDs das obras que limitam o acesso do usuário.
     *
     * null:
     *     usuário possui acesso global e não deve ser
     *     limitado por obra.
     *
     * []:
     *     usuário não possui nenhuma obra vinculada.
     *
     * [1, 5, 8]:
     *     usuário só pode acessar essas obras.
     */
    public function getAccessibleProjectIds(
        int $userId
    ): ?array {

        if ($this->canAccessAllProjects($userId)) {
            return null;
        }


        $usersProjectsRepository =
            new UsersProjectsRepository();


        return $usersProjectsRepository
            ->getUserProjectIds($userId);
    }


    /**
     * Verifica se o usuário pode acessar determinada obra.
     */
    public function canAccessProject(
        int $userId,
        int $projectId
    ): bool {

        if ($userId <= 0 || $projectId <= 0) {
            return false;
        }


        /*
         * Usuários com acesso global não dependem
         * de vínculo na tabela usuário x obra.
         */
        if ($this->canAccessAllProjects($userId)) {
            return true;
        }


        $usersProjectsRepository =
            new UsersProjectsRepository();


        return $usersProjectsRepository
            ->userHasProject(
                $userId,
                $projectId
            );
    }


    /**
     * Verifica se o usuário possui acesso a pelo menos
     * uma obra.
     *
     * Usuários privilegiados sempre retornam true.
     */
    public function hasProjectAccess(
        int $userId
    ): bool {

        if ($this->canAccessAllProjects($userId)) {
            return true;
        }


        $usersProjectsRepository =
            new UsersProjectsRepository();


        return $usersProjectsRepository
            ->userHasAnyProject($userId);
    }
}
