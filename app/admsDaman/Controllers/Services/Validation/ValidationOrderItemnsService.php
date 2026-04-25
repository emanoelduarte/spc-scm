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

        $rules['description'] = 'required';
        $rules['adms_daman_measurement_units_id'] = 'required';

        if (isset($data['id']) && isset($data['item_id']) && isset($data['adms_daman_acquisition_types_id']) && ($data['adms_daman_acquisition_types_id'] == 1)) {

            $rules['purchased_quantity'] = 'required';

        } else if (isset($data['id']) && isset($data['item_id']) && isset($data['adms_daman_acquisition_types_id']) && ($data['adms_daman_acquisition_types_id'] == 2)) {

            $rules['rented_quantity'] = 'required';

        } else { // Para cadastro de um novo

            $rules['quantity'] = 'required';
        }

        // Definir mensagens personalizadas
        $messages = [
            'description:required' => 'O Campo descrição é obrigatório',

            'quantity:required' => 'O Campo quantidade é obrigatório',
            'purchased_quantity:required' => 'O Campo quantidade comprada é obrigatório',
            'rented_quantity:required' => 'O Campo quantidade locada é obrigatório',

            'adms_daman_measurement_units_id:required' => 'O campo unidade é obrigatório',
        ];

        $errors = [];

        if (!empty($data['items'])) {

            foreach ($data['items'] as $index => $item) {

                $validation = $validator->make($item, $rules);
                $validation->setMessages($messages);
                $validation->validate();

                if ($validation->fails()) {
                    $arrayErrors = $validation->errors();

                    foreach ($arrayErrors->firstOfAll() as $key => $message) {
                        // Ex: items.0.quantity
                        $errors["items.$index.$key"] = $message;
                    }
                }
            }
        }

        $errors = array_values(array_unique($errors));
        return $errors;
    }
}