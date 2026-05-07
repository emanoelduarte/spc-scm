<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationSupplierService
 * 
 * Esta classe é responsável por validar os campos de pedido em um formulário de criação de um novo fornecedor.
 * Ela garante que todos os campos ou ao menos os mais importantes atenda a critérios específicos de segurança e confirmação.
 * 
 *  @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationSupplierService
{
    /**
     * Validar os dados do formulário com dependencia Rakit.
     * 
     * Este método valida os inumeros campos de pedidos, garantindo que todos sejam preenchidos da forma correta.
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

        // Instaciar a classe de validação
        $validator->addValidator('uniqueInColumns', new UniqueInColumnsRule());

        // Definir as regras de validação
        $rules = [
            'legal_name' => 'required',
            'trade_name' => 'required',
            'cnpj' => 'required',
            'contact_name' => 'required',
            'phone' => 'required',
            'adms_daman_suppliers_types_id' => 'required|integer',
        ];

        if(!isset($data['id'])){
            $rules['cnpj'] = 'required|uniqueInColumns:adms_daman_suppliers,cnpj';
        } else {
            // Para edição, adicionar a validação de id e ignorar o próprio cnpj na verificação de cnpj
            $rules['id'] = 'required|integer';
            $rules['supplier_status'] = 'required|integer';
            $rules['cnpj'] = 'required|uniqueInColumns:adms_daman_suppliers,cnpj,' . $data['id'];
        }
        
        // Definir mensagens personalizadas
        $messages = [
            'id:required'                                    => 'Dados inválidos.',
            'id:integer'                                     => 'Dados inválidos.',
            'legal_name:required'                            => 'O campo nome do fornecedor é obrigatório.',
            'trade_name:required'                            => 'O campo nome fantasia é obrigatório.',
            'supplier_status:required'                       => 'Informe o status do fornecedor',
            'supplier_status:integer'                        => 'Dados inválidos',
            'cnpj:required'                                  => 'O campo CNPJ é obrigatório.',
            'cnpj:uniqueInColumns'                           => 'Já existe um fornecedor cadastrado com esse CNPJ informado.',
            'contact_name:required'                          => 'O campo contato é obrigatório.',
            'phone:required'                                 => 'O campo telefone é obrigatório.',
            'adms_daman_suppliers_types_id:required'         => 'Dados inválidos.',
            'adms_daman_suppliers_types_id:integer'          => 'Dados inválidos.',
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