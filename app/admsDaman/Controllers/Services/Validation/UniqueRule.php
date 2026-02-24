<?php

namespace App\admsDaman\Controllers\Services\Validation;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\UniqueValueRepository;
use Exception;
use Rakit\Validation\Rule;

class UniqueRule extends Rule
{
    // Mensagem de erro genérica
    protected $message = ":value já está em uso";

    // Parametros dinâmicos
    protected $fillableParams = ['table', 'column', 'except'];

    public function check($value): bool
    {
        try {

            // Verificar se os parâmetros necessários existem
            $this->requireParameters(['table', 'column']);

            // Recuperar os parametros
            $table = $this->parameter('table');
            $column = $this->parameter('column');
            $except = $this->parameter('except');

            if ($except and $except == $value) {
                return true;
            }

            // instanciar o Repository para Verificar se existe Registro valor fornecido
            $validateUniqueValue = new UniqueValueRepository();
            return $validateUniqueValue->getRecord($table, $column, $value);
        } catch (Exception $e) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Usuário não cadastrado.", ['error' => $e->getMessage()]);

            return false;
        }
    }
}