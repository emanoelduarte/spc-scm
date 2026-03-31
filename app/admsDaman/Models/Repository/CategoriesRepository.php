<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class CategoriesRepository extends DbConnection
{
    /**
     * Recuperar todas as categorias com paginação.
     *
     * Este método retorna uma lista de categorias da tabela `adms_daman_categories`, com suporte à paginação.
     *
     * @param int $page Número da página para recuperação de categorias (começa do 1).
     * @param int $limitResult Número máximo de resultados por página.
     * @return array Lista de categorias recuperados do banco de dados.
     */
    public function getAllCategories(int $page = 1, int $limitResult = 10)
    {

        // Calcular o registro inicial de cada página exemplo:
        // 2(caso pagina 2) - 1 = 1 * $limite por página = 10
        $offset = max(0, ($page - 1) * $limitResult);

        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name 
                FROM adms_daman_categories
                ORDER BY id ASC
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
     * Recuperar a quantidade total de Categorias para paginação.
     *
     * Este método retorna a quantidade total de Categorias na tabela `adms_daman_Categories`, útil para a paginação.
     *
     * @return int Quantidade total de Categorias encontrados no banco de dados.
     */
    public function getAmountCategories(): int|bool
    {
        // Criar Query para recuperar todos os registros no banco de dados
        $sql = 'SELECT COUNT(id) AS amount_records
        FROM adms_daman_categories';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a query
        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    /**
     * Recuperar uma categoria específica pelo ID.
     *
     * Este método retorna os detalhes de uma categoria específica identificada pelo ID.
     *
     * @param int $id ID da categoria a ser recuperada.
     * @return array|bool Detalhes da categoria recuperada ou `false` se não encontrada.
     */
    public function getCategory(int $id): array|bool
    {
        // QUERY para recuperar o registro do banco de dados
        $sql = 'SELECT id, name, created_at, updated_at
                FROM adms_daman_categories
                WHERE id = :id
                ORDER BY id DESC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        // Executar a QUERY
        $stmt->execute();

        // Ler o registro e retornar
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cadastrar uma nova categoria.
     *
     * Este método insere uma nova categoria na tabela `adms_daman_categories`. Em caso de erro, um log é gerado.
     *
     * @param array $data Dados da categoria a ser cadastrada, incluindo `name`.
     * @return bool|int `true` se a categoria foi criada com sucesso ou `false` em caso de erro.
     */
    public function createCategory(array $data): bool|int
    {
        try {

            // QUERY para cadastrar categoria
            $sql = 'INSERT INTO adms_daman_categories (name, created_at) VALUES (:name, :created_at)';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os parâmetros da QUERY pelos valores
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

            // Executar a QUERY
            $stmt->execute();

            // Retornar o ID da categoria recém-cadastrada
            return $this->getConnection()->lastInsertId();
        } catch (Exception $e) {
            // Gerar log de erro
            GenerateLog::generateLog("error", "ategoria não cadastrada.", ['name' => $data['name'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Atualizar os dados de uma categoria existente.
     *
     * Este método atualiza as informações de uma categoria existente. Em caso de erro, um log é gerado.
     *
     * @param array $data Dados atualizados da categoria, incluindo `id`, `name`.
     * @return bool `true` se a atualização foi bem-sucedida ou `false` em caso de erro.
     */
    public function updateCategory(array $data): bool
    {
        try {
            // QUERY para atualizar categoria
            $sql = 'UPDATE adms_daman_categories SET 
                    name = :name, updated_at = :updated_at';

            // Condição para indicar qual registro editar
            $sql .= ' WHERE id = :id';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os parâmetros da QUERY pelos valores 
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            // Executar a QUERY
            return $stmt->execute();
        } catch (Exception $e) {
            // Gerar log de erro
            GenerateLog::generateLog("error", "Categoria não editada.", ['id' => $data['id'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Deletar uma categoria pelo ID.
     *
     * Este método remove uma categoria específica da tabela `adms_daman_categories`. Em caso de erro, um log é gerado.
     *
     * @param int $id ID da categoria a ser deletada.
     * @return bool `true` se a categoria foi deletada com sucesso ou `false` em caso de erro.
     */
    public function deleteCategory(int $id): bool
    {
        try {
            // QUERY para deletar categoria
            $sql = 'DELETE FROM adms_daman_categories WHERE id = :id LIMIT 1';

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
                GenerateLog::generateLog("error", "Categoria não apagada.", ['id' => $id]);

                return false;
            }
        } catch (Exception $e) {
            // Gerar log de erro
            GenerateLog::generateLog("error", "Categoria não apagada.", ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Recuperar uma Categoria específica
     * 
     * @return array|bool Categoria recuperada do banco de dados
     */
    public function getAllCategoriesSelect(): array|bool
    {
        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name 
                FROM adms_daman_categories
                ORDER BY id ASC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
