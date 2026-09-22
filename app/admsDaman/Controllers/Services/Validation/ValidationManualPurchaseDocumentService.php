<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationManualPurchaseDocumentService
 *
 * Esta classe é responsável por validar os campos
 * do lançamento financeiro manual.
 *
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationManualPurchaseDocumentService
{
    /**
     * Validar os dados do formulário com dependencia Rakit.
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

        $rules['adms_daman_supplier_id'] = 'required|integer';
        $rules['adms_daman_project_id'] = 'required|integer';
        $rules['adms_daman_user_id'] = 'required|integer';


        /*
        * ==========================================================
        * CONDIÇÃO DE PAGAMENTO
        * ==========================================================
        *
        * Quando o lançamento estiver marcado como
        * "Falta boleto / parcelas ainda não confirmadas",
        * a condição de pagamento pode ainda não ser conhecida.
        *
        * Nesse caso ela não será obrigatória.
        *
        * Se o usuário já souber a condição e informar mesmo
        * com o FB marcado, validamos normalmente como inteiro.
        */
        $paymentSchedulePending =
            !empty($data['payment_schedule_pending']);


        if (!$paymentSchedulePending) {

            /*
            * Fluxo normal:
            * condição de pagamento obrigatória.
            */
            $rules['adms_daman_payment_method_id'] =
                'required|integer';
        } elseif (
            !empty($data['adms_daman_payment_method_id'])
        ) {

            /*
            * FB marcado, mas o usuário informou
            * uma condição de pagamento.
            *
            * Não é obrigatória, porém precisa ser válida.
            */
            $rules['adms_daman_payment_method_id'] =
                'integer';
        }


        $rules['purchase_date'] = 'required';

        /*
         * Não usar numeric aqui.
         *
         * O valor chega no padrão brasileiro:
         * 1.000,00
         *
         * A validação financeira continuará sendo
         * realizada no PurchaseDocumentService.
         */
        $rules['total_value'] = 'required';


        /*
         * Tipo do documento não é obrigatório,
         * pois o lançamento pode ser sem documento.
         *
         * Mas se existir, validamos os valores permitidos.
         */
        if (!empty($data['document_type'])) {
            $rules['document_type'] =
                'in:CUPOM,RECIBO,NOTA,OUTRO,SEM_DOCUMENTO';
        }


        /*
         * Se houver data do documento,
         * garantir que não seja uma string inválida.
         */
        if (
            isset($data['document_date'])
            &&
            $data['document_date'] !== ''
        ) {
            $rules['document_date'] = 'required';
        }


        // Definir mensagens personalizadas
        $messages = [

            'adms_daman_supplier_id:required'
            => 'Fornecedor não pode ser vazio.',

            'adms_daman_supplier_id:integer'
            => 'Dados do fornecedor inválidos.',


            'adms_daman_project_id:required'
            => 'Obra não pode ser vazia.',

            'adms_daman_project_id:integer'
            => 'Dados da obra inválidos.',


            'adms_daman_user_id:required'
            => 'Comprador não pode ser vazio.',

            'adms_daman_user_id:integer'
            => 'Dados do comprador inválidos.',


            'adms_daman_payment_method_id:required'
            => 'Condição de pagamento não pode ser vazia.',

            'adms_daman_payment_method_id:integer'
            => 'Condição de pagamento inválida.',


            'purchase_date:required'
            => 'Data da compra não pode ser vazia.',


            'total_value:required'
            => 'Valor total da compra não pode ser vazio.',


            'document_type:in'
            => 'Tipo de documento inválido.',


            'document_date:required'
            => 'Data do documento inválida.',
        ];


        // Criar o validador com os dados e regras fornecidas
        $validation = $validator->make(
            $data,
            $rules
        );

        // Definir as mensagens de erro personalizadas
        $validation->setMessages(
            $messages
        );

        // Validar dados
        $validation->validate();


        // Retornar os erros se houver
        if ($validation->fails()) {

            // Recuperar os erros
            $arrayErrors = $validation->errors();

            /*
             * Percorrer o array de erros.
             *
             * firstOfAll - obter a primeira mensagem
             * de erro para cada campo validado.
             */
            foreach (
                $arrayErrors->firstOfAll()
                as $key => $message
            ) {
                $errors[$key] = $message;
            }
        }


        return $errors;
    }
}
