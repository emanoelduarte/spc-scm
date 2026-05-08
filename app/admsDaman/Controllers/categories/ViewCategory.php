<?php

namespace App\admsDaman\Controllers\categories;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\CategoriesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para visualizar uma Categoria
 *
 * Esta classe é responsável por exibir as informações detalhadas de uma categoria específico. Ela recupera os dados
 * da categoria a partir do repositório, valida se a categoria existe e carrega a visualização apropriada. Se a categoria
 * não for encontrado, uma mensagem de erro é exibida e o usuário é redirecionado para a página de listar.
 *
 * @package App\admsDaman\Controllers\categories
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 */
class ViewCategory
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes da Categoria.
     *
     * Este método gerencia a recuperação e exibição dos detalhes de uma Categoria específico. Ele valida o ID fornecido,
     * recupera os dados da categoria do repositório e carrega a visualização. Se a categoria não for encontrado, registra
     * um erro, exibe uma mensagem e redireciona para a página de lista de categorias.
     *
     * @param int|string $id ID da categoria a ser visualizado.
     * 
     * @return void
     */
    public function index(int|string $id)
    {
        // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Categoria não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Categoria não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-categories");

            return;
        }

        // Instanciar o Repository para recuperar o registro do banco de dados
        $viewCategory = new CategoriesRepository();
        $this->data['category'] = $viewCategory->getCategory((int) $id);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['category']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Categoria não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Categoria não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-categories");

            return;
        }

        $pageElements = [
            'title_head' => "Visualizar Categoria",
            'menu' => "list-categories",
            'buttonPermissions' => ["ListCategories", "UpdateCategory", "DeleteCategory"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/categories/view", $this->data);
        $loadView->loadView();
    }
}
