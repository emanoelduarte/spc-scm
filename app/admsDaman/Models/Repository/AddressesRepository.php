<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class AddressesRepository extends DbConnection
{
    /**
     * Cadastrar endereço e vincular a uma entidade.
     *
     * @param array $data Dados do endereço
     * @param int $entityId ID do usuário ou fornecedor
     * @param string $entityType 'user' ou 'supplier'
     */
    public function createAddress(array $data, int $entityId, string $entityType): bool
    {
        try {
            $this->getConnection()->beginTransaction();

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

            $addressId = (int) $this->getConnection()->lastInsertId();

            $this->createRelation($addressId, $entityId, $entityType);

            $this->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            if ($this->getConnection()->inTransaction()) {
                $this->getConnection()->rollBack();
            }

            GenerateLog::generateLog("error", "Endereço não cadastrado.", [
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'error'       => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Recuperar o endereço vinculado a uma entidade.
     *
     * @return array|bool
     */
    public function getAddressByEntity(int $entityId, string $entityType): array|bool
    {
        try {
            [$relationTable, $entityColumn] = $this->getRelationConfig($entityType);

            $sql = "SELECT
                        ada.id AS address_id,
                        ada.zip_code,
                        ada.street,
                        ada.number,
                        ada.complement,
                        ada.neighborhood,
                        ada.city,
                        ada.state
                    FROM adms_daman_addresses AS ada
                    INNER JOIN {$relationTable} AS rel
                        ON rel.adms_daman_address_id = ada.id
                    WHERE rel.{$entityColumn} = :entity_id
                    LIMIT 1";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':entity_id', $entityId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            GenerateLog::generateLog("error", "Endereço não encontrado.", [
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'error'       => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Atualizar o endereço vinculado a uma entidade.
     *
     * Caso seja um cadastro antigo sem endereço relacionado,
     * cria o endereço e o vínculo em vez de falhar.
     */
    public function updateAddress(array $data, int $entityId, string $entityType): bool
    {
        try {
            $address = $this->getAddressByEntity($entityId, $entityType);

            if (!$address || empty($address['address_id'])) {
                return $this->createAddress($data, $entityId, $entityType);
            }

            $sql = "UPDATE adms_daman_addresses
                    SET
                        zip_code = :zip_code,
                        street = :street,
                        number = :number,
                        complement = :complement,
                        neighborhood = :neighborhood,
                        city = :city,
                        state = :state
                    WHERE id = :address_id";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':zip_code',     $data['zip_code']);
            $stmt->bindValue(':street',       $data['street']);
            $stmt->bindValue(':number',       $data['number']);
            $stmt->bindValue(':complement',   $data['complement'] ?? null);
            $stmt->bindValue(':neighborhood', $data['neighborhood']);
            $stmt->bindValue(':city',         $data['city']);
            $stmt->bindValue(':state',        $data['state']);
            $stmt->bindValue(':address_id',   (int) $address['address_id'], PDO::PARAM_INT);
            $stmt->execute();

            // Assim como no fornecedor, rowCount() = 0 também pode
            // significar apenas que os valores permaneceram iguais.
            return true;
        } catch (Exception $e) {
            GenerateLog::generateLog("error", "Endereço não editado.", [
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'error'       => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Cria o vínculo na tabela relacional correta.
     */
    private function createRelation(int $addressId, int $entityId, string $entityType): void
    {
        [$table, $column] = $this->getRelationConfig($entityType);

        $sql = "INSERT INTO {$table} (adms_daman_address_id, {$column}, created_at)
                VALUES (:address_id, :entity_id, :created_at)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':address_id', $addressId, PDO::PARAM_INT);
        $stmt->bindValue(':entity_id',  $entityId, PDO::PARAM_INT);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
        $stmt->execute();
    }

    /**
     * Retornar tabela de relação e coluna da entidade.
     */
    private function getRelationConfig(string $entityType): array
    {
        if ($entityType === 'user') {
            return [
                'adms_daman_user_addresses',
                'adms_daman_user_id',
            ];
        }

        if ($entityType === 'supplier') {
            return [
                'adms_daman_supplier_addresses',
                'adms_daman_supplier_id',
            ];
        }

        throw new Exception('Tipo de entidade de endereço inválido.');
    }
}
