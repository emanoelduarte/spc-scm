<?php

declare(strict_types=1);

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

class ValidationDirectExpenseService
{
    /**
     * Validar os campos básicos da despesa direta.
     *
     * O valor monetário é normalizado posteriormente pelo Service,
     * pois pode chegar no padrão brasileiro: 8.450,00.
     *
     * @param array $data
     * @return array
     */
    public function validate(array $data): array
    {
        $validator =
            new Validator();

        $rules = [
            'adms_daman_project_id' =>
                'required|integer',

            'adms_daman_expense_category_id' =>
                'required|integer',

            'adms_daman_financial_payment_method_id' =>
                'required|integer',

            'expense_date' =>
                'required',

            'description' =>
                'required|max:255',

            'amount' =>
                'required',
        ];

        $messages = [
            'adms_daman_project_id:required' =>
                'Selecione a obra.',

            'adms_daman_project_id:integer' =>
                'Obra inválida.',

            'adms_daman_expense_category_id:required' =>
                'Selecione a categoria da despesa.',

            'adms_daman_expense_category_id:integer' =>
                'Categoria da despesa inválida.',

            'adms_daman_financial_payment_method_id:required' =>
                'Selecione a forma de pagamento.',

            'adms_daman_financial_payment_method_id:integer' =>
                'Forma de pagamento inválida.',

            'expense_date:required' =>
                'Informe a data do desembolso.',

            'description:required' =>
                'Informe a descrição da despesa.',

            'description:max' =>
                'A descrição deve possuir no máximo 255 caracteres.',

            'amount:required' =>
                'Informe o valor da despesa.',
        ];

        $validation =
            $validator->make(
                $data,
                $rules
            );

        $validation->setMessages(
            $messages
        );

        $validation->validate();

        if (!$validation->fails()) {
            return [];
        }

        return
            $validation
                ->errors()
                ->firstOfAll();
    }
}
