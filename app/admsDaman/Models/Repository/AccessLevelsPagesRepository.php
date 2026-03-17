<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;
use PDO;

class AccessLevelsPagesRepository extends DbConnection
{
    public function getPagesAccessLevelsArray(int $accessLevel): array|bool
    {
        // Query para recuperar os registros do banco de dados
        $sql = 'SELECT adms_daman_page_id 
        FROM adms_daman_access_levels_pages
        WHERE adms_daman_access_level_id = :adms_daman_access_level_id';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Substirui o link da condição
        $stmt->bindValue(':adms_daman_access_level_id', $accessLevel, PDO::PARAM_INT);

        // Executar a Query
        $stmt->execute();

        // Ler os Registos e retornar os dados com fetchAll
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retornar apenas os valores de 'adms_daman_page_id' como array simples
        return $result ? array_column($result, 'adms_daman_page_id') : false;
    }
}
?>