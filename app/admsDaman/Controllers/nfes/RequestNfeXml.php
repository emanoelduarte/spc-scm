<?php

namespace App\admsDaman\Controllers\nfes;

use App\admsDaman\Models\Repository\NfeRepository;
use App\admsDaman\Models\Services\NfeDistributionService;
use Throwable;

/**
 * Controller responsável por tentar recuperar
 * o XML completo de uma NF-e específica.
 */
class RequestNfeXml
{
    /**
     * Solicitar XML completo da NF-e.
     *
     * Consulta pontual por chave de acesso.
     *
     * @param string|int $id
     * @return void
     */
    public function index(string|int $id): void
    {
        try {

            $id = (int) $id;

            if ($id <= 0) {

                $_SESSION['error'] =
                    'NF-e inválida.';

                $this->redirect();
            }


            $nfeRepository =
                new NfeRepository();


            $nfe =
                $nfeRepository
                    ->getNfeById($id);


            if (!$nfe) {

                $_SESSION['error'] =
                    'NF-e não encontrada.';

                $this->redirect();
            }


            /*
             * Não consultar novamente se o XML
             * já estiver salvo localmente.
             */
            if (
                (int) ($nfe['has_xml'] ?? 0)
                === 1
            ) {

                $_SESSION['info'] =
                    'O XML desta NF-e já está disponível.';

                $this->redirect();
            }


            $accessKey =
                (string) (
                    $nfe['access_key']
                    ?? ''
                );


            if (
                !preg_match(
                    '/^\d{44}$/',
                    $accessKey
                )
            ) {

                $_SESSION['error'] =
                    'A chave de acesso da NF-e é inválida.';

                $this->redirect();
            }


            /*
             * Consulta pontual via consChNFe.
             *
             * Não existe loop nem tentativa automática.
             */
            $service =
                new NfeDistributionService();


            $download =
                $service
                    ->downloadByAccessKey(
                        $accessKey
                    );


            /*
             * Consumo indevido informado pela SEFAZ.
             * Não tentar novamente automaticamente.
             */
            if (
                $download['blocked']
                ?? false
            ) {

                $_SESSION['error'] =
                    'A SEFAZ bloqueou temporariamente as consultas '
                    . 'por consumo indevido (cStat 656). '
                    . 'Não faça novas consultas por pelo menos 1 hora.';

                $this->redirect();
            }


            /*
             * Confirmar pelo banco se o XML foi
             * efetivamente persistido.
             */
            $updatedNfe =
                $nfeRepository
                    ->getNfeById($id);


            if (
                $updatedNfe
                &&
                (int) (
                    $updatedNfe['has_xml']
                    ?? 0
                ) === 1
            ) {

                $_SESSION['success'] =
                    'XML completo da NF-e obtido com sucesso.';

                $this->redirect();
            }


            $cStat =
                $download['cStat']
                ?? null;


            $message =
                $download['message']
                ?? 'XML completo ainda não disponibilizado pela SEFAZ.';


            $_SESSION['warning'] =
                $cStat
                    ? "NF-e consultada. SEFAZ: {$cStat} - {$message}"
                    : $message;

        } catch (Throwable $err) {

            $_SESSION['error'] =
                'Não foi possível solicitar o XML da NF-e.';
        }


        $this->redirect();
    }


    /**
     * Redirecionar para a listagem.
     */
    private function redirect(): never
    {
        header(
            'Location: '
            . $_ENV['URL_ADM']
            . 'list-nfes'
        );

        exit;
    }
}
