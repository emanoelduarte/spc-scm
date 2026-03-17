<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class PagesRepository extends DbConnection
{
    /**
     * Recuperar todos os pages com paginação.
     *
     * Este método retorna uma lista de pages da tabela `adms_daman_pages`, com suporte à paginação.
     *
     * @param int $page Número da página para recuperação de pages (começa do 1).
     * @param int $limitResult Número máximo de resultados por página.
     * @return array Lista de pages recuperados do banco de dados.
     */
    public function getAllPages(int $page = 1, int $limitResult = 10)
    {

        // Calcular o registro inicial de cada página exemplo:
        // 2(caso pagina 2) - 1 = 1 * $limite por página = 10
        $offset = max(0, ($page - 1) * $limitResult);

        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name, page_status, public_page 
                FROM adms_daman_pages
                ORDER BY id DESC
                LIMIT :limit OFFSET :offset';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir o link da QUERY pelo valor
        $stmt->bindValue(':limit', $limitResult, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar a quantidade total de pages para paginação.
     *
     * Este método retorna a quantidade total de pages na tabela `adms_daman_pages`, útil para a paginação.
     *
     * @return int Quantidade total de pages encontrados no banco de dados.
     */
    public function getAmountPages(): int|bool
    {
        // Criar Query para recuperar todos os registros no banco de dados
        $sql = 'SELECT COUNT(id) AS amount_records
        FROM adms_daman_pages';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a query
        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    /**
     * Recuperar uma página específica pelo ID.
     *
     * Este método retorna os detalhes de uma página específica identificada pelo ID.
     *
     * @param int $id ID da página a ser recuperada.
     * @return array|bool Detalhes da página recuperada ou `false` se não encontrada.
     */
    public function getPage(int $id): array|bool
    {
        // QUERY para recuperar o registro do banco de dados
        $sql = 'SELECT ap.id, ap.name, ap.controller, ap.controller_url, ap.directory, ap.obs, ap.page_status, ap.public_page, ap.adms_daman_packages_page_id, ap.adms_daman_groups_page_id, ap.created_at, ap.updated_at,
                app.name app_name,
                agp.name agp_name
                FROM adms_daman_pages AS ap
                INNER JOIN adms_daman_packages_pages AS app ON app.id=ap.adms_daman_packages_page_id
                INNER JOIN adms_daman_groups_pages AS agp ON agp.id=ap.adms_daman_groups_page_id
                WHERE ap.id = :id
                ORDER BY ap.id DESC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        // Executar a QUERY
        $stmt->execute();

        // Ler o registro e retornar
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cadastrar uma nova página.
     *
     * Este método insere uma nova página na tabela `adms_daman_pages`. Em caso de erro, um log é gerado.
     *
     * @param array $data Dados da página a ser cadastrada, incluindo `name`, `controller`, `controller_url`, `directory`, `obs`, `page_status`, `public_page`, `adms_daman_packages_page_id`, e `adms_daman_groups_page_id`.
     * @return bool|int `true` se a página foi criada com sucesso ou `false` em caso de erro.
     */
    public function createPage(array $data): bool|int
    {
        try {

            // QUERY para cadastrar página
            $sql = 'INSERT INTO adms_daman_pages (name, controller, controller_url, directory, obs, page_status, public_page, adms_daman_packages_page_id, adms_daman_groups_page_id, created_at) VALUES (:name, :controller, :controller_url, :directory, :obs, :page_status, :public_page, :adms_daman_packages_page_id, :adms_daman_groups_page_id, :created_at)';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os parâmetros da QUERY pelos valores
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':controller', $data['controller'], PDO::PARAM_STR);
            $stmt->bindValue(':controller_url', $data['controller_url'], PDO::PARAM_STR);
            $stmt->bindValue(':directory', $data['directory'], PDO::PARAM_STR);
            $stmt->bindValue(':obs', $data['obs'], PDO::PARAM_STR);
            $stmt->bindValue(':page_status', $data['page_status'], PDO::PARAM_BOOL);
            $stmt->bindValue(':public_page', $data['public_page'], PDO::PARAM_BOOL);
            $stmt->bindValue(':adms_daman_packages_page_id', $data['adms_daman_packages_page_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_groups_page_id', $data['adms_daman_groups_page_id'], PDO::PARAM_INT);
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

            // Executar a QUERY
            $stmt->execute();

            // Retornar o ID da página recém-cadastrada
            return $this->getConnection()->lastInsertId();
        } catch (Exception $e) {
            // Gerar log de erro
            GenerateLog::generateLog("error", "Página não cadastrada.", ['name' => $data['name'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Atualizar os dados de uma página existente.
     *
     * Este método atualiza as informações de uma página existente. Em caso de erro, um log é gerado.
     *
     * @param array $data Dados atualizados da página, incluindo `id`, `name`, `controller`, `controller_url`, `directory`, `obs`, `page_status`, `public_page`, `adms_daman_packages_page_id`, e `adms_daman_groups_page_id`.
     * @return bool `true` se a atualização foi bem-sucedida ou `false` em caso de erro.
     */
    public function updatePage(array $data): bool
    {
        try {
            // QUERY para atualizar página
            $sql = 'UPDATE adms_daman_pages SET 
                    name = :name, 
                    controller = :controller, 
                    controller_url = :controller_url, 
                    directory = :directory, 
                    obs = :obs,
                    page_status = :page_status, 
                    public_page = :public_page, 
                    adms_daman_packages_page_id = :adms_daman_packages_page_id, 
                    adms_daman_groups_page_id = :adms_daman_groups_page_id,  
                    updated_at = :updated_at';

            // Condição para indicar qual registro editar
            $sql .= ' WHERE id = :id';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os parâmetros da QUERY pelos valores 
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':controller', $data['controller'], PDO::PARAM_STR);
            $stmt->bindValue(':controller_url', $data['controller_url'], PDO::PARAM_STR);
            $stmt->bindValue(':directory', $data['directory'], PDO::PARAM_STR);
            $stmt->bindValue(':obs', $data['obs'], PDO::PARAM_STR);
            $stmt->bindValue(':page_status', $data['page_status'], PDO::PARAM_BOOL);
            $stmt->bindValue(':public_page', $data['public_page'], PDO::PARAM_BOOL);
            $stmt->bindValue(':adms_daman_packages_page_id', $data['adms_daman_packages_page_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_groups_page_id', $data['adms_daman_groups_page_id'], PDO::PARAM_INT);
            $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            // Executar a QUERY
            return $stmt->execute();
        } catch (Exception $e) {
            // Gerar log de erro
            GenerateLog::generateLog("error", "Página não editada.", ['id' => $data['id'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Deletar uma página pelo ID.
     *
     * Este método remove uma página específica da tabela `adms_daman_pages`. Em caso de erro, um log é gerado.
     *
     * @param int $id ID da página a ser deletada.
     * @return bool `true` se a página foi deletada com sucesso ou `false` em caso de erro.
     */
    public function deletePage(int $id): bool
    {
        try {
            // QUERY para deletar página
            $sql = 'DELETE FROM adms_daman_pages WHERE id = :id LIMIT 1';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // Executar a QUERY
            $stmt->execute();

            // Verificar o número de linhas afetadas
            $affectedRows = $stmt->rowCount();

            if ($affectedRows > 0) {
                return true;
            } else {
                // Gerar log de erro
                GenerateLog::generateLog("error", "Página não apagada.", ['id' => $id]);

                return false;
            }
        } catch (Exception $e) {
            // Gerar log de erro
            GenerateLog::generateLog("error", "Página não apagada.", ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function getPagesArray(): array|bool
    {

        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id 
                FROM adms_daman_pages';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros 
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retornar apenas os valores 'id' como array simples
        return $result ? array_column($result, 'id') : false;
    }
}
