<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationMaterialStockService
 * 
 * Esta classe é responsável por validar os campos quantidade e obra para realizar a movimentação do estoque.
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationMaterialStockService
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
            'quantity' => 'required',
            'adms_daman_project_id' => 'required',
        ]);

        // Definir mensagens personalizadas
        $validation->setMessages([
            'quantity:required'                 => 'O campo quantidade é Obrigatório.',
            'adms_daman_project_id:required'    => 'O campo Obra é obrigatório.',
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