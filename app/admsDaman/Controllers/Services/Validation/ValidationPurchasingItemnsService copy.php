<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationPurchasingItemnsService
 * 
 * Esta classe é responsável por validar os campos de compra em um formulário de criação de uma nova compra.
 * Ela garante que todos os campos ou ao menos os mais importantes atenda a critérios específicos de segurança e confirmação.
 * 
 *  @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationPurchasingItemnsService
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
        $errors = [];

        $validator = new Validator();

        // 🔹 Regras apenas para items
        $rules = [
            'items' => 'required|array',
        ];

        $validation = $validator->make($data, $rules);

        $validation->validate();

        // Se falhar nas regras básicas
        if ($validation->fails()) {
            $arrayErrors = $validation->errors();

            foreach ($arrayErrors->firstOfAll() as $key => $message) {
                $errors[$key] = $message;
            }

            return $errors;
        }

        // VALIDAÇÃO PRINCIPAL: pelo menos um checkbox marcado
        $items = $data['items'] ?? [];

        $temSelecionado = false;

        foreach ($items as $item) {
            if (isset($item['selected_item']) && $item['selected_item'] == 1) {
                $temSelecionado = true;
                break;
            }
        }

        if (!$temSelecionado) {
            $errors['items'] = 'Selecione pelo menos um item para continuar.';
        }

        return $errors;
    }
}
