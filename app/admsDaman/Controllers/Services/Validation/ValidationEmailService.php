<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationEmailService
 * 
 * Esta classe é responsável por validar o campo email e para recuperar a senha do usuário.
 * Ela garante que a senha atenda a critérios específicos de segurança.
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationEmailService
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

        // Instaciar a classe de validação
        $validator = new Validator();

        // definir as regras de validação
        $validation = $validator->make($data, [
            'email' => 'required|email'
        ]);

        // Definir mensagens personalizadas
        $validation->setMessages([
            'email:required' => 'O campo e-mail é obrigatório.',
            'email:email' => 'O campo e-mail deve ser um e-mail válido.'
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