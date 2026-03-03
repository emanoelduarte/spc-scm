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

    public function getUsersAccessLevelsArray(int $id): array|bool
    {
        // Query para recuperar os registros do banco de dados
        // (recupera os níveis de acesso dos usuários)
        $sql = 'SELECT adms_daman_access_level_id
        FROM adms_daman_users_access_levels
        WHERE adms_daman_user_id = :adms_daman_user_id';

        // Preparar a Query -> Subistiruir links por valores
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':adms_daman_user_id', $id, PDO::PARAM_INT);

        // Executar a Query
        $stmt->execute();

        // Ler os registross
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retornar apenas os valores de 'adms_daman_access_level_id' como array simples
        return $result ? array_column($result, 'adms_damanaccess_level_id') : false;
    }

    // Obter níveis de acesso de menor prioridade
    public function getLowerPriorityAccessLevels(): array|bool
    {
        // Etapa 1: Recuperar o menor numero relacionado a ordem.(quanto menor o número maior a ordem de prioridade)
        $sql = 'SELECT MIN(al.order_levels) AS min_order_levels
                FROM adms_daman_users_access_levels ual
                INNER JOIN adms_daman_access_levels al ON al.id = ual.adms_daman_access_level_id
                WHERE ual.adms_daman_user_id = :userId';

        // Preparar a Query -> Subistiruir links por valores -> Executar
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        // O menor valor de 'order_leveals'
        $minOrderLavels = $stmt->fetchColumn();

        // Se não encontrar níveis de acessp, retorna falso
        if (!$minOrderLavels) {
            return false;
        }

        // Etapa 2: Recuperar todos os níveis de acesso com order_levels' Maior que o menor valor do usuário
        $sql = 'SELECT al.id, al.name
                FROM adms_daman_access_levels al
                WHERE al.order_levels > :minOrderLevels
                ORDER BY al.name ASC';

        // Preparar a Query -> Subistiruir links por valores -> Executar
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':minOrderLevels', $minOrderLavels, PDO::PARAM_INT);
        $stmt->execute();

        // Retornar os níveis de acesso com menor prioridade/importância (números maiores que do próprio usuário)
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
