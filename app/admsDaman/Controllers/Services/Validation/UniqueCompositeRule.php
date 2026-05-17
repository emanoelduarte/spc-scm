<?php

namespace App\admsDaman\Controllers\Services\Validation;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\UniqueValueRepository;
use Exception;
use Rakit\Validation\Rule;

class UniqueCompositeRule extends Rule
{
    protected $message = "Já existe um item com este nome nesta obra.";

    protected $fillableParams = ['table', 'columns', 'values', 'except'];

    public function check($value): bool
    {
        try {
            $this->requireParameters(['table', 'columns', 'values']);

            $table   = $this->parameter('table');
            $columns = explode(';', $this->parameter('columns'));
            $values  = explode(';', $this->parameter('values'));
            $except  = $this->parameter('except');

            $validateUniqueValue = new UniqueValueRepository();

            return $validateUniqueValue->getCompositeRecord($table, $columns, $values, $except);
        } catch (Exception $e) {
            GenerateLog::generateLog("error", "Erro na validação composta.", ['error' => $e->getMessage()]);
            return false;
        }
    }
}