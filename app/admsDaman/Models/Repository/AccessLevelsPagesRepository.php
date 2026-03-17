<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
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
}
?>