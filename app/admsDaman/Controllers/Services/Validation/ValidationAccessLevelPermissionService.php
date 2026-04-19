<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationAccessLevelPermissionService
 * 
 * Esta classe é responsável por validar o id do nível de acesso.
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationAccessLevelPermissionService
{
    /**
     * 
     * @param array $data Dados do formulário.
     * @return array Lista de Erros. Se não houver erros, o array será vazio.
     */
    public function validate(array $data): array
    {

        // Criar o array que deve receber as mensagens de erro
        $errors = [];

        // Instaciar a classe de validação para validar o formulário
        $validator = new Validator();

        // definir as regras de validação
        $validation = $validator->make($data, [
            'adms_daman_access_level_id' => 'required|integer'
        ]);

        // Definir mensagens personalizadas
        $validation->setMessages([
            'adms_daman_access_level_id:required' => 'Dados inválidos.',
            'adms_daman_access_level_id:integer' => 'Dados inválidos.'
        ]);

        // Validar dados
        $validation->validate();

        // Retornar os erros se houver
        if ($validation->fails()) {

            // Recuperar os erros
            $arrayErrors = $validation->errors();

            // Percorre o array de erros
            // firstofAll - obter a primeira mensagem de erro para cada campo validado.
            foreach ($arrayErrors->firstOfAll() as $key => $message) {
                $errors[$key] = $message;
            }
        }

        return $errors;
    }
}