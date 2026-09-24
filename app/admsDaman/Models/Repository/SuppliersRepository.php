<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class SuppliersRepository extends DbConnection

{
    /*
     * ==========================================================
     * TIPOS DE FORNECEDOR
     * ==========================================================
     *
     * Centralizar os IDs evita espalhar números "mágicos"
     * pelos Controllers e pelas consultas.
     */
    public const TYPE_SALE = 1;
    public const TYPE_RENTAL = 2;
    public const TYPE_SERVICE = 3;
    public const TYPE_FINANCIAL_OBLIGATION = 4;

    public function getAllSuppliers(int $page = 1, int $limitResult = 10, ?array $filters = []): array|bool
    {
        $offset = max(0, ($page - 1) * $limitResult);

        $conditions = [];
        $params = [];

        if (!empty($filters['legal_name'])) {
            $conditions[] = "legal_name LIKE :legal_name";
            $params['legal_name'] = '%' . $filters['legal_name'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT id, legal_name, cnpj, contact_name, phone, supplier_status
            FROM adms_daman_suppliers
            {$where}
            ORDER BY legal_name ASC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->bindValue(':limit', $limitResult, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar a quantidade de usuários para paginação
     * @return int|bool Quantidade de usuários encontrados no banco de dados
     */

    public function getAmountSuppliers(?array $filters = []): int|bool
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['legal_name'])) {
            $conditions[] = "legal_name LIKE :legal_name";
            $params['legal_name'] = '%' . $filters['legal_name'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT COUNT(id) AS amount_records
            FROM adms_daman_suppliers
            {$where}";

        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();

        return ($stmt->fetch(PDO::FETCH_ASSOC)['amount_records'] ?? 0);
    }

    /**
     * Recuperar o fornecedor
     * 
     * @return array|bool Fornecedor recuperado do banco de dados
     */
    public function getSupplier(int $id): array|bool
    {
        try {
            $sql = 'SELECT ads.id, ads.legal_name, ads.trade_name, ads.cnpj, ads.contact_name, ads.phone, ads.email, ads.adms_daman_suppliers_types_id, ads.accepted_payments, ads.supplier_status, ads.created_at, ads.updated_at,
            adst.name AS supplier_type
            FROM adms_daman_suppliers AS ads
            INNER JOIN adms_daman_suppliers_types AS adst ON adst.id = ads.adms_daman_suppliers_types_id
            WHERE ads.id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substiruir os links pelos valores 
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            GenerateLog::generateLog("error", "Fornecedor não encontrado", ['id' => (int) $id]);
            die("Fornecedor não encontrado " . $err->getMessage());
        }
        return false;
    }

    /**
     * Cadastrar novo Fornecedor
     * @param array $data Dados do fornecedor
     * @return bool Sucesso ou falha
     */
    public function createSupplier(array $data): bool|int
    {
        try {
            // Criar a Query para cadastrar os dados
            $sql = 'INSERT INTO adms_daman_suppliers (legal_name, trade_name, cnpj, contact_name, phone, email, adms_daman_suppliers_types_id, supplier_status, created_at) 
            VALUES (:legal_name, :trade_name, :cnpj, :contact_name, :phone, :email, :adms_daman_suppliers_types_id, :supplier_status, :created_at)';

            if ($data['email'] == '') {
                $data['email'] = null;
            }

            // Preparar a query para inserir os dados no banco de dados
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links pelos valores passados no array
            $stmt->bindValue(':legal_name', strtoupper($data['legal_name']), PDO::PARAM_STR);
            $stmt->bindValue(':trade_name', strtoupper($data['trade_name']), PDO::PARAM_STR);
            $stmt->bindValue(':cnpj', $data['cnpj'], PDO::PARAM_STR);
            $stmt->bindValue(':contact_name', strtoupper($data['contact_name']), PDO::PARAM_STR);
            $stmt->bindValue(':phone', $data['phone'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $data['email']);
            $stmt->bindValue(':adms_daman_suppliers_types_id', $data['adms_daman_suppliers_types_id'], PDO::PARAM_INT);
            $stmt->bindValue(':supplier_status', 1, PDO::PARAM_INT);
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"));

            // Executar a querry para cadastrar no banco de dados
            $stmt->execute();

            // Retornar o ID do fornecedor recém cadastrado
            return $this->getConnection()->lastInsertId();
        } catch (Exception $e) {
            // Chamar método para salvar o log
            GenerateLog::generateLog("error", "Usuário tentou cadastrar usuário existente", ['email' => $data['email'], 'error_sql' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Editar os dados do Fornecedor
     * @param array $data Dados atualizados do Fornecedor
     * @return bool Sucesso ou Falha | true|false
     */
    public function updateSupplier(array $data): bool
    {
        // Usar try e catch para gerencia exceção/erro
        try { // Permanece no try se não houver nenhum erro

            // Query para atualizar o Fornecedor
            $sql = "UPDATE adms_daman_suppliers 
            SET legal_name = :legal_name, trade_name = :trade_name, cnpj = :cnpj, contact_name = :contact_name, phone = :phone, email = :email, adms_daman_suppliers_types_id = :adms_daman_suppliers_types_id, supplier_status = :supplier_status, updated_at = :updated_at";

            // Condição para indicar qual registo editar
            $sql .= ' WHERE id = :id';

            // Preparar a Query
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links pelos valores passados no array
            $stmt->bindValue(':legal_name', strtoupper($data['legal_name']), PDO::PARAM_STR);
            $stmt->bindValue(':trade_name', strtoupper($data['trade_name']), PDO::PARAM_STR);
            $stmt->bindValue(':cnpj', $data['cnpj'], PDO::PARAM_STR);
            $stmt->bindValue(':contact_name', strtoupper($data['contact_name']), PDO::PARAM_STR);
            $stmt->bindValue(':phone', $data['phone'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $data['email']);
            $stmt->bindValue(':adms_daman_suppliers_types_id', $data['adms_daman_suppliers_types_id'], PDO::PARAM_INT);
            $stmt->bindValue(':supplier_status', $data['supplier_status'], PDO::PARAM_INT);
            $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            // Executar a Query.
            //
            // IMPORTANTE:
            // rowCount() pode retornar 0 quando os dados gerais do
            // fornecedor não mudam. Isso é válido, por exemplo, quando
            // o usuário altera somente o endereço.
            $stmt->execute();

            return true;
        } catch (Exception $e) { // Acessa o catch quando houver erro no try
            // Chamar método para salvar o log
            GenerateLog::generateLog("error", "Fornecedor não editado.", ['id' => $data['id'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Deletar Fornecedor pelo ID
     * @param int $id ID do Fornecedor a ser deletado
     * @return bool Sucesso ou falha
     */
    public function deleteSupplier(int $id): bool
    {
        // Usar o try e catch para gerenciar exceção/erro
        try {

            // Query para deletar o fornecedor
            $sql = 'DELETE FROM adms_daman_suppliers WHERE id = :id LIMIT 1';

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
                GenerateLog::generateLog("error", "Fornecedor não apagado.", ['id' => $id]);
                return false;
            }
        } catch (Exception $e) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Fornecedor não apagado.", ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function getAllSuppliersSelect(): array|bool
    {

        $sql = 'SELECT id, legal_name, cnpj, contact_name, phone
        FROM adms_daman_suppliers
        ORDER BY legal_name ASC';

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a Query
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar fornecedores por tipo.
     *
     * Este método passa a ser a fonte comum para os selects de
     * fornecedores. O tipo é informado explicitamente pelo fluxo
     * chamador, evitando IDs fixos escondidos nas consultas.
     *
     * @param int  $supplierTypeId ID do tipo de fornecedor.
     * @param bool $onlyActive     Quando true, retorna somente ativos.
     *
     * @return array|bool
     */
    public function getAllSuppliersSelectByType(
        int $supplierTypeId,
        bool $onlyActive = true
    ): array|bool {

        $conditions = [
            'adms_daman_suppliers_types_id = :supplier_type_id',
        ];

        if ($onlyActive) {
            $conditions[] = 'supplier_status = :supplier_status';
        }

        $sql = '
            SELECT
                id,
                legal_name,
                trade_name,
                cnpj,
                contact_name,
                phone,
                supplier_status,
                adms_daman_suppliers_types_id
            FROM
                adms_daman_suppliers
            WHERE
                ' . implode(' AND ', $conditions) . '
            ORDER BY
                legal_name ASC
        ';

        $stmt =
            $this->getConnection()
            ->prepare(
                $sql
            );

        $stmt->bindValue(
            ':supplier_type_id',
            $supplierTypeId,
            PDO::PARAM_INT
        );

        if ($onlyActive) {

            $stmt->bindValue(
                ':supplier_status',
                1,
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    /**
     * Recuperar fornecedores ativos utilizados no fluxo de compra.
     *
     * Mantido com o mesmo nome para preservar compatibilidade com
     * os Controllers já existentes em produção.
     */
    public function getAllSuppliersSelectActive(): array|bool
    {
        return $this->getAllSuppliersSelectByType(
            self::TYPE_SALE,
            true
        );
    }


    public function getAllTypesSuppliersSelect(): array|bool
    {

        $sql = 'SELECT id, name
        FROM adms_daman_suppliers_types
        ORDER BY name ASC';

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a Query
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Recuperar fornecedores utilizados no fluxo de locação.
     *
     * IMPORTANTE:
     * O método legado não filtrava supplier_status.
     * Para não alterar silenciosamente o comportamento atual da
     * produção, esse detalhe foi preservado aqui.
     *
     * @return array|bool Fornecedor recuperado do banco de dados
     */
    public function getSupplierTypeSelect(): array|bool
    {
        return $this->getAllSuppliersSelectByType(
            self::TYPE_RENTAL,
            false
        );
    }
}
