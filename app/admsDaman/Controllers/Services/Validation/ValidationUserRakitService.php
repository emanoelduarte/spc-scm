<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

class ValidationUserRakitService
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

        $validator->addValidator('uniqueInColumns', new UniqueInColumnsRule());

        // Definir as regras de validação
        $validation = $validator->make($data, [
            'name'              => 'required',
            'email'             => 'required|email|uniqueInColumns:adms_daman_users,email;username',
            'password'          => 'required|min:6|regex:/[A-Z]/|regex:/[^\w\s]/',
            'confirm_password'  => 'required|same:password'
        ]);

        // Definir mensagens personalizadas
        $validation->setMessages([
            'name:required'             => 'O campo nome é obrigatório.',
            'email:required'            => 'O campo e-mail é obrigatório.',
            'email:email'               => 'O campo e-mail deve ser um e-mail válido.',
            'email:uniqueInColumns'     => 'Já existe um usuário com este e-mail.',
            'password:required'         => 'O campo senha é obrigatório.',
            'password:min'              => 'A senha deve ter no mínimo 6 caracters.',
            'password:regex'            => 'A senha deve ter pelo menos uma letra minúscula e um caractere especial.',
            'confirm_password:required' => 'Você deve confirmar a senha.',
            'confirm_password:same'     => 'As senhas precisam concidir'
        ]);

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