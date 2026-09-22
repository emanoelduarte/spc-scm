<?php

namespace App\admsDaman\Controllers\nfes;

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

            $service = new NfeDistributionService();

            $result = $service->synchronize();


            // Consulta bloqueada pelo intervalo mínimo.
            if ($result['blocked'] ?? false) {

                $message = $result['message']
                    ?? 'Sincronização de NF-e indisponível.';

                $_SESSION['warning'] = $message;
            } elseif (($result['cStat'] ?? '') == '138') {

                $savedCount = (int) ($result['saved_count'] ?? 0);

                $_SESSION['success'] = "
                        
                        Sincronização realizada.
                        {$savedCount} NF-e(s) localizada(s) e processada(s).
                ";

                // Nenhum documento novo.
            } elseif (($result['cStat'] ?? '') == '137') {

                $_SESSION['info'] = "
                        Sincronização realizada.
                        Nenhuma nova NF-e encontrada.";
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
