<?php

namespace App\admsDaman\Controllers\nfes;

use App\admsDaman\Models\Repository\NfeRepository;
use App\admsDaman\Models\Services\NfeDistributionService;
use Throwable;

/**
 * Controller responsável por solicitar
 * a sincronização das NF-e com a SEFAZ.
 */
class SyncNfes
{
    /**
     * Executar sincronização manual das NF-e.
     *
     * @return void
     */
    public function index(): void
    {
        try {

            /*
             * Contar as NF-e existentes antes da sincronização.
             *
             * O serviço pode processar resNFe, procNFe e eventos.
             * Para a interface, porém, queremos avisar apenas
             * quando uma nova NF-e for efetivamente cadastrada.
             */
            $nfeRepository = new NfeRepository();

            $nfeCountBefore =
                $nfeRepository->countNfes();


            $service = new NfeDistributionService();

            $result = $service->synchronize();


            // Consulta bloqueada pelo intervalo mínimo.
            if ($result['blocked'] ?? false) {

                $message = $result['message']
                    ?? 'Sincronização de NF-e indisponível.';

                $_SESSION['warning'] = $message;
            } elseif (($result['cStat'] ?? '') == '138') {

                /*
                 * Comparar a quantidade antes/depois.
                 *
                 * procNFe e eventos podem ser processados sem
                 * significar que existe uma NF-e nova na listagem.
                 */
                $nfeCountAfter =
                    $nfeRepository->countNfes();


                $newNfesCount =
                    max(
                        0,
                        $nfeCountAfter - $nfeCountBefore
                    );


                if ($newNfesCount > 0) {

                    $_SESSION['success'] =
                        $newNfesCount === 1
                            ? '1 nova NF-e recebida.'
                            : "{$newNfesCount} novas NF-e recebidas.";

                } else {

                    $_SESSION['info'] =
                        'Sincronização concluída. Nenhuma nova NF-e encontrada.';
                }

                // Nenhum documento novo.
            } elseif (($result['cStat'] ?? '') == '137') {

                $_SESSION['info'] =
                    'Sincronização concluída. Nenhuma nova NF-e encontrada.';
            } else {

                $_SESSION['error'] = "Não foi possível concluir a sincronização com a SEFAZ.";
            }
        } catch (Throwable $err) {

            $_SESSION['error'] = "Não foi possível concluir a sincronização com a SEFAZ.";
        }


        header(
            'Location: ' . $_ENV['URL_ADM'] . 'list-nfes'
        );

        exit;
    }
}
