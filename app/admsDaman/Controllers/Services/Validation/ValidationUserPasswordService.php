<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

class ValidationUserPasswordService
{
    /**
     * Validar os dados do formulário com dependencia Rakit.
     * 
     * @param array $data Dados do formulário.
     * @return array Lista de Erros.
     */
    public function validate(array $data): array
    {

        // Criar o array que deve receber as mensagens de erro
        $errors = [];

        // Instaciar a classe de validação
        $validator = new Validator();

        // Definir as regras de validação
        $rules = [
            'password'              => 'required',
            'confirm_password'      => 'required',
        ];

        // Se não existir/estiver ausente o ID, significa que é uma criação (cadastrar)
        $rules['password'] = 'required|min:6|regex:/[A-Z]/|regex:/[^\w\s]/';
        $rules['confirm_password'] = 'required|same:password';



        // Definir mensagens personalizadas
        $messages = [
            'id:required'               => 'Dados inválidos.',
            'id:integer'                => 'Dados inválidos.',

            'password:required'         => 'O campo senha é obrigatório.',
            'password:min'              => 'A senha deve ter no mínimo 6 caracters.',
            'password:regex'            => 'A senha deve ter pelo menos uma letra minúscula e um caractere especial.',
            'confirm_password:required' => 'Você deve confirmar a senha.',
            'confirm_password:same'     => 'As senhas precisam concidir',

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