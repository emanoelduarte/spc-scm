<?php

namespace App\admsDaman\Controllers\permission;

use App\admsDaman\Controllers\Services\Validation\ValidationAccessLevelPermissionService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\AccessLevelsPagesRepository;
use App\admsDaman\Models\Repository\AccessLevelsRepository;
use App\admsDaman\Models\Repository\PagesRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ListAccessLevelsPermissions
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    /**
     * @var int $id ID do nível de acesso
     */
    private int $id;

    /**
     * Recuperar os ultimos usuários
     * 
     * @return void
     */
    public function index(string|int $id): void
    {
        $this->id = $id;

        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o if se existir o CSRF e for válido
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_update_access_level_permissions', $this->data['form']['csrf_token'])) {
            // Chamar método para editar permissões do nível de acesso
            $this->editAccessLevelPermissions();
        } else {
            // Chamar o método para carregar a view de criação de nível de acesso
            $this->viewAccessLevelPermissions();
        }
    }

    /**
     * Carregar a visualização de criação de usuário.
     * 
     * Este método configura os dados necessários e carrega a view para a criação de um novo usuário.
     * 
     * @return void
     */
    private function viewAccessLevelPermissions(): void
    {
        // Recuperar o registro do nível de acceso
        $viewAccessLevel = new AccessLevelsRepository();
        $this->data['accessLevel'] = $viewAccessLevel->getAccessLevel($this->id);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!$this->data['accessLevel']) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Nível de Acesso não encontrado", ['id' => (int) $this->id]);
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['error'] = "Nível de Acesso não encontrado";

            // Redirecionar o Nivel de acesso para a página de listar Nivel de acesso
            header("Location: {$_ENV['URL_ADM']}list-access-levels");

            return;
        }

        // Recuperar as páginas associadas ao nível de acesso
        $listPages = new PagesRepository();
        $this->data['pages'] = $listPages->getAllPagesFull();

        // Recuperar as permissões do nível de acesso para as páginas
        $listAccessLevelsPages = new AccessLevelsPagesRepository();
        $this->data['accessLevelsPages'] = $listAccessLevelsPages->getPagesAccessLevelsArray($this->id, true);

        // Criar o título da página
        $this->data['title_head'] = "Editar Permissão do Nível de Acesso";

        $this->data['menu'] = "list-access-levels";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/permission/list", $this->data);
        $loadView->loadView();
    }

    private function editAccessLevelPermissions(): void
    {
        // Validar os dados do formulário
        $validationAccessLevelPermissions = new ValidationAccessLevelPermissionService();
        $this->data['errors'] = $validationAccessLevelPermissions->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewAccessLevelPermissions();
            return;
        }

        // Atualizar permissões do nível de acesso
        // Atualizar as permissões do nível de acesso
        $accessLevelPagesUpdate = new AccessLevelsPagesRepository();
        $result = $accessLevelPagesUpdate->updateAccessLevelPages($this->data['form']);

        // Verificar o resultado da atualização
        if ($result) {
            $_SESSION['success'] = "Permissões do nível de acesso editadas com sucesso!";
            header("Location: {$_ENV['URL_ADM']}list-access-levels-permissions/{$this->data['form']['adms_daman_access_level_id']}");
        } else {
            $this->data['errors'][] = "Permissões do nível de acesso não editado!";
            $this->viewAccessLevelPermissions();
        }
    }
}
