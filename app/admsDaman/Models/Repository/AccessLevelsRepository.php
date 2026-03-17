<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

/**
 * Classe responsável por fazer as consultas no banco de dados e retornar para a controller que solicitar.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Models\Repository
 */
class AccessLevelsRepository extends DbConnection
{
    //
    public function getAllAccessLevels(int $page = 1, int $limitResult = 10)
    {
        // Calcular o registro inicial de cada página exemplo:
        // 2(caso pagina 2) - 1 = 1 * $limite por página = 10
        $offset = max(0, ($page - 1) * $limitResult);

        // Query para recuperar os registros do banco de dados
        $sql = 'SELECT id, name, order_levels 
        FROM adms_daman_access_levels
        ORDER BY id ASC
        LIMIT :limit OFFSET :offset';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir os links pelos valores
        $stmt->bindValue(':limit', $limitResult, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Executar a Query
        $stmt->execute();

        // Ler os Registos e retornar os dados com fetchAll
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar a quantidade total de níveis de acesso para paginação.
     * Este método retorna a quantidade total de níveis de acesso na tabela `adms_daman_access_levels`, útil para a paginação.
     * 
     * @return int Quantidade total de níveis de acesso encontrados no banco de dados.
     */
    public function getAmountAccessLevels(): int
    {
        // QUERY para recuperar a quantidade de registros
        $sql = 'SELECT COUNT(id) as amount_records
                FROM adms_daman_access_levels';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

    return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    public function getAccessLevel(int|string $id): array|bool
    {
        // Criar query para recuperar o registro do banco de dados
        $sql = 'SELECT id, name, order_levels, created_at, updated_at
                FROM adms_daman_access_levels
                WHERE id = :id';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Subistituir o link pelos valores
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        // Executar a query
        $stmt->execute();

        // Ler e retornar o registro
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cadastrar um novo nível de acesso.
     *
     * Este método insere um novo nível de acesso na tabela `adms_daman_access_levels`. Em caso de erro, um log é gerado.
     *
     * @param array $data Dados do nível de acesso a ser cadastrado, incluindo `name`.
     * @return bool|int `true` se o nível de acesso foi criado com sucesso ou `false` em caso de erro.
     */
    public function createAccessLevel(array $data): bool|int
    {
        // Usar try e catch para gerenciar exceção/erro
        try { // Permanece no try se não houver nenhum erro

            // QUERY para recuperar o nível de acesso com permissão menor
            $sql = 'SELECT id, order_levels	
                    FROM adms_daman_access_levels
                    ORDER BY order_levels DESC
                    LIMIT 1';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Executar a QUERY
            $stmt->execute();

            // Ler o registro e retornar
            $lastAccessLevel = $stmt->fetch(PDO::FETCH_ASSOC);

            // Acessar o IF quando não encontrar nível de acesso
            if (!$lastAccessLevel) {
                // Gerar log de erro
                GenerateLog::generateLog("error", "Não existe nenhum nível de acesso para usar como última ordem.", ['name' => $data['name']]);
            }

            // QUERY cadastrar usuários
            $sql = 'INSERT INTO adms_daman_access_levels (name, order_levels, created_at) VALUES (:name, :order_levels, :created_at)';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':order_levels', $lastAccessLevel['order_levels'] + 1, PDO::PARAM_INT);
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

            // Executar a QUERY
            $stmt->execute();

            // Retornar o ID do nível de acesso recém-cadastrado
            return $this->getConnection()->lastInsertId();

        }catch (Exception $e) { // Acessa o catch quando houver erro no try
            GenerateLog::generateLog("error", "Nível de acesso não cadastrado.", ['name' => $data['name'], 'error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Atualizar os dados de um nível de acesso existente.
     *
     * Este método atualiza as informações de um nível de acesso existente. Se a senha for fornecida, ela também será atualizada.
     * Em caso de erro, um log é gerado.
     *
     * @param array $data Dados atualizados do nível de acesso, incluindo `id`, `name`, `order_levels`
     * @return bool `true` se a atualização foi bem-sucedida ou `false` em caso de erro.
     */
    public function updateAccessLevel(array $data): bool
    {
        // Usar try e catch para gerenciar exceção/erro
        try { // Permanece no try se não houver erro

            // QUERY para atualizar usuário
            $sql = 'UPDATE adms_daman_access_levels SET name = :name, updated_at = :updated_at
            WHERE id = :id';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':order_levels', $data['order_levels'], PDO::PARAM_INT);
            $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            $stmt->execute();

            // Receber a quantidade de linhas afetadas
            $affectedRows = $stmt->rowCount();

            // Verificar o número de linhas afetadas
            if ($affectedRows > 0) {
                return true;
            } else {

                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Nível de acesso não editado.", ['id' => $data['id']]);

                return false;
            }
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Nível de acesso não editado.", ['id' => $data['id'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Deletar um nível de acesso pelo ID.
     *
     * Este método remove um nível de acesso específico da tabela `adms_daman_access_levels`. Em caso de erro, um log é gerado.
     *
     * @param int $id ID do nível de acesso a ser deletado.
     * @return bool `true` se o nível de acesso foi deletado com sucesso ou `false` em caso de erro.
     */
    public function deleteAccessLevel(int $id): bool
    {
        try { // Usar try catch para gerenciar erro/exceção

            // Query para deletar o usuário
            $sql = 'DELETE FROM adms_daman_access_levels WHERE id = :id LIMIT 1';

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
                GenerateLog::generateLog("error", "Nível de acesso não apagado.", ['id' => $id]);
                return false;
            }
        } catch (Exception $e) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Nível de acesso não apagado.", ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function getAllAccessLevelsSelect()
    {
        // Query para recuperar os registros do banco de dados
        $sql = 'SELECT id, name, order_levels 
        FROM adms_daman_access_levels
        ORDER BY name ASC';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a Query
        $stmt->execute();

        // Ler os Registos e retornar os dados com fetchAll
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>