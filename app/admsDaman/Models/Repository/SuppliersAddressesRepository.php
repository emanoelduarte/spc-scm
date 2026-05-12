<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class SuppliersAddressesRepository extends DbConnection
{
    public function getAddressesSupplier(int $supplierId): array
    {
        // Selecionar o endereço do fornecedor pela tabela relacional
        $sql = "SELECT ada.zip_code, ada.street, ada.number, ada.complement, ada.neighborhood, ada.city, ada.state
        FROM adms_daman_supplier_addresses AS adsa
        INNER JOIN adms_daman_addresses AS ada ON ada.id = adsa.adms_daman_address_id
        WHERE adsa.adms_daman_supplier_id = :adms_daman_supplier_id";

        // Preparar a Query
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir Links por valores
        $stmt->bindParam(':adms_daman_supplier_id', $supplierId, PDO::PARAM_INT);

        //Executar a Query
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createRelation(array $data): bool
    {
        try {

            $sql = 'INSERT INTO adms_daman_supplier_addresses (adms_daman_supplier_id, adms_daman_address_id, created_at ) 
                VALUES (:adms_daman_supplier_id, :adms_daman_address_id, :created_at)';

            // Prepar a query
            $stmt = $this->getConnection()->prepare($sql);

            // Substitui os links
            $stmt->bindValue(':adms_daman_supplier_id', $data['adms_daman_supplier_id'], PDO::PARAM_INT);
            $stmt->bindValue(':adms_daman_address_id', $data['adms_daman_address_id'], PDO::PARAM_INT);
            $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));

            return $stmt->execute();
        } catch (Exception $e) {

            GenerateLog::generateLog(
                "error",
                "Erro ao criar relação fornecedor/endereço",
                [
                    'supplier_id' => $data['adms_daman_supplier_id'] ?? null,
                    'address_id' => $data['adms_daman_address_id'] ?? null,
                    'error_sql' => $e->getMessage()
                ]
            );

            return false;
        }
    }
}