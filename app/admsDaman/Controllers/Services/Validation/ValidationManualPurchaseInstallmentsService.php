<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationManualPurchaseInstallmentsService
 *
 * Responsável por validar as parcelas
 * do lançamento financeiro manual.
 *
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationManualPurchaseInstallmentsService
{
    /**
     * Validar as parcelas do formulário com dependencia Rakit.
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


        /*
         * Não permitir lançamento sem parcelas.
         */
        if (empty($data['installments'])) {

            $errors[] =
                'É necessário gerar pelo menos uma parcela.';

            return $errors;
        }


        /*
         * Percorrer todas as parcelas.
         */
        foreach (
            $data['installments']
            as $index => $installment
        ) {

            // Definir as regras de validação
            $rules = [];

            $rules['installment_number'] =
                'required|integer';

            $rules['original_amount'] =
                'required';

            $rules['status'] =
                'required|in:AV,AT,ON,OK,AP';


            /*
             * Permuta pode ficar sem vencimento.
             */
            if (
                !isset($installment['status'])
                ||
                $installment['status'] !== 'AP'
            ) {

                $rules['due_date'] =
                    'required';
            }


            // Definir mensagens personalizadas
            $messages = [

                'installment_number:required'
                    => 'Número da parcela inválido.',

                'installment_number:integer'
                    => 'Número da parcela inválido.',


                'original_amount:required'
                    => 'Valor da parcela não pode ser vazio.',


                'status:required'
                    => 'Situação da parcela não pode ser vazia.',

                'status:in'
                    => 'Situação da parcela inválida.',


                'due_date:required'
                    => 'Vencimento da parcela não pode ser vazio.',
            ];


            /*
             * Criar o validador para esta parcela.
             */
            $validation =
                $validator->make(
                    $installment,
                    $rules
                );


            // Definir mensagens personalizadas
            $validation->setMessages(
                $messages
            );


            // Validar dados
            $validation->validate();


            if ($validation->fails()) {

                // Recuperar os erros
                $arrayErrors =
                    $validation->errors();


                /*
                 * Recuperar o primeiro erro
                 * de cada campo.
                 */
                foreach (
                    $arrayErrors->firstOfAll()
                    as $key => $message
                ) {

                    /*
                     * Exemplo:
                     *
                     * installments.0.original_amount
                     */
                    $errors[
                        "installments.$index.$key"
                    ] = $message;
                }
            }
        }


        /*
         * Remover mensagens repetidas.
         *
         * Mesmo padrão utilizado na validação
         * dos itens de pedido.
         */
        $errors =
            array_values(
                array_unique(
                    $errors
                )
            );


        return $errors;
    }
}