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
    public static function generatePagination(int $totalRecords, int $limitResult, int $currentPage, string $urlController, ?array $filters = []): array
    {
        $lastPage = (int) ceil($totalRecords / $limitResult);

        // Remover campos que não são filtros
        $cleanFilters = array_filter($filters ?? [], function ($value, $key) {
            return !empty($value) && !in_array($key, ['csrf_token', 'submit', 'url']);
        }, ARRAY_FILTER_USE_BOTH);

        // Serializar os filtros como query string
        $queryString = !empty($cleanFilters) ? '?' . http_build_query($cleanFilters) : '';

        return [
            'amount_records'  => $totalRecords,
            'last_page'       => $lastPage,
            'current_page'    => $currentPage == 0 ? 1 : $currentPage,
            'url_controller'  => $urlController,
            'query_string'    => $queryString
        ];
    }
}
