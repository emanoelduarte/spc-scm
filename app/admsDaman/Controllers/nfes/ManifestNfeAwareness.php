<?php

namespace App\admsDaman\Controllers\nfes;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\NfeRepository;
use App\admsDaman\Models\Services\NfeDistributionService;
use Throwable;

/**
 * Registrar Ciência da Emissão
 * de uma NF-e específica.
 */
class ManifestNfeAwareness
{
    /**
     * Processar manifestação.
     */
    public function index(): void
    {
        try {

            /*
             * Manifestação é uma ação fiscal:
             * aceitar somente POST.
             */
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

                $_SESSION['error'] =
                    'Método inválido para manifestação da NF-e.';

                $this->redirect();
            }


            $form =
                filter_input_array(
                    INPUT_POST,
                    FILTER_UNSAFE_RAW
                ) ?? [];


            /*
             * Validar CSRF.
             */
            if (
                empty($form['csrf_token'])
                ||
                !CSRFHelper::validateCSRFToken(
                    'form_manifest_nfe_awareness',
                    $form['csrf_token']
                )
            ) {

                $_SESSION['error'] =
                    'Token de segurança inválido ou expirado.';

                $this->redirect();
            }


            $nfeId =
                (int) (
                    $form['nfe_id']
                    ?? 0
                );


            if ($nfeId <= 0) {

                $_SESSION['error'] =
                    'NF-e inválida.';

                $this->redirect();
            }


            $nfeRepository =
                new NfeRepository();


            $nfe =
                $nfeRepository
                ->getNfeById(
                    $nfeId
                );


            if (!$nfe) {

                $_SESSION['error'] =
                    'NF-e não encontrada.';

                $this->redirect();
            }


            if (
                (int) (
                    $nfe['has_xml']
                    ?? 0
                ) === 1
            ) {

                $_SESSION['info'] =
                    'O XML completo desta NF-e já está disponível.';

                $this->redirect();
            }


            if (
                ($nfe['status'] ?? '')
                !== 'authorized'
            ) {

                $_SESSION['warning'] =
                    'A Ciência da Emissão não será registrada porque a NF-e não está autorizada.';

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


            $service =
                new NfeDistributionService();


            /*
             * Registrar Ciência da Emissão.
             */
            $manifest =
                $service
                ->manifestAwareness(
                    $accessKey
                );


            if (
                !($manifest['registered'] ?? false)
            ) {

                $cStat =
                    $manifest['cStat']
                    ?? '-';


                $message =
                    $manifest['message']
                    ?? 'Retorno não informado pela SEFAZ.';


                $_SESSION['error'] =
                    "Não foi possível registrar a Ciência da Emissão. "
                    . "SEFAZ: {$cStat} - {$message}";

                /*
                * Registrar localmente a manifestação aceita
                * pela SEFAZ.
                */
                $savedManifestation =
                    $nfeRepository
                    ->saveManifestation(
                        $nfeId,
                        [
                            'event_type' =>
                            '210210',

                            'status' =>
                            'registered',

                            'cstat' =>
                            $manifest['cStat']
                                ?? null,

                            'message' =>
                            $manifest['message']
                                ?? null,

                            'protocol' =>
                            $manifest['protocol']
                                ?? null,

                            'registered_at' =>
                            $manifest['registered_at']
                                ?? null,
                        ]
                    );


                if (!$savedManifestation) {

                    throw new \RuntimeException(
                        'Não foi possível salvar a manifestação da NF-e.'
                    );
                }

                $this->redirect();
            }


            /*
             * Após a Ciência, fazer UMA única consulta
             * pontual pela chave de acesso.
             *
             * Não há loop nem repetição automática.
             */
            $download =
                $service
                ->downloadByAccessKey(
                    $accessKey
                );


            if (
                $download['blocked']
                ?? false
            ) {

                $_SESSION['warning'] =
                    'Ciência da Emissão registrada, porém a SEFAZ '
                    . 'informou consumo indevido (cStat 656). '
                    . 'Não faça novas consultas por pelo menos 1 hora.';

                $this->redirect();
            }


            $updatedNfe =
                $nfeRepository
                ->getNfeById(
                    $nfeId
                );


            if (
                $updatedNfe
                &&
                (int) (
                    $updatedNfe['has_xml']
                    ?? 0
                ) === 1
            ) {

                $_SESSION['success'] =
                    'Ciência da Emissão registrada e XML completo obtido com sucesso.';

                $this->redirect();
            }


            $downloadCStat =
                $download['cStat']
                ?? null;


            $downloadMessage =
                $download['message']
                ?? 'XML completo ainda não disponibilizado pela SEFAZ.';


            $prefix =
                ($manifest['already_registered'] ?? false)
                ? 'A Ciência da Emissão já estava registrada.'
                : 'Ciência da Emissão registrada com sucesso.';


            $_SESSION['warning'] =
                $prefix
                . ' O XML completo ainda não foi obtido.'
                . (
                    $downloadCStat
                    ? " SEFAZ: {$downloadCStat} - {$downloadMessage}"
                    : ''
                );
        } catch (Throwable $err) {

            $_SESSION['error'] =
                'Não foi possível concluir a manifestação da NF-e.';
        }


        $this->redirect();
    }


    /**
     * Voltar para a listagem.
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
