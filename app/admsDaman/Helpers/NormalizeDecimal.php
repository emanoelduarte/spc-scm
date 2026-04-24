<?php

namespace App\admsDaman\Helpers;

class NormalizeDecimal
{
    /**
     * Gerar um token único.
     * 
     * @param string $formIdentifier Identificador do formulário
     * @return string Token CSRF gerado.
     */

    public static function normalizeDecimal(string $value)
    {
        // remove separador de milhar (.)
        $value = str_replace('.', '', $value);

        // troca vírgula por ponto
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }
}
