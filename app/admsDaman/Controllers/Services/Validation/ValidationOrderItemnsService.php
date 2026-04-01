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
            'items.*.description' => 'required',
            'items.*.quantity'    => 'required',
            'items.*.adms_daman_measurement_units_id'        => 'required',
        ]);

        // Setar mensagens
        $validation->setMessages([
            'items.*.description:required' => 'O Campo descrição é obrigatório',
            'items.*.quantity:required'    => 'O Campo quantidade é obrigatório',
            'items.*.adms_daman_measurement_units_id:required'        => 'O campo unidade é obrigatório',
        ]);

        // Validar dados
        $validation->validate();

        // Retornar os erros se houver
        $errors = [];

        if ($validation->fails()) {

            $arrayErrors = $validation->errors()->toArray();

            foreach ($arrayErrors as $field => $items) {

                // Se for array (ex: items)
                if (is_array($items)) {

                    foreach ($items as $item) {

                        if (is_array($item)) {

                            foreach ($item as $message) {
                                $errors[] = $message;
                            }
                        } else {
                            $errors[] = $item;
                        }
                    }
                } else {
                    $errors[] = $items;
                }
            }
             // remove duplicados
        $errors = array_values(array_unique($errors));
        }

        return $errors;
    }
}
