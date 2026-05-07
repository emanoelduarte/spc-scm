<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class SuppliersRepository extends DbConnection

{
    public function getAllSuppliers(int $page = 1, int $limitResult = 10): array|bool
    {
        $offset = max(0, ($page - 1) * $limitResult);

        $sql = 'SELECT id, legal_name, cnpj, contact_name, phone
        FROM adms_daman_suppliers
        ORDER BY legal_name ASC
        LIMIT :limit OFFSET :offset';

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir o link da QUERY pelo valor
        $stmt->bindValue(':limit', $limitResult, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Executar a Query
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recuperar a quantidade de usuários para paginação
     * @return int|bool Quantidade de usuários encontrados no banco de dados
     */

    public function getAmountSuppliers(): int|bool
    {
        // Criar Query para recuperar todos os registros no banco de dados
        $sql = 'SELECT COUNT(id) AS amount_records
        FROM adms_daman_suppliers';

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a query
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
            $sql = 'SELECT ads.id, ads.legal_name, ads.trade_name, ads.cnpj, ads.contact_name, ads.phone, ads.adms_daman_suppliers_types_id, ads.supplier_status, ads.created_at, ads.updated_at,
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

    public function getAllSuppliersSelectActive(): array|bool
    {

        $sql = 'SELECT id, legal_name, cnpj, contact_name, phone
        FROM adms_daman_suppliers
        WHERE supplier_status = :supplier_status
        ORDER BY legal_name ASC';

        // Preparar a query
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir link pelo valores
        $stmt->bindValue(':supplier_status', 1, PDO::PARAM_INT);

        // Executar a Query
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
