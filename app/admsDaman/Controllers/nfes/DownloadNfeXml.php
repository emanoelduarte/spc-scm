<?php

namespace App\admsDaman\Controllers\nfes;

use App\admsDaman\Models\Repository\NfeRepository;
use Throwable;

/**
 * Controller responsável pelo download
 * do XML completo da NF-e.
 */
class DownloadNfeXml
{
    /**
     * Baixar XML da NF-e.
     *
     * @param string|int $id
     * @return void
     */
    public function index(string|int $id): void
    {
        try {

            /*
             * Validar ID.
             */
            $nfeId = (int) $id;

            if ($nfeId <= 0) {

                $_SESSION['error'] =
                    'NF-e inválida.';

                $this->redirect();
            }


            /*
             * Recuperar XML completo.
             */
            $nfeRepository =
                new NfeRepository();


            $nfe =
                $nfeRepository
                    ->getNfeXmlById(
                        $nfeId
                    );


            /*
             * Verificar se a NF-e existe.
             */
            if (!$nfe) {

                $_SESSION['error'] =
                    'NF-e não encontrada.';

                $this->redirect();
            }


            $xml =
                (string) (
                    $nfe['xml_content']
                    ?? ''
                );


            /*
             * Não existe XML para download.
             */
            if (trim($xml) === '') {

                $_SESSION['warning'] =
                    'O XML completo desta NF-e ainda não está disponível.';

                $this->redirect();
            }


            /*
             * Montar nome seguro do arquivo.
             */
            $nfeNumber =
                preg_replace(
                    '/[^0-9]/',
                    '',
                    (string) (
                        $nfe['nfe_number']
                        ?? $nfeId
                    )
                );


            $accessKey =
                preg_replace(
                    '/[^0-9]/',
                    '',
                    (string) (
                        $nfe['access_key']
                        ?? ''
                    )
                );


            if ($nfeNumber === '') {
                $nfeNumber = (string) $nfeId;
            }


            $fileName =
                'NFe-'
                . $nfeNumber;


            /*
             * Acrescentar chave de acesso quando disponível.
             */
            if (strlen($accessKey) === 44) {

                $fileName .=
                    '-'
                    . $accessKey;
            }


            $fileName .= '.xml';


            /*
             * Limpar qualquer saída anterior.
             */
            while (ob_get_level() > 0) {
                ob_end_clean();
            }


            /*
             * Forçar download do XML original.
             */
            header(
                'Content-Type: application/xml; charset=UTF-8'
            );

            header(
                'Content-Disposition: attachment; filename="'
                . $fileName
                . '"'
            );

            header(
                'Content-Length: '
                . strlen($xml)
            );

            header(
                'X-Content-Type-Options: nosniff'
            );


            echo $xml;

            exit;

        } catch (Throwable $err) {

            $_SESSION['error'] =
                'Não foi possível baixar o XML desta NF-e.';

            $this->redirect();
        }
    }


    /**
     * Retornar para a listagem de NF-e.
     *
     * @return never
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