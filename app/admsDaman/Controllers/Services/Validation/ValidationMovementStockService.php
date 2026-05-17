<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationMovementStockService
 * 
 * Esta classe é responsável por validar os campos quantidade e obra para realizar a movimentação do estoque.
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationMovementStockService
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

        // Definir as regras de validação
        $rules = [];

        $rules['quantity'] = 'required';
        $rules['adms_daman_project_id'] = 'required|integer';

        if (isset($data['type']) && $data['type'] === 'output') {
            $rules['reason'] = 'required';
        }
        // definir as regras de validação
        $messages = [
            'quantity:required'                 => 'O campo quantidade é Obrigatório.',
            'adms_daman_project_id:required'    => 'O campo Obra é obrigatório.',
            'adms_daman_project_id:integer'     => 'Dados Inválidos.',
            'reason:required'                   => 'O motivo da saída é obrigatório.'
        ];

        // Criar o validador com os dados e regras fornecidas
        $validation = $validator->make($data, $rules);

        //Definir as mensagens de erro personalizadas
        $validation->setMessages($messages);

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