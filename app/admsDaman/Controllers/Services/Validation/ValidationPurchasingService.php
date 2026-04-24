<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationPurchasingService
 * 
 * Esta classe é responsável por validar os campos de compra em um formulário de criação de uma nova compra.
 * Ela garante que todos os campos ou ao menos os mais importantes atenda a critérios específicos de segurança e confirmação.
 * 
 *  @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationPurchasingService
{
    /**
     * Validar os dados do formulário com dependencia Rakit.
     * 
     * Este método valida os inumeros campos de compras, garantindo que todos sejam preenchidos da forma correta.
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


        $rules = [
            'adms_daman_supplier_id' => 'required|integer',
            'expected_receipt_date' => 'required',
            'service' => 'required',
            'delivery_address' => 'required',
            'adms_daman_payment_methods_id' => 'required|integer',
            ];

        // Definir mensagens personalizadas
        $messages = [
            'adms_daman_supplier_id:required'           => 'Informe um fornecedor',
            'adms_daman_supplier_id:integer'            => 'Dados inválidos.',
            'expected_receipt_date:required'            => 'Prazo de entrega não pode ser vazio.',
            'service:required'                          => 'Serviço não pode ser vazio.',
            'delivery_address:required'                 => 'Endereço de entrega é obrigatório',
            'adms_daman_payment_methods_id:required'    => 'Por favor, escolha uma forma de pagamento válida.',
            'adms_daman_payment_methods_id:integer'     => 'Dados inválidos.',
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
