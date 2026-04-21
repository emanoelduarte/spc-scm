<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;
use PDO;

class PagesRoutesRepository extends DbConnection
{
    public function getPage(string $controller): array|bool
    {        
        // QUERY para recuperar o registro do banco de dados sobre a página
        $sql = 'SELECT ap.id AS id_ap, ap.directory, ap.public_page,
                app.name AS name_app
                FROM adms_daman_pages AS ap
                INNER JOIN adms_daman_packages_pages AS app ON app.id=ap.adms_daman_packages_page_id
                WHERE ap.controller = :controller
                AND ap.page_status = 1
                LIMIT 1';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':controller', $controller, PDO::PARAM_STR);

        // Executar a QUERY
        $stmt->execute();

        // Ler o registro e retornar
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkUserPagePermission(int $pageId)
    {

        // QUERY para verificar a permissão do usuário em relação à página
        $sql = 'SELECT aulp.adms_daman_access_level_id, alp.permission            
                FROM adms_daman_users_access_levels AS aulp
                INNER JOIN adms_daman_access_levels_pages As alp ON alp.adms_daman_access_level_id = aulp.adms_daman_access_level_id
                WHERE aulp.adms_daman_user_id = :adms_daman_user_id
                AND alp.adms_daman_page_id = :adms_daman_page_id
                AND alp.permission = 1
                LIMIT 1';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':adms_daman_user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':adms_daman_page_id', $pageId, PDO::PARAM_INT);

        // Executar a QUERY
        $stmt->execute();

        // Ler o registro e retornar
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }
}
