<?php
    
namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class PackagesRepository extends DbConnection
{
    /**
     * Recuperar todos os pacotes com paginação.
     *
     * Este método retorna uma lista de pacotes da tabela `adms_daman_packages_pages`, com suporte à paginação.
     *
     * @param int $page Número da página para recuperação de pacotes (começa do 1).
     * @param int $limitResult Número máximo de resultados por página.
     * @return array Lista de pacotes recuperados do banco de dados.
     */
    public function getAllPackages(int $page = 1, int $limitResult = 10)
    {

        // Calcular o registro inicial de cada página exemplo:
        // 2(caso pagina 2) - 1 = 1 * $limite por página = 10
        $offset = max(0, ($page - 1) * $limitResult);

        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name 
                FROM adms_daman_packages_pages
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
     * Recuperar a quantidade total de packages para paginação.
     *
     * Este método retorna a quantidade total de packages na tabela `adms_daman_packages_pages`, útil para a paginação.
     *
     * @return int Quantidade total de packages encontrados no banco de dados.
     */
    public function getAmountPackages(): int|bool
    {
        // Criar Query para recuperar todos os registros no banco de dados
        $sql = 'SELECT COUNT(id) AS amount_records
        FROM adms_daman_packages_pages';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a query
        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    /**
     * Recuperar um pacote específico pelo ID.
     *
     * Este método retorna os detalhes de um pacote específico identificado pelo ID.
     *
     * @param int $id ID do pacote a ser recuperado.
     * @return array|bool Detalhes do pacote recuperado ou `false` se não encontrado.
     */
    public function getPackage(int $id): array|bool
    {

        // QUERY para recuperar o registro do banco de dados
        $sql = 'SELECT id, name, obs, created_at, updated_at
                FROM adms_daman_packages_pages
                WHERE id = :id
                ORDER BY id DESC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir o link da QUERY pelo valor
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        // Executar a QUERY
        $stmt->execute();

        // Ler o registro e retornar 
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createPackage(array $data): bool|int
    {
        // Usar try e catch para gerenciar exceção/erro
        try { // Permanece no try se não houver nenhum erro

            // QUERY cadastrar pacote
            $sql = 'INSERT INTO adms_daman_packages_pages (name, obs, created_at) VALUES (:name, :obs, :created_at)';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':obs', $data['obs'], PDO::PARAM_STR);
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

            // Executar a QUERY
            $stmt->execute();

            // Retornar o ID do pacote recém cadastrado
            return $this->getConnection()->lastInsertId();
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pacote não cadastrado.", ['email' => $data['name'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Atualizar os dados de um pacote existente.
     *
     * Este método atualiza as informações de um pacote existente.
     * Em caso de erro, um log é gerado.
     *
     * @param array $data Dados atualizados do pacote, incluindo `id`, `name` e `obs`.
     * @return bool `true` se a atualização foi bem-sucedida ou `false` em caso de erro.
     */
    public function updatePackage(array $data): bool
    {
        // Usar try e catch para gerenciar exceção/erro
        try {  // Permanece no try se não houver nenhum erro

            // QUERY para atualizar pacote
            $sql = 'UPDATE adms_daman_packages_pages SET name = :name, obs = :obs, updated_at = :updated_at';

            // Condição para indicar qual registro editar
            $sql .= ' WHERE id = :id';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':obs', $data['obs'], PDO::PARAM_STR);
            $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            // Executar a QUERY
            $stmt->execute();

            // Receber a quantidade de linhas afetadas
            $affectedRows = $stmt->rowCount();

            // Verificar o número de linhas afetadas
            if ($affectedRows > 0) {
                return true;
            } else {

                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Pacote não editado.", ['id' => $data['id']]);

                return false;
            }
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pacote não editado.", ['id' => $data['id'], 'error' => $e->getMessage()]);

            return false;
        }
    }

     /**
     * Deletar um pacote pelo ID.
     *
     * Este método remove um pacote específico da tabela `adms_daman_packages`. Em caso de erro, um log é gerado.
     *
     * @param int $id ID do pacote a ser deletado.
     * @return bool `true` se o pacote foi deletado com sucesso ou `false` em caso de erro.
     */
    public function deletePackage(int $id): bool
    {
        // Usar o try e catch para gerenciar exceção/erro
        try {

            // Query para deletar o pacote
            $sql = 'DELETE FROM adms_daman_packages_pages WHERE id = :id LIMIT 1';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substiruir os links pelo valor
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();

            // Verificar o número de linhas afetadas
            $affectedRows = $stmt->rowCount();

            if ($affectedRows > 0) {
                return true;
            } else {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Pacote não apagado.", ['id' => $id]);
                return false;
            }
        } catch (Exception $e) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pacote não apagado.", ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Recuperar todos os pacotes, para preencher o select de forma dinâmica na hora de cadastrar um novo pacote.
     *
     * Este método retorna uma lista de pacotes da tabela `adms_packages_pages`.
     *
     * @return array Lista de pacotes recuperados do banco de dados.
     */
    public function getAllPackagesSelect()
    {

        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name 
                FROM adms_daman_packages_pages
                ORDER BY id ASC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>