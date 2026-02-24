<?php

namespace App\admsDaman\Helpers;
/**
 * Gerar token e validar CSRF
 * 
 * @author Cesar <emanoel.c.duarte@hotmail.com>
 */
class CSRFHelper
{

    /**
     * Gerar um token único.
     * 
     * @param string $formIdentifier Identificador do formulário
     * @return string Token CSRF gerado.
     */

    public static function generateCSRFToken(string $formIdentifier): string
    {
        // A função random_bytes gera uma sequência de 32 bytes aleatórios.
        //A função bin2hex converte os bytes binários gerados pela random_bytes em uma representação hexadecimal.
        $token = bin2hex(random_bytes(32));

        $_SESSION['csrf_tokens'][$formIdentifier] = $token;

        return $token;
    }

    /**
     * Validar um token CSRF.
     * 
     * @param string $formIdentifier Identificador do formulário.
     * @param string $token CSRF para validar
     * @return bool True se o token for válido ou False caso contrário.
     */
    public static function validateCSRFToken(string $formIdentifier, string $token)
    {
        if (isset($_SESSION['csrf_tokens'][$formIdentifier]) && hash_equals($_SESSION['csrf_tokens'][$formIdentifier], $token)) {

            // Token usado deve ser inválidado, uso único
            unset($_SESSION['csrf_tokens'][$formIdentifier]);

            return true;
        }
        return false;
    }
}