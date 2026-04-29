<?php

namespace App\admsDaman\Helpers;

class NormalizeDecimal
{
    /**
     * Normatizar decimal.
     * 
     * @param string $formIdentifier Identificador do formulário
     * @return float Numero formatado.
     */

    public static function normalizeDecimal(string $value): float
    {
        $value = trim($value);

        if (str_contains($value, ',')) {
            // formato brasileiro: 1.234,56
            $value = str_replace('.', '', $value); // remove milhar
            $value = str_replace(',', '.', $value); // troca decimal
        }

        return (float) $value;
    }
}