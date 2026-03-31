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
        // $validation = $validator->make($data, [
        //     'name'              => 'required',
        //     'email'             => 'required|email|uniqueInColumns:adms_daman_users,email;username',
        //     'password'          => 'required|min:6|regex:/[A-Z]/|regex:/[^\w\s]/',
        //     'confirm_password'  => 'required|same:password'
        // ]);

        $rules = [
            'name'              => 'required',
            'email'             => 'required|email',
        ];

         // Se não existir/estiver ausente o ID, significa que é uma criação (cadastrar)
        if (!isset($data['id'])) {
            $rules['email'] = 'required|email|uniqueInColumns:adms_daman_users,email;username';
            $rules['password'] = 'required|min:6|regex:/[A-Z]/|regex:/[^\w\s]/';
            $rules['confirm_password'] = 'required|same:password';
        } else {
            // Para edição, adicionar a validação de id e ignorar o próprio usuário na verificação de e-mail
            $rules['id'] = 'required|integer';
            $rules['username'] = 'required|min:8|regex:/^\S*$/|uniqueInColumns:adms_daman_users,email;username,' . $data['id'];
            $rules['email'] = 'required|email|uniqueInColumns:adms_daman_users,email;username,' . $data['id'];
        }

        // Definir mensagens personalizadas
        $messages = [
            'id:required'               => 'Dados inválidos.',
            'id:integer'                => 'Dados inválidos.',
            'name:required'             => 'O campo nome é obrigatório.',
            'email:required'            => 'O campo e-mail é obrigatório.',
            'email:email'               => 'O campo e-mail deve ser um e-mail válido.',
            'email:uniqueInColumns'     => 'Já existe um usuário com este e-mail.',

            'username:required'         => 'O campo usuário é obrigatório.',
            'username:min'              => 'O usuário deve ter no mínimo 8 caracters.',
            'username:regex'            => 'O nome de usuário não pode ter espaço em branco.',
            'username:uniqueInColumns'  => 'Já existe um registro com este usuário de acesso.',
            'password:required'         => 'O campo senha é obrigatório.',
            'password:min'              => 'O senha precisa ter no mínimo 6 caracters.',
            'password:regex'            => 'O senha precisa ter no mínimo uma letra maiúscula e um caractere especial.',
            'confirm_password:required' => 'O campo confirmar senha é obrigatório.',
            'confirm_password:same'     => 'As senhas precisam concidir.',

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