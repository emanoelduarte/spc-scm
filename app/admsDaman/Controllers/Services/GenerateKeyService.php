<?php
    
namespace App\admsDaman\Controllers\Services;
/**
 * Classe de serviço responsável por gerar chave para validação de recuperação de senha
 */
class GenerateKeyService
{
    public static function generateKey(): array
    {
        // Definir os caracters que podem ser utilizados (possiveis)
        $chars = 'abcdefghijklmnopqrstuvwyxz0123456789';

        // Embaralha os caracters
        $shuffle = str_shuffle($chars);

        // Extrai a chave de 12 caracters
        $key = substr($shuffle, 0, 12);

        // Criptografa a chave de 12 caracters
        $encryptedKey = password_hash($key, PASSWORD_DEFAULT);

        // Retorna a chave em texto claro e criptografado
        return [
            'key' => $key,
            'encryptedKey' => $encryptedKey
        ];
    }
}