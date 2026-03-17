<?php

namespace App\admsDaman\Controllers\pages;

use App\admsDaman\Controllers\Services\Validation\ValidationPageService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\GroupsRepository;
use App\admsDaman\Models\Repository\PackagesRepository;
use App\admsDaman\Models\Repository\PagesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para editar páginas
 *
 * Esta classe é responsável por gerenciar a edição de informações de uma página existente. Inclui a validação dos dados
 * do formulário, a atualização das informações da página no repositório e a renderização da visualização apropriada.
 * Caso haja algum problema, como uma página não encontrada ou dados inválidos, mensagens de erro são exibidas e registradas.
 *
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\pages;
 */
class UpdatePage
{
    /** @var array|string|null $data Dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Editar a página.
     *
     * Este método gerencia o processo de edição de uma página. Recebe os dados do formulário, valida o CSRF token e
     * a existência da página, e chama o método adequado para editar a página ou carregar a visualização de edição.
     *
     * @param int|string $id ID da página a ser editada.
     * 
     * @return void
     */
    public function index(int|string $id): void
    {
        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Validar o CSRF token e a existência do ID da página
        if (
            isset($this->data['form']['csrf_token']) &&
            CSRFHelper::validateCSRFToken('form_update_page', $this->data['form']['csrf_token'])
        ) {
            // Editar a página
            $this->editPage();
        } else {
            // Recuperar o registro da página
            $viewPage = new PagesRepository();
            $this->data['form'] = $viewPage->getPage((int) $id);

            // Verificar se a página foi encontrada
            if (!$this->data['form']) {
                // Registrar o erro e redirecionar
                GenerateLog::generateLog("error", "Página não encontrada", ['id' => (int) $id]);
                $_SESSION['error'] = "Página não encontrada!";
                header("Location: {$_ENV['URL_ADM']}list-pages");
                return;
            }

            // Carregar a visualização para edição da página
            $this->viewPage();
        }
    }

    /**
     * Carregar a visualização para edição da página.
     *
     * Este método define o título da página e carrega a visualização de edição da página com os dados necessários.
     * 
     * @return void
     */
    private function viewPage(): void
    {

        // Intanciar o repositório para recuperar os pacotes do banco para preencher o select
        $listPackagesPages = new PackagesRepository();
        $this->data['listPackagesPages'] = $listPackagesPages->getAllPackagesSelect();

        // Intanciar o repositório para recuperar os grupos do banco para preencher o select
        $getAllGroupsPagesSelect = new GroupsRepository();
        $this->data['getAllGroupsPagesSelect'] = $getAllGroupsPagesSelect->getAllGroupsPagesSelect();


        // Definir o título da página
        $this->data['title_head'] = "Editar Página";

        // Ativar o item de menu
        $this->data['menu'] = "list-pages";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/pages/update", $this->data);
        $loadView->loadView();
    }

    /**
     * Editar a página.
     *
     * Este método valida os dados do formulário, atualiza as informações da página no repositório e lida com o resultado
     * da operação. Se a atualização for bem-sucedida, a página é redirecionada para a visualização da página editada.
     * Caso contrário, uma mensagem de erro é exibida e a visualização de edição é recarregada.
     * 
     * @return void
     */
    private function editPage(): void
    {
        // var_dump($this->data['form']);
        // exit;
        // Validar os dados do formulário
        $validationPage = new ValidationPageService();
        $this->data['errors'] = $validationPage->validate($this->data['form']);

        // Se houver erros de validação, recarregar a visualização
        if (!empty($this->data['errors'])) {
            $this->viewPage();
            return;
        }

        // Atualizar a página
        $pageUpdate = new PagesRepository();
        $result = $pageUpdate->updatePage($this->data['form']);

        // Verificar o resultado da atualização
        if ($result) {
            $_SESSION['success'] = "Página editada com sucesso!";
            header("Location: {$_ENV['URL_ADM']}view-page/{$this->data['form']['id']}");
        } else {
            $this->data['errors'][] = "Página não editada!";
            $this->viewPage();
        }
    }
}
