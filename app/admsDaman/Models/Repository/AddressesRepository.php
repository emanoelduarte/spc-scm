<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class AddressesRepository extends DbConnection
{
    /**
     * Cadastrar endereço e vincular a uma entidade
     * @param array $data Dados do endereço
     * @param int $entityId ID do usuário ou fornecedor
     * @param string $entityType 'user' ou 'supplier'
     */
    public function createAddress(array $data, int $entityId, string $entityType): bool
    {
        try {
            $this->getConnection()->beginTransaction();

            // 1. Cadastrar o endereço
            $sql = "INSERT INTO adms_daman_addresses 
                        (zip_code, street, number, complement, neighborhood, city, state, created_at)
                    VALUES 
                        (:zip_code, :street, :number, :complement, :neighborhood, :city, :state, :created_at)";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':zip_code',      $data['zip_code']);
            $stmt->bindValue(':street',        $data['street']);
            $stmt->bindValue(':number',        $data['number']);
            $stmt->bindValue(':complement',    $data['complement'] ?? null);
            $stmt->bindValue(':neighborhood',  $data['neighborhood']);
            $stmt->bindValue(':city',          $data['city']);
            $stmt->bindValue(':state',         $data['state']);
            $stmt->bindValue(':created_at',    date('Y-m-d H:i:s'));
            $stmt->execute();

            // 2. Pegar o ID do endereço recém criado
            $addressId = $this->getConnection()->lastInsertId();

            // 3. Vincular à entidade correta
            $this->createRelation($addressId, $entityId, $entityType);

            $this->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            GenerateLog::generateLog("error", "Endereço não cadastrado.", [
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'error'       => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cria o vínculo na tabela relacional correta
     */
    private function createRelation(int $addressId, int $entityId, string $entityType): void
    {
        $table  = $entityType === 'user'
            ? 'adms_daman_user_addresses'
            : 'adms_daman_supplier_addresses';

        $column = $entityType === 'user'
            ? 'adms_daman_user_id'
            : 'adms_daman_supplier_id';

        $sql = "INSERT INTO {$table} (adms_daman_address_id, {$column}, created_at)
                VALUES (:address_id, :entity_id, :created_at)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':address_id',  $addressId, PDO::PARAM_INT);
        $stmt->bindValue(':entity_id',   $entityId,  PDO::PARAM_INT);
        $stmt->bindValue(':created_at',  date('Y-m-d H:i:s'));
        $stmt->execute();
    }
}