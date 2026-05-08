<?php

namespace App\admsDaman\Controllers\suppliers;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\SuppliersRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller responsável por exibir os detalhes do fornecedor
 */
class ViewSupplier
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /**
     * Recupera os dados do fornecedor e chama a View para exibir os detalhes do fornecedor
     * 
     * @return void
     */
    public function index(int|string $id): void
    {

        // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Fornecedor não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Fornecedor não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-suppliers");

            return;
        }

        // Instanciar o Repository para recuperar os registros do banco de dados
        $viewSupplier = new SuppliersRepository();
        $this->data['supplier'] = $viewSupplier->getSupplier((int) $id);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['supplier']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Fornecedor não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Dornecedor não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-users");

            return;
        }

        // Chamar o método para salvar o log
        GenerateLog::generateLog("error", "Visualizar o Fornecedor", ['id' => (int) $id]);

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Visualizar Fornecedor",
            'menu' => "list-suppliers",
            'buttonPermissions' => ["ListSuppliers", "UpdateSupplier", "DeleteSupplier"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Criar o título da página
        $this->data['title_head'] = "Visualizar Fornecedor";

        // Ativar o item de Menu
        $this->data['menu'] = "list-suppliers";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/suppliers/view", $this->data);
        $loadView->loadView();
    }
}