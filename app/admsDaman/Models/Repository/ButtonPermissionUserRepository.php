<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;
use PDO;

/**
 * Repository responsável em buscar o usuário no banco de dados para realizar login.
 *
 *
 * @package App\admsDaman\Models\Repository
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 */
class ButtonPermissionUserRepository extends DbConnection
{

    public function buttonPermission(array $button): array|bool
    {
        // Gerar string de placeholders para a consulta SQL
        $placeholders = implode(',', array_fill(0, count($button), '?'));

        // Verificar as permissões do usuário para cada botão e retornar um array com as permissões em relação a página atual
        $sql = "SELECT 
                    adp.controller
                FROM 
                    adms_daman_users_access_levels AS adual
                LEFT JOIN
                    adms_daman_access_levels_pages AS adalp ON adalp.adms_daman_access_level_id = adual.adms_daman_access_level_id
                LEFT JOIN
                    adms_daman_pages AS adp ON adp.id = adalp.adms_daman_page_id
                WHERE 
                    adual.adms_daman_user_id = ?
                    AND adp.controller IN ($placeholders)
                    AND adalp.permission = 1";

        // Preparar a consulta SQL
        $stmt = $this->getConnection()->prepare($sql);

        // Combinar o valor do ID do usuário com os valores dos botões para a consulta
        $params = array_merge([$_SESSION['user_id']], $button);

        // Executar a consulta SQL com os parâmetros
        $stmt->execute($params);

        //Ler os resultados e retornar um array associativo com as permissões dos botões
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retornar apenas os valores de 'controller' como um array simples
        return $result ? array_column($result, 'controller') : [];
    }
}
