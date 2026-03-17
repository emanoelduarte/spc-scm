<?php

namespace App\admsDaman\Controllers\pages;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\PagesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para visualizar um Page
 *
 * Esta classe é responsável por exibir as informações detalhadas de um Page específico. Ela recupera os dados
 * do Page a partir do repositório, valida se o Page existe e carrega a visualização apropriada. Se o Page
 * não for encontrado, uma mensagem de erro é exibida e o Page é redirecionado para a página de lista.
 *
 * @package App\admsDaman\Controllers\pages
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 */
class ViewPage
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes do Page.
     *
     * Este método gerencia a recuperação e exibição dos detalhes de um Page específico. Ele valida o ID fornecido,
     * recupera os dados do Page do repositório e carrega a visualização. Se o Page não for encontrado, registra
     * um erro, exibe uma mensagem e redireciona para a página de lista de Pages.
     *
     * @param int|string $id ID do Page a ser visualizado.
     * 
     * @return void
     */
    public function index(int|string $id)
    {
        // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pagina não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Pagina não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-pages");

            return;
        }

        // Instanciar o Repository para recuperar o registro do banco de dados
        $viewPage = new PagesRepository();
        $this->data['page'] = $viewPage->getPage((int) $id);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['page']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pagina não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Pagina não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-pages");

            return;
        }

        // Criar o título da página
        $this->data['title_head'] = "Visualizar Página";

        // Ativar o item de Menu
        $this->data['menu'] = "list-pages";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/pages/view", $this->data);
        $loadView->loadView();
    }
}
