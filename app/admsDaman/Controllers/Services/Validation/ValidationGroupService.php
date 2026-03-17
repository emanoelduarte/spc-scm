<?php

namespace App\admsDaman\Controllers\Services\Validation;

use Rakit\Validation\Validator;

/**
 * Classe ValidationGroupService
 * 
 *  @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * 
 * Esta classe é responsável por validar os dados de um formulário de cadastro e edição de pacotes, aplicando regras de validação para criação e edição de pacotes.
 * Ela utiliza o pacote `Rakit\Validation` para realizar as validações e inclui uma regra personalizada de unicidade em múltiplas colunas.
 * 
 * @package App\adms\Controllers\Services\Validation
 */
class ValidationGroupService
{
    /**
     * Validar os dados do formulário.
     * 
     * Este método valida os dados fornecidos no formulário de grupo, aplicando diferentes regras dependendo se é uma criação ou edição de grupo.
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

        // Definir as regras de validação
        $rules = [];

        // Se não existir/estiver ausente o ID, significa que é uma criação (cadastrar)
        if (!isset($data['id'])) {
            $rules['name'] = 'required|uniqueInColumns:adms_daman_groups_pages,name';
        } else {
            // Para edição, adicionar a validação de id e ignorar o próprio usuário na verificação do nome
            $rules['id'] = 'required|integer';
            $rules['name'] = 'required|uniqueInColumns:adms_daman_groups_pages,name,' . $data['id'];
        }

        // Definir mensagens personalizadas
        $messages = [
            'id:required'               => 'Dados inválidos.',
            'id:integer'                => 'Dados inválidos.',
            'name:required'             => 'O campo nome é obrigatório.',
            'name:uniqueInColumns'      => 'Já existe um grupo com este nome.',
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
