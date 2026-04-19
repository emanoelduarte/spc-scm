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
}
