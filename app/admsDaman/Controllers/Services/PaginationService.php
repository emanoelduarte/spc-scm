<?php
    
namespace App\admsDaman\Controllers\Services;

/**
 * Controller resppnsável por criar a paginação
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 */
class PaginationService
{
    /**
     * Gerar os dados de paginação
     * @param int $totalRecords Total de registros do banco de dados
     * @param int $limitResult Limite de Registros por página
     * @param int $currentPage Página Atual
     * @param string $urlController URL da controller
     * @return array Dados da paginação
     */
    public static function generatePagination(int $totalRecords, int $limitResult, int $currentPage, string $urlController): array
    {

        // Calcular a ultima página
        $lastPage = (int) ceil($totalRecords / $limitResult);

        // Retornar os dados da paginação
        return [
            'amount_records' => $totalRecords,
            'last_page' => $lastPage,
            'current_page' => $currentPage == 0 ? 1 : $currentPage,
            'url_controller' => $urlController
        ];
    }
}
?>