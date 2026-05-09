<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

/**
 * Repositório de Níveis de Acesso dos Usuários
 *
 * Esta classe é responsável por realizar operações de CRUD relacionadas aos níveis de acesso dos usuários no sistema.
 * Ela realiza consultas, inserções, atualizações e exclusões nos registros de níveis de acesso de cada usuário.
 * Além disso, trata a lógica de associação e remoção de níveis de acesso ao usuário, com a devida geração de logs 
 * para acompanhamento de alterações.
 * 
 * @package App\adms\Models\Repository
 * @author <emanoel.c.duarte@hotmail.com>
 **/
class UsersAccessLevelsRepository extends DbConnection
{
    /**
     * Recupera os níveis de acesso de um usuário específico.
     *
     * Executa uma consulta ao banco de dados para obter os níveis de acesso associados ao ID do usuário.
     *
     * @param int $id ID do usuário
     * @return array|bool Retorna um array com os níveis de acesso ou false caso não encontre
     */
    public function getUsersAccessLevels(int $id): array|bool
    {
        // Criar a Query para recuperar os dados
        // nome_da_tabela AS (recebe o apelido) lev ON (onde na tabela identificada por na coluna) [lev.id]=chave primária que deve ser igual a chave estrangeira da tabela que guarda o id do level dos usuários
        $sql = 'SELECT lev.id, lev.name
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

    /**
     * Retorna os IDs dos níveis de acesso de um usuário em formato de array.
     *
     * Obtém os IDs dos níveis de acesso do usuário a partir de seu ID.
     *
     * @param int $id ID do usuário
     * @return array|bool Retorna um array simples com os IDs ou false caso não encontre
     */
    public function getUserAccessLevelsArray(int $id): array|bool
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
        return $result ? array_column($result, 'adms_daman_access_level_id') : false;
    }

    /**
     * Obtém todos os níveis de acesso que possuem prioridade inferior ao do usuário atual.
     *
     * Consulta os níveis de acesso com um valor de 'order_levels' superior ao nível de menor prioridade do usuário atual.
     *
     * @return array|bool Retorna os níveis de acesso ou false se nenhum for encontrado
     */
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

    /**
     * Atualiza os níveis de acesso do usuário com base nos dados fornecidos.
     *
     * Realiza a lógica de adicionar ou remover níveis de acesso de um usuário conforme os níveis fornecidos.
     * Também gera logs para acompanhamento das operações.
     *
     * @param array $data Dados contendo os níveis de acesso e o ID do usuário
     * @return bool Retorna true se a atualização for bem-sucedida, false em caso de erro
     */
    public function updateUserAccessLevel(array $data): array|bool
    {
        // Criar o Elemento userAccessLevels no array quando não vem nível de acesso do formulário
        $userAccessLevelsArray = $data['userAccessLevels'] ?? [];

        try { // Permanece no try se não houver erro

            // Recuperar os níveis de aceso do usuário em formato de array
            $userAccessLevelsArray = $this->getUserAccessLevelsArray($data['adms_daman_user_id']);

            // Quando o usuário não tiver nível de acesso cadastrado ele irá criar um array vazio com a expressão ternária
            $userAccessLevelsArray = $userAccessLevelsArray ? $userAccessLevelsArray : [];

            // Percorrer o array com os níveis de acesso e liberar acesso
            foreach ($data['userAccessLevels'] ?? [] as $userAccessLevel) {

                // Se o usuário já tiver o nível de acesso liberado, remove do array de liberação
                if (in_array($userAccessLevel, $userAccessLevelsArray)) {
                    $userAccessLevelsArray = array_diff($userAccessLevelsArray, [$userAccessLevel]);
                } else {

                    // Cadastrar o nível de acesso do usuário
                    // Query para cadastrar o novo nível de acesso do usuário
                    $sql = 'INSERT INTO adms_daman_users_access_levels (adms_daman_user_id, adms_daman_access_level_id, created_at)
                    VALUES (:adms_daman_user_id, :adms_daman_access_level_id, :created_at)';

                    // Preparar a Query
                    $stmt = $this->getConnection()->prepare($sql);

                    // Subistituir Links pelos valores
                    $stmt->bindValue(':adms_daman_user_id', $data['adms_daman_user_id'], PDO::PARAM_INT);
                    $stmt->bindValue(':adms_daman_access_level_id', $userAccessLevel, PDO::PARAM_INT);
                    $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

                    // Executar a Query
                    $stmt->execute();

                    // Chamar o método para salvar o log
                    GenerateLog::generateLog("info", "Cadastrado nível de acesso do usuário com sucesso.", ['id' => $data['adms_daman_user_id'], 'adms_daman_access_level_id' => $userAccessLevel]);
                }
            }

            // Percorrer o array com níveis de acessso e bloquear acesso
            foreach ($userAccessLevelsArray as $userAccessLevel) {
                $sql = 'DELETE FROM adms_daman_users_access_levels
                WHERE adms_daman_user_id = :adms_daman_user_id
                AND adms_daman_access_level_id = :adms_daman_access_level_id
                LIMIT 1';

                // Preparar a Query
                $stmt = $this->getConnection()->prepare($sql);

                // Subistituir Links pelos valores
                $stmt->bindValue(':adms_daman_user_id', $data['adms_daman_user_id'], PDO::PARAM_INT);
                $stmt->bindValue(':adms_daman_access_level_id', $userAccessLevel, PDO::PARAM_INT);

                // Executar a Query
                $stmt->execute();

                // Chamar o método para salvar o log
                GenerateLog::generateLog("info", "Removido nível de acesso do usuário com sucesso.", ['id' => $data['adms_daman_user_id'], 'adms_daman_access_level_id' => $userAccessLevel]);
            }

            return true;
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Nível de acesso do usuário não editado.", ['id' => $data['adms_daman_user_id'], 'error' => $e->getMessage()]);

            return false;
        }
        return true;
    }
}
