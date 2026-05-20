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

        $validator->addValidator('uniqueComposite', new UniqueCompositeRule());

        // Definir as regras de validação
        $rules = [];

        $rules['adms_daman_measurement_units_id'] = 'required|integer';
        $rules['adms_daman_project_id'] = 'required|integer';
        $rules['min_quantity'] = 'required';
        $rules['adms_daman_category_id'] = 'required|integer';

        if (!isset($data['id'])) {
            $rules['name'] = 'required|uniqueComposite:adms_daman_material_stock,name;adms_daman_project_id,' . $data['name'] . ';' . $data['adms_daman_project_id'];
            $rules['quantity'] = 'required';
        } else {
            $rules['name'] = 'required|uniqueComposite:adms_daman_material_stock,name;adms_daman_project_id,' . $data['name'] . ';' . $data['adms_daman_project_id'] . ',' . $data['id'];
        }

        // definir as regras de validação
        $messages = [
            'name:uniqueComposite'                        => 'Já existe um item com este nome nesta obra.<br>Considere uma nova <strong>Entrada</strong> do item.',
            'adms_daman_measurement_units_id:required'    => 'O campo unidade é obrigatório.',
            'adms_daman_measurement_units_id:integer'     => 'Dados Inválidos.',
            'adms_daman_project_id:required'              => 'O campo obra é obrigatório.',
            'adms_daman_project_id:integer'               => 'Dados Inválidos.',
            'adms_daman_category_id:required'             => 'O campo categoria é obrigatório.',
            'adms_daman_category_id:integer'              => 'O campo categoria é obrigatório.',
            'quantity:required'                           => 'O campo quantidade é obrigatório.',
            'min_quantity:required'                       => 'O campo quantidade mínima é obrigatório.',
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