<?php

namespace App\admsDaman\Controllers\groupsPages;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\GroupsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para visualizar um Grupo
 *
 * Esta classe é responsável por exibir as informações detalhadas de um Grupo específico. Ela recupera os dados
 * do Grupo a partir do repositório, valida se o Grupo existe e carrega a visualização apropriada. Se o Grupo
 * não for encontrado, uma mensagem de erro é exibida e o Grupo é redirecionado para a página de lista.
 *
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\groupsPages
 */
class ViewGroupPage
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes do Grupo.
     *
     * Este método gerencia a recuperação e exibição dos detalhes de um Grupo específico. Ele valida o ID fornecido,
     * recupera os dados do Grupo do repositório e carrega a visualização. Se o Grupo não for encontrado, registra
     * um erro, exibe uma mensagem e redireciona para a página de lista de Grupos.
     *
     * @param int|string $id ID do Grupo a ser visualizado.
     * 
     * @return void
     */
    public function index(int|string $id)
    {
        // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Grupo não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Grupo não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-groups-pages");

            return;
        }

        // Instanciar o Repository para recuperar o registro do banco de dados
        $viewGroup = new GroupsRepository();
        $this->data['group'] = $viewGroup->getGroup((int) $id);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['group']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Grupo não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Grupo não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-groups-pages");

            return;
        }

        $pageElements = [
            'title_head' => "Visualizar Grupo",
            'menu' => "list-groups-pages",
            'buttonPermissions' => ["ListGroupsPages", "UpdateGroupPage", "DeleteGroupPage"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/groupsPages/view", $this->data);
        $loadView->loadView();
    }
}
