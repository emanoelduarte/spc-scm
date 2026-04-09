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
        $rules = [];

        if(isset($data['adms_daman_order_types_id']) && ($data['adms_daman_order_types_id'] == 1 || $data['adms_daman_order_types_id'] == '')){
            $rules['items.*.description'] = 'required';
            $rules['items.*.purchased_quantity'] = 'required';
            $rules['items.*.adms_daman_measurement_units_id'] = 'required';
        } else {
            $rules['items.*.description'] = 'required';
            $rules['items.*.purchased_quantity'] = 'required';
            $rules['items.*.adms_daman_measurement_units_id'] = 'required';
            $rules['items.*.rented_quantity'] = 'required';
            $rules['items.*.returned_quantity'] = 'required';
        }

         // Definir mensagens personalizadas
        $messages = [
            'items.*.description:required' => 'O Campo descrição é obrigatório',
            'items.*.quantity:required'    => 'O Campo quantidade é obrigatório',
            'items.*.adms_daman_measurement_units_id:required'        => 'O campo unidade é obrigatório',
        ];

        // Criar o validador com os dados e regras fornecidas
        $validation = $validator->make($data, $rules);

        //Definir as mensagens de erro personalizadas
        $validation->setMessages($messages);

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
