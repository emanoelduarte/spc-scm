<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationUserPasswordService
 * 
 *  @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * 
 * Esta classe é responsável por validar os campos de senha e confirmação de senha em um formulário de usuário.
 * Ela garante que a senha atenda a critérios específicos de segurança e que a confirmação da senha coincida com a senha fornecida.
 * 
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationLoginService
{
    /**
     * Validar os dados do formulário com dependencia Rakit.
     * 
     * Este método valida os campos de senha e confirmação de senha, garantindo que a senha seja forte o suficiente e que a confirmação coincida com a senha.
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

        // Definir as regras de validação
        $validation = $validator->make($data, [
            'username'              => 'required',
            'password'              => 'required',
        ]);

        // Se não existir/estiver ausente o ID, significa que é uma criação (cadastrar)
        $validation->setMessages([
            'username:required' => 'O campo usuário é obrigatório',
            'password:required' => 'O campo senha é obrigatório',
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