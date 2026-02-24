<?php

namespace App\admsDaman\Controllers\Services\Validation;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\UniqueValueRepository;
use Exception;
use Rakit\Validation\Rule;

class UniqueInColumnsRule extends Rule
{
    // Mensagem de erro genérica
    protected $message = ":value já está em uso";

    // Parametros dinâmicos
    protected $fillableParams = ['table', 'columns', 'except'];

    public function check($value): bool
    {
        try {

            // Verificar se os parâmetros necessários existem
            $this->requireParameters(['table', 'columns']);

            // Recuperar os parametros
            $table = $this->parameter('table');
            $columns = explode(';', $this->parameter('columns')); //Espera-se que as colunas sejam uma string separada por ponto e virgula, por isso o uso do explode
            $except = $this->parameter('except');

            if ($except and $except == $value) {
                return true;
            }

            // instanciar o Repository para Verificar se existe Registro valor fornecido
            $validateUniqueValue = new UniqueValueRepository();

            // Percorrer o array de colunas 
            foreach ($columns as $column) {

                // Verificar se existe Registro valor fornecido
                if (!$validateUniqueValue->getRecord($table, $column, $value, $except)) {
                    return false; // Se algum registro já possui o valor que o usuário tá enviando, para não cadastrar no banco nesse caso
                }
            }

            return true;
        } catch (Exception $e) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Usuário não cadastrado.", ['error' => $e->getMessage()]);

            return false;
        }
    }
}