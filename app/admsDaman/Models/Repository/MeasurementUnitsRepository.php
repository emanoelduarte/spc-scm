<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;
use PDO;

class MeasurementUnitsRepository extends DbConnection
{
    /**
     * Recuperar uma Unidade de medida específica
     * 
     * @return array|bool Unidade de medida recuperada do banco de dados
     */
    public function getAllMeasurementUnitsSelect(): array|bool
    {
        // QUERY para recuperar os registros do banco de dados
        $sql = 'SELECT id, name
                FROM adms_daman_measurement_units AS admu
                ORDER BY name ASC';

        // Preparar a QUERY
        $stmt = $this->getConnection()->prepare($sql);

        // Executar a QUERY
        $stmt->execute();

        // Ler os registros e retornar 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
