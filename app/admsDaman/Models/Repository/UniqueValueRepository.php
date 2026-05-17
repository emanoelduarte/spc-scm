<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;
use PDO;

/**
 * Repository responsável em verificar se existe um registro com dados fornecidos
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 */
class UniqueValueRepository extends DbConnection
{
    /**
     * Recuperar o registro com o dado fornecido
     * @return bool Retornar falso se o valor fornecido já estiver cadastrado, verdadeiro caso contrário
     */

    public function getRecord($table, $column, $value, $except = null): bool
    {
        // Query para recuperar o registro do banco de dados
        $sql = "SELECT COUNT(id) as count FROM `{$table}` WHERE `{$column}` = :value";

        // Se houver um ID de excessão, adicionar a condição à consulta
        if ($except !== null) {
            $sql .= " AND `id` != :exept";
        }

        // Preparar a Querry
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir o link pelo valor
        $stmt->bindParam('value', $value, PDO::PARAM_STR);

        // Substitui o link da exeção se existir
        if ($except !== null) {
            $stmt->bindParam(':exept', $except, PDO::PARAM_INT);
        }

        // Executar a querry
        $stmt->execute();

        // Retorna false se o valor fornecido já estiver cadastrada, verdadeiro, caso contrário.

        return $stmt->fetchColumn() === 0;
    }

    /**
     * Verificar duas colunas (valores compostos fazendo a combinação para verificar se existe um item com o mesmo nome na obra)
     */
    public function getCompositeRecord(string $table, array $columns, array $values, $except = null): bool
    {
        $conditions = [];
        foreach ($columns as $index => $column) {
            $conditions[] = "`{$column}` = :value{$index}";
        }

        $sql = "SELECT COUNT(id) as count FROM `{$table}` WHERE " . implode(' AND ', $conditions);

        if ($except !== null) {
            $sql .= " AND `id` != :except";
        }

        $stmt = $this->getConnection()->prepare($sql);

        foreach ($values as $index => $value) {
            $stmt->bindValue(":value{$index}", $value);
        }

        if ($except !== null) {
            $stmt->bindValue(':except', $except, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchColumn() === 0;
    }
}
