<?php

namespace App\admsDaman\Controllers\purchasing;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\PurchasingQuoteRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ViewPurchasingQuote
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    public function index(string|int $id)
    {
        // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra pendente não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Compra pendente não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-purchasing-quotes");

            return;
        }

        // Instanciar o Repository para recuperar os registros do banco de dados
        $viewQuotePurchasing = new PurchasingQuoteRepository();
        $this->data['purchasingQuotes'] = $viewQuotePurchasing->getPurchasingQuote((int) $id);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['purchasingQuotes']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra pendente não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Compra pendente não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-purchasing-quotes");

            return;
        }

        // Instanciar o Repository para recuperar os registros do banco de dados
        $viewItemsQuotePurchasing = new PurchasingQuoteRepository();
        $this->data['itemsPurchasingQuotes'] = $viewItemsQuotePurchasing->getItemsQuote((int) $id);

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Visualizar Compra Pendente",
            'menu' => "list-purchasings",
            'buttonPermissions' => ["ApprovePurchasingQuote", "RejectPurchasingQuote", "ListPurchasingQuotes"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/purchasing/viewQuotes", $this->data);
        $loadView->loadView();
    }
}
