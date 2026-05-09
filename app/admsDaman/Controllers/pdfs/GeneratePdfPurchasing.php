<?php

namespace App\admsDaman\Controllers\pdfs;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Helpers\GeneratePdfHelper;
use App\admsDaman\Models\Repository\GeneratePdfPurchasingRepository;

class GeneratePdfPurchasing
{
    /** 
     * Este método é responsável por gerar um PDF com os detalhes de uma compra específica. Ele utiliza a biblioteca Dompdf para criar o documento PDF, formatar o conteúdo e enviar o arquivo para download. O método recupera os dados da compra, formata as informações e as organiza em um layout legível antes de gerar o PDF.
    
     * @return void
     **/
    public function index(string|int $id): void
    {
        // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Compra não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-purchasings");

            return;
        }

        // Instanciar o Repository para recuperar os registros do banco de dados
        $viewPurchasing = new GeneratePdfPurchasingRepository();
        $purchasing = $viewPurchasing->getPurchasing((int) $id);

        // Verificar se encontrou o registro no banco de dados
        if (!$purchasing) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Compra não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-purchasings");

            return;
        }

        $this->generatePdf($purchasing, $purchasing['items']);
    }


    private function generatePdf($data, $items): bool
    {
        $dataPdf  = $data;
        $itemsPdf = $items;

        ob_start();
        require __DIR__ . '/../../Views/pdfs/viewpdf.php';
        $html_data = ob_get_clean();

        // Monta o nome do arquivo aqui
        $fileName = 'ORDEM_DE_COMPRA-' . $data['id'] . '-OS' . $data['project_id'] . '-' . date('d-m-Y');

        $generatePdf = new GeneratePdfHelper();
        $generatePdf->generatePdfHelper($html_data, $fileName);

        return true;
    }
}
