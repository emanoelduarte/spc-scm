<?php

namespace App\admsDaman\Helpers;

// reference the Dompdf namespace
use Dompdf\Dompdf;

/**
 * Classe genérica para gerar PDF
 */
class GeneratePdfHelper
{
    /** @var array|string|null $dados Recebe os dados que devem ser incluidos no PDF */
    private string|null $data = null;

    /**
     * Método recebe o conteúdo para o PDF
     * @param string|null $data Dados a serem incluídos no PDF
     * @return void
     */
    public function generatePdfHelper(string|null $data): void
    {
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml($data);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }
}
