<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class AccessLevelsPagesRepository extends DbConnection
{
    public function getPagesAccessLevelsArray(int $accessLevel, bool $permission = false): array|bool
    {
        // Query para recuperar os registros do banco de dados
        $sql = 'SELECT adms_daman_page_id 
        FROM adms_daman_access_levels_pages
        WHERE adms_daman_access_level_id = :adms_daman_access_level_id';

        // Acessa o IF quando deve retornar apenas páginas que tiverem permissão 1
        if($permission) {
            $sql .= " AND permission = 1";
        }

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

    public function createPagesAccessLevel(array $data): bool
    {
        try { // Permanece no try se der tudo certo
            // Marcar o ponto inicial de uma transação SQL
            $this->getConnection()->beginTransaction();

            // Array para armazenar o ID do nível de acesso para salvar no log
            $accessLevelArrayId = [];

            // Percorrer array com niveis de acesso e páginas
            foreach ($data as $accessLevelId => $accessLevelPages) {

                // Array para acumular os valores
                $values = [];
                $placeholders = [];

                // Percorrer o array de páginas que o nível de acesso não tem permissão de acessar
                foreach ($accessLevelPages as $pageId) {
                    $values[] = $accessLevelId == 1 ? 1 : 0;
                    $values[] = $accessLevelId;
                    $values[] = $pageId;
                    $values[] = date("Y-m-d H:i:s");
                    $placeholders[] = "(?, ?, ?, ?)";
                }

                // Criar a QUERY somente se o nível de acesso não tem a página cadastrada
                if ($accessLevelPages ?? false) {

                    // Query para inserir os dados
                    $sql = "INSERT INTO adms_daman_access_levels_pages (permission, adms_daman_access_level_id, adms_daman_page_id, created_at) VALUE " . implode(", ", $placeholders);

                    // Preparar a Query
                    $stmt = $this->getConnection()->prepare($sql);

                    // Executar a Query
                    $stmt->execute($values);

                    // Salvar o ID do nível de acesso para o log
                    $accessLevelArrayId[] = $accessLevelId;
                }
            }

            // Gerar log de sucesso
            GenerateLog::generateLog("info", "Páginas cadastradas para o nível de acesso!", ['adms_daman_access_level_id' => $accessLevelArrayId]);

            // Acessa somente este if se cadastrou alguma página corretamente para o nível de acesso
            if ($accessLevelArrayId ?? false) {
                // Operação SQL concluída com êxito
                $this->getConnection()->commit();
            }

            return true;
        } catch (Exception $e) {

            // Operação SQL não concluida com êxito
            $this->getConnection()->rollBack();

            // Gerar log de erro
            GenerateLog::generateLog("error", "Páginas não cadastradas para o nível de acesso!", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Atualiza as permissões de páginas associadas a um nível de acesso.
     *
     * Este método realiza a atualização das permissões de acesso às páginas para um nível de acesso específico,
     * removendo ou adicionando permissões conforme as alterações recebidas no formulário.
     *
     * @param array $data Dados contendo as permissões de páginas a serem atualizadas.
     * @return bool Retorna `true` se a operação foi bem-sucedida, ou `false` em caso de erro.
     */
    public function updateAccessLevelPages(array $data): bool
    {

        if ($data['adms_daman_access_level_id'] == 1) {
            // Gerar log de erro
            GenerateLog::generateLog("error", "Permissão para o Super Administrador não pode ser editada.", ['id' => $data['adms_daman_access_level_id']]);

            $_SESSION['error'] = "Permissão para o Super Administrador não pode ser editada!";

            return false;
        }

        try {

            // Marca o ponto inicial de uma transação SQL
            $this->getConnection()->beginTransaction();

            // Criar o elemento userAccessLevels no array quando não vem nível de acesso do formulário
            $data['accessLevelPage'] = $data['accessLevelPage'] ?? [];

            // Recuperar todas as páginas cadastradas para o nível de acesso
            $resultAccessLevelsPages = $this->getPagesAccessLevelsArray((int) $data['adms_daman_access_level_id']);

            // Recuperar as páginas que nível de acesso tem permissão de acessar
            $resultAccessLevelsPagesPermissions = $this->getPagesAccessLevelsArray((int) $data['adms_daman_access_level_id'], true);
            $resultAccessLevelsPagesPermissions = $resultAccessLevelsPagesPermissions ? $resultAccessLevelsPagesPermissions : [];

            // Percorrer o array com as páginas que devem ser liberadas para o usuário
            foreach ($data['accessLevelPage'] as $accessLevelPage) {

                // Verificar se a página não está cadastrada para o nível de acesso
                if (!in_array($accessLevelPage, $resultAccessLevelsPages)) {

                    // QUERY para cadastrar página para o nível de acesso
                    $sql = 'INSERT INTO adms_daman_access_levels_pages (permission, adms_daman_access_level_id, adms_daman_page_id, created_at) VALUES (:permission, :adms_daman_access_level_id, :adms_daman_page_id, :created_at)';

                    // Preparar a QUERY
                    $stmt = $this->getConnection()->prepare($sql);

                    // Substituir os parâmetros da QUERY pelos valores
                    $stmt->bindValue(':permission', 1, PDO::PARAM_INT);
                    $stmt->bindValue(':adms_daman_access_level_id', $data['adms_daman_access_level_id'], PDO::PARAM_INT);
                    $stmt->bindValue(':adms_daman_page_id', $accessLevelPage, PDO::PARAM_INT);
                    $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

                    // Executar a QUERY
                    $stmt->execute();
                } elseif (!in_array($accessLevelPage, $resultAccessLevelsPagesPermissions)) { // Verificar se a página está cadastrada no BD, mas não está liberada o nível de acesso

                    // QUERY para atualizar página para o nível de acesso
                    $sql = 'UPDATE adms_daman_access_levels_pages 
                            SET permission = :permission, updated_at = :updated_at
                            WHERE adms_daman_access_level_id = :adms_daman_access_level_id 
                            AND adms_daman_page_id = :adms_daman_page_id';

                    // Preparar a QUERY
                    $stmt = $this->getConnection()->prepare($sql);

                    // Substituir os parâmetros da QUERY pelos valores 
                    $stmt->bindValue(':permission', 1, PDO::PARAM_INT);
                    $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
                    $stmt->bindValue(':adms_daman_access_level_id', $data['adms_daman_access_level_id'], PDO::PARAM_INT);
                    $stmt->bindValue(':adms_daman_page_id', $accessLevelPage, PDO::PARAM_INT);

                    // Executar a QUERY
                    $stmt->execute();

                    // Usar array_diff para remover o valor
                    $resultAccessLevelsPagesPermissions = array_diff($resultAccessLevelsPagesPermissions, [$accessLevelPage]);
                } elseif (in_array($accessLevelPage, $resultAccessLevelsPagesPermissions)) { // Verificar se no banco de dados o registro indica que o usuário tem permissão de acessar a página, no formulário mantém a permissão de acesso a página

                    // Usar array_diff para remover o valor
                    $resultAccessLevelsPagesPermissions = array_diff($resultAccessLevelsPagesPermissions, [$accessLevelPage]);
                }
            }

            // Remover a permissão de acesso a páginas que foi retirado o acesso no formulário
            if ($resultAccessLevelsPagesPermissions) {

                // Percorrer o array com as permissões que devem ser removidas
                foreach ($resultAccessLevelsPagesPermissions as $accessLevelsPagesPermissions) {

                    // QUERY para apagar o nível de acesso do usuário
                    $sql = 'DELETE FROM adms_daman_access_levels_pages 
                        WHERE adms_daman_access_level_id = :adms_daman_access_level_id 
                        AND adms_daman_page_id = :adms_daman_page_id 
                        LIMIT 1';

                    // Preparar a QUERY
                    $stmt = $this->getConnection()->prepare($sql);

                    // Substituir os parâmetros da QUERY pelos valores 
                    $stmt->bindValue(':adms_daman_access_level_id', $data['adms_daman_access_level_id'], PDO::PARAM_INT);
                    $stmt->bindValue(':adms_daman_page_id', $accessLevelsPagesPermissions, PDO::PARAM_INT);

                    // Executar a QUERY
                    $stmt->execute();

                    GenerateLog::generateLog("info", "Removido a permissão de acesso à página ao nível de acesso.", ['id' => $data['adms_daman_access_level_id'], 'adms_daman_access_level_id' => $accessLevelsPagesPermissions]);
                }
            }

            // Operação SQL concluída com êxito
            $this->getConnection()->commit();

            return true;
        } catch (Exception $e) {

            // Operação SQL não é concluída com êxito
            $this->getConnection()->rollBack();

            // Gerar log de erro
            GenerateLog::generateLog("error", "Permissão de acesso à página pelo nível de acesso não editada.", ['id' => $data['adms_daman_access_level_id'], 'error' => $e->getMessage()]);

            return false;
        }
    }
}
?>