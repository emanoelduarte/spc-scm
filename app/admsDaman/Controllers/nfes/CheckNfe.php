<?php

namespace App\admsDaman\Controllers\nfes;

use App\admsDaman\Models\Repository\NfeRepository;

/**
 * Controller responsável por marcar
 * uma NF-e como conferida.
 */
class CheckNfe
{
    /**
     * Marcar NF-e como conferida.
     *
     * @param string|int $id
     * @return void
     */
    public function index(string|int $id): void
    {
        $nfeRepository = new NfeRepository();

        $result = $nfeRepository->markAsChecked((int) $id);

        if ($result) {

            $_SESSION['success'] = "NF-e marcada como conferida com sucesso.";

        } else {

            $_SESSION['error'] = "Não foi possível marcar a NF-e como conferida.";
        }

        header(
            'Location: ' . $_ENV['URL_ADM'] . 'list-nfes'
        );

        exit;
    }
}