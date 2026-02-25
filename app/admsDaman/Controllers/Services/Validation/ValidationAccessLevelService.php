<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationAccessLevelService
 * 
 * 
 * Esta classe é responsável por validar os dados de um formulário de nível de acesso, aplicando regras de validação para criação e edição de nível de acess.
 * Ela utiliza o pacote `Rakit\Validation` para realizar as validações e inclui uma regra personalizada de unicidade em múltiplas colunas.
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\Services\Validation
 */
class ValidationAccessLevelService
{
    /**
     * Validar os dados do formulário.
     * 
     * Este método valida os dados fornecidos no formulário de usuário, aplicando diferentes regras dependendo se é uma criação ou edição de usuário.
     * 
     * @param array $data Dados do formulário.
     * @return array Lista de erros. Se não houver erros, o array será vazio.
     */

    public function validate(array $data): array
    {
        // Criar o array que deve receber as mensagens de erro
        $errors = [];

        // Instaciar a classe de validação
        $validator = new Validator();

        $validator->addValidator('uniqueInColumns', new UniqueInColumnsRule());

        // Definir regras de validação

        $rules = [
            'name'          => 'required',
        ];

        // Se não tiver/existir o ID, significa que é uma criação (cadastrar)

        if (!isset($data['id'])) {
            $rules['name'] = 'required|uniqueInColumns:adms_daman_access_levels,name';
        } else {
            // Para edição, adicionar a validação de id e ignorar o próprio usuário na verificação de e-mail
            $rules['name'] = 'required|min:4|uniqueInColumns:adms_daman_access_levels,name,' . $data['id'];
        }

        // Definir mensagens personalizadas
        $messages = [
            'id:required'     => 'Dados inválidos.',
            'id:integer'      => 'Dados inválidos.',
            'name:required'   => 'O campo nome é obrigatório.',
            'name:min'   => 'O campo nome precisa ter no mínimo 4 caracters.',
            'name:uniqueInColumns' => "O nível de acesso {name} já está em uso.",
        ];

        // Criar a o validador com os dados e regras fornecidas
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
                $name = "<strong>{$data['name']}</strong>";
                $errors[$key] = str_replace('{name}', $name ?? '', $message);
            }
        }

        return $errors;
    }
}