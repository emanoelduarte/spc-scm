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

    public function getRecord($table, $column, $value)
    {
        // Query para recuperar o registro do banco de dados
        $sql = "SELECT COUNT(id) as count FROM `{$table}` WHERE `{$column}` = :value";

        // Preparar a Querry
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir o link pelo valor
        $stmt->bindParam('value', $value, PDO::PARAM_STR);

        // Executar a querry
        $stmt->execute();

        // Retorna false se o valor fornecido já estiver cadastrada, verdadeiro, caso contrário.

        return $stmt->fetchColumn() === 0;
    }
}