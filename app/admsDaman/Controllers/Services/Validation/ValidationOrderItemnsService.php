<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationOrderItemnsService
 * 
 *  @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * 
 * 
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationOrderItemnsService
{
    /**
     * Validar os dados do formulário com dependencia Rakit.
     * 
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
            'description.*'              => 'required',
            'quantity.*'              => 'required',
            'unit.*'              => 'required',
        ]);

        // Setar mensagens
        $validation->setMessages([
            'description.*:required' => 'O Campo descrição é obrigatório',
            'quantity.*:required' => 'O Campo quantidade é obrigatório',
            'unit.*:required' => 'O campo unidade é obrigatório',
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

                if (is_array($message)) {
                    $errors[] = $message[0]; // pega a primeira mensagem
                } else {
                    $errors[] = $message;
                }
            }
        }

        return $errors;
    }
}
