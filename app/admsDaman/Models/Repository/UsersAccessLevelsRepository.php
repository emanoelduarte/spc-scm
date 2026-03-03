<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;
use PDO;

class UsersAccessLevelsRepository extends DbConnection
{
    public function getUsersAccessLevels(int $id): array|bool
    {
        // Criar a Query para recuperar os dados
        // nome_da_tabela AS (recebe o apelido) lev ON (onde na tabela identificada por na coluna) [lev.id]=chave primária que deve ser igual a chave estrangeira da tabela que guarda o id do level dos usuários
        $sql = 'SELECT lev.name
            FROM adms_daman_users_access_levels AS usr_lev
            INNER JOIN adms_daman_access_levels AS lev ON lev.id=usr_lev.adms_daman_access_level_id
            WHERE usr_lev.adms_daman_user_id = :adms_daman_user_id
            ORDER BY usr_lev.id DESC';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir Links por valores
        $stmt->bindParam(':adms_daman_user_id', $id, PDO::PARAM_INT);

        //Executar a Query
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
