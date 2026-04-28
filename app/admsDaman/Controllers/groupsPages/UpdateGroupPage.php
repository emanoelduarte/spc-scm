<?php

namespace App\admsDaman\Controllers\groupsPages;

use App\admsDaman\Controllers\Services\Validation\ValidationGroupService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\GroupsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para editar grupo
 *
 * Esta classe é responsável por gerenciar a edição de informações de um grupo existente. Inclui a validação dos dados
 * do formulário, a atualização das informações do grupo no repositório e a renderização da visualização apropriada.
 * Caso haja algum problema, como um grupo não encontrado ou dados inválidos, mensagens de erro são exibidas e registradas.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\groupsPages;
 */
class UpdateGroupPage
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Editar o Grupo.
     *
     * Este método gerencia o processo de edição de um Grupo. Recebe os dados do formulário, valida o CSRF token e
     * a existência do Grupo, e chama o método adequado para editar o Grupo ou carregar a visualização de edição.
     *
     * @param int|string $id ID do Grupo a ser editado.
     * 
     * @return void
     */
    public function index(int|string $id): void
    {
        // Receber os dados do formulário de cadastro de Grupo
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_update_group', $this->data['form']['csrf_token'])) {

            // Chamar o método Editar o Grupo
            $this->editGroup();
        } else {
            // Instanciar o Repository para recuperar o registro do banco de dados
            $viewGroup = new GroupsRepository();
            $this->data['form'] = $viewGroup->getGroup((int) $id);

            // Verificar se encontrou o registro no banco de dados
            if (!$this->data['form']) {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Grupo não encontrado", ['id' => (int) $id]);

                // Criar a mensagem de erro
                $_SESSION['error'] = "Grupo não encontrado!";

                // Redirecionar o usuário para a página listar
                header("Location: {$_ENV['URL_ADM']}list-groups-pages");

                return;
            }

            $this->viewUpdateGroup();
        }
    }

    /**
     * Instanciar a classe responsável em carregar a VIEW e enviar os dados para View.
     * 
     * @return void
     */
    private function viewUpdateGroup(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Editar Grupo";

        // Ativar o item de Menu
        $this->data['menu'] = "list-groups-pages";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/groupsPages/update", $this->data);
        $loadView->loadView();
    }

    /**
     * Editar Grupo
     * 
     * Este método realiza a edição de um Grupo existente no sistema. Ele valida os dados do formulário usando a
     * Classe `ValidationGroupService`, exibe a view com os erros caso existam campos compdados incorretos,
     * Chama o repositório para atualizar o Grupo e, depedendo do resultado, redireciona o Grupo ou exibe
     * uma mensagem de erro
     * 
     * @return void
     */
    private function editGroup(): void
    {
        // Instanciar a classe validar os dados do formulário com Rakit
        $validationGroup = new ValidationGroupService();
        $this->data['errors'] = $validationGroup->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados inclorretos
        if (!empty($this->data['errors'])) {

            // Chama o método carregar a view
            $this->viewUpdateGroup();

            return;
        }

        // Instanciar o GroupRepository para chamar o método que faz a edição do Grupo
        $groupUpdate = new GroupsRepository();
        $result = $groupUpdate->updateGroup($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar
            $_SESSION['success'] = "Grupo editado com sucesso!";

            // Redirecionar o usuário para a página de visualizar Grupo
            header("Location: {$_ENV['URL_ADM']}view-group-page/{$this->data['form']['id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar
            $this->data['errors'][] = "Grupo não editado!";

            // Chamar o método carregar a view
            $this->viewUpdateGroup();
        }
    }
}
