<?php

namespace App\admsDaman\Controllers\nfes;

use App\admsDaman\Models\Repository\NfeRepository;
use NFePHP\DA\NFe\Danfe;
use Throwable;

/**
 * Controller responsável por gerar e visualizar
 * o DANFE de uma NF-e cujo XML completo já
 * esteja armazenado no sistema.
 */
class ViewNfeDanfe
{
    /**
     * Gerar e exibir o DANFE em PDF.
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
             * Recuperar somente os dados relacionados
             * ao XML da NF-e.
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


            /*
             * Recuperar XML completo.
             */
            $xml =
                (string) (
                    $nfe['xml_content']
                    ?? ''
                );


            /*
             * Sem procNFe não podemos gerar DANFE.
             */
            if (trim($xml) === '') {

                $_SESSION['warning'] =
                    'O XML completo desta NF-e ainda não está disponível.';

                $this->redirect();
            }


            /*
             * Gerar DANFE a partir do XML completo.
             */
            $danfe =
                new Danfe(
                    $xml
                );


            /*
             * Orientação:
             * P  = Retrato
             * A4 = Papel A4
             * 2  = Margem esquerda
             * 2  = Margem superior
             */
            $danfe->printParameters(
                'P',
                'A4',
                2,
                2
            );


            /*
             * Renderizar PDF em memória.
             */
            $pdf =
                $danfe->render();


            /*
             * Limpar qualquer saída anterior.
             *
             * Isso evita corromper o PDF caso exista
             * espaço, warning ou conteúdo no buffer.
             */
            while (ob_get_level() > 0) {
                ob_end_clean();
            }


            /*
             * Nome do arquivo.
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


            if ($nfeNumber === '') {
                $nfeNumber = (string) $nfeId;
            }


            $fileName =
                'DANFE-NFe-'
                . $nfeNumber
                . '.pdf';


            /*
             * Exibir PDF diretamente no navegador.
             */
            header(
                'Content-Type: application/pdf'
            );

            header(
                'Content-Disposition: inline; filename="'
                . $fileName
                . '"'
            );

            header(
                'Content-Length: '
                . strlen($pdf)
            );


            echo $pdf;

            exit;

        } catch (Throwable $err) {

            /*
             * Por enquanto não expor detalhes técnicos
             * da exceção ao usuário.
             */
            $_SESSION['error'] =
                'Não foi possível gerar o DANFE desta NF-e.';

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