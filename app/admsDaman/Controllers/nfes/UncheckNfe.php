<?php

namespace App\admsDaman\Controllers\nfes;

use App\admsDaman\Models\Repository\NfeRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;

/**
 * Controller responsável por marcar
 * uma NF-e como não conferida.
 */
class UncheckNfe
{
    /**
     * Marcar NF-e como não conferida.
     *
     * A conferência só pode ser desfeita enquanto
     * a NF-e ainda não possuir lançamento financeiro.
     *
     * @param string|int $id
     * @return void
     */
    public function index(string|int $id): void
    {
        $nfeId = (int) $id;


        /*
         * =====================================================
         * VALIDAR ID
         * =====================================================
         */
        if ($nfeId <= 0) {

            $_SESSION['error'] =
                'NF-e inválida.';

            $this->redirectToList();
        }


        /*
         * =====================================================
         * VALIDAR EXISTÊNCIA DA NF-e
         * =====================================================
         */
        $nfeRepository =
            new NfeRepository();

        $nfe =
            $nfeRepository->getNfeById(
                $nfeId
            );


        if (!$nfe) {

            $_SESSION['error'] =
                'NF-e não encontrada.';

            $this->redirectToList();
        }


        /*
         * =====================================================
         * BLOQUEAR SE JÁ HOUVER LANÇAMENTO FINANCEIRO
         * =====================================================
         *
         * Esta é uma regra de negócio e não pode depender
         * apenas do botão estar oculto na View.
         *
         * Mesmo que alguém acesse manualmente:
         *
         * /uncheck-nfe/{id}
         *
         * a operação será recusada quando existir um
         * lançamento financeiro vinculado à NF-e.
         */
        $purchaseDocumentsRepository =
            new PurchaseDocumentsRepository();


        if (
            $purchaseDocumentsRepository
                ->existsByNfeId(
                    $nfeId
                )
        ) {

            $_SESSION['error'] =
                'Não é possível desfazer a conferência desta NF-e, '
                . 'pois ela já possui um lançamento financeiro.';

            $this->redirectToList();
        }


        /*
         * =====================================================
         * DESFAZER CONFERÊNCIA
         * =====================================================
         */
        $result =
            $nfeRepository->markAsUnchecked(
                $nfeId
            );


        if ($result) {

            $_SESSION['success'] =
                'Conferência da NF-e desfeita com sucesso.';

        } else {

            $_SESSION['error'] =
                'Não foi possível desfazer a conferência da NF-e.';
        }


        $this->redirectToList();
    }


    /**
     * Redirecionar para a listagem de NF-e.
     */
    private function redirectToList(): never
    {
        header(
            'Location: '
            . $_ENV['URL_ADM']
            . 'list-nfes'
        );

        exit;
    }
}
