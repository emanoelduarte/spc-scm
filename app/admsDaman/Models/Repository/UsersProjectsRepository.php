<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class UsersProjectsRepository extends DbConnection
{
    /**
     * Retorna os IDs das obras vinculadas ao usuário.
     */
    public function getUserProjectIds(int $userId): array
    {
        $sql = "SELECT adms_daman_project_id
                FROM adms_daman_user_projects
                WHERE adms_daman_user_id = :user_id
                ORDER BY adms_daman_project_id ASC";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->bindValue(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            'intval',
            array_column(
                $result,
                'adms_daman_project_id'
            )
        );
    }


    /**
     * Verifica se o usuário possui determinada obra vinculada.
     */
    public function userHasProject(
        int $userId,
        int $projectId
    ): bool {

        $sql = "SELECT 1
                FROM adms_daman_user_projects
                WHERE adms_daman_user_id = :user_id
                AND adms_daman_project_id = :project_id
                LIMIT 1";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->bindValue(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':project_id',
            $projectId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }


    /**
     * Verifica se o usuário possui ao menos uma obra vinculada.
     */
    public function userHasAnyProject(int $userId): bool
    {
        $sql = "SELECT 1
                FROM adms_daman_user_projects
                WHERE adms_daman_user_id = :user_id
                LIMIT 1";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->bindValue(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }


    /**
     * Sincroniza as obras vinculadas ao usuário.
     */
    public function syncUserProjects(
        int $userId,
        array $projectIds
    ): bool {

        $connection = $this->getConnection();

        try {

            /*
             * Sanitizar IDs recebidos.
             */
            $projectIds = array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'intval',
                            $projectIds
                        ),
                        fn($id) => $id > 0
                    )
                )
            );


            /*
             * Recuperar vínculos atuais.
             */
            $currentProjects =
                $this->getUserProjectIds(
                    $userId
                );


            /*
             * Descobrir o que precisa ser adicionado.
             */
            $projectsToAdd =
                array_diff(
                    $projectIds,
                    $currentProjects
                );


            /*
             * Descobrir o que precisa ser removido.
             */
            $projectsToRemove =
                array_diff(
                    $currentProjects,
                    $projectIds
                );


            $connection->beginTransaction();


            /*
             * Adicionar novos vínculos.
             */
            if (!empty($projectsToAdd)) {

                $sqlInsert = "
                    INSERT INTO adms_daman_user_projects
                    (
                        adms_daman_user_id,
                        adms_daman_project_id,
                        created_at
                    )
                    VALUES
                    (
                        :user_id,
                        :project_id,
                        :created_at
                    )
                ";

                $stmtInsert =
                    $connection->prepare(
                        $sqlInsert
                    );


                foreach ($projectsToAdd as $projectId) {

                    $stmtInsert->bindValue(
                        ':user_id',
                        $userId,
                        PDO::PARAM_INT
                    );

                    $stmtInsert->bindValue(
                        ':project_id',
                        $projectId,
                        PDO::PARAM_INT
                    );

                    $stmtInsert->bindValue(
                        ':created_at',
                        date('Y-m-d H:i:s')
                    );

                    $stmtInsert->execute();
                }
            }


            /*
             * Remover vínculos que deixaram de existir.
             */
            if (!empty($projectsToRemove)) {

                $sqlDelete = "
                    DELETE FROM adms_daman_user_projects
                    WHERE adms_daman_user_id = :user_id
                    AND adms_daman_project_id = :project_id
                ";

                $stmtDelete =
                    $connection->prepare(
                        $sqlDelete
                    );


                foreach ($projectsToRemove as $projectId) {

                    $stmtDelete->bindValue(
                        ':user_id',
                        $userId,
                        PDO::PARAM_INT
                    );

                    $stmtDelete->bindValue(
                        ':project_id',
                        $projectId,
                        PDO::PARAM_INT
                    );

                    $stmtDelete->execute();
                }
            }


            $connection->commit();


            GenerateLog::generateLog(
                'info',
                'Vínculos do usuário com obras atualizados.',
                [
                    'user_id' => $userId,
                    'projects' => $projectIds,
                ]
            );


            return true;

        } catch (Exception $err) {

            if ($connection->inTransaction()) {
                $connection->rollBack();
            }


            GenerateLog::generateLog(
                'error',
                'Erro ao atualizar vínculos do usuário com obras.',
                [
                    'user_id' => $userId,
                    'error' => $err->getMessage(),
                ]
            );


            return false;
        }
    }
}