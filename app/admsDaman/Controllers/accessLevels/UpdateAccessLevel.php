<?php

namespace App\admsDaman\Controllers\accessLevels;

use App\admsDaman\Controllers\Services\Validation\ValidationAccessLevelService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\AccessLevelsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para editar o nível de acesso
 *
 * Esta classe é responsável por gerenciar a edição de informações de um nível de acesso existente. Inclui a validação dos dados
 * do formulário, a atualização das informações do nível de acesso no repositório e a renderização da visualização apropriada.
 * Caso haja algum problema, como um nível de acesso não encontrado ou dados inválidos, mensagens de erro são exibidas e registradas.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaaman\Controllers\accessLevels;
 */
class UpdateAccessLevel
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Editar o nível de acesso.
     *
     * Este método gerencia o processo de edição de um nível de acesso. Recebe os dados do formulário, valida o CSRF token e
     * a existência do nível de acesso, e chama o método adequado para editar o nível de acesso ou carregar a visualização de edição.
     *
     * @param int|string $id ID do nível de acesso a ser editado.
     * 
     * @return void
     */
    public function index(int|string $id): void
    {
        // Receber os dados do formulário de cadastro de usuário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_update_level', $this->data['form']['csrf_token'])) {

            // Chamar o método Editar o usuário
            // var_dump($this->data['form']);

            $this->editAccessLevel();
        } else {
            // Instanciar o Repository para recuperar o registro do banco de dados
            $viewUser = new AccessLevelsRepository();
            $this->data['form'] = $viewUser->getAccessLevel((int) $id);

            // Verificar se encontrou o registro no banco de dados
            if (!$this->data['form']) {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Nível de Acesso não encontrado", ['id' => (int) $id]);

                // Criar a mensagem de erro
                $_SESSION['error'] = "Nível de Acesso não encontrado!";

                // Redirecionar o usuário para a página listar
                header("Location: {$_ENV['URL_ADM']}list-access-levels");

                return;
            }


            $this->viewAccessLevel();
        }

        // Acessa o IF se existir o CSRF e for válido o CSRF
    }

    /**
     * Instanciar a classe responsável em carregar a VIEW e enviar os dados para View.
     * 
     * @return void
     */
    private function viewAccessLevel(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Editar Nível de Acesso";

        $this->data['menu'] = "list-access-level";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/accessLevels/update", $this->data);
        $loadView->loadView();
    }

    /**
     * Editar Nível de Acesso
     * 
     * Este método realiza a edição de um Nível de Acesso existente no sistema. Ele valida os dados do formulário usando a
     * Classe `ValidationAccessLevelService`, exibe a view com os erros caso existam campos compdados incorretos,
     * Chama o repositório para atualizar o Nível de Acesso e, depedendo do resultado, redireciona o Nível de Acesso ou exibe
     * uma mensagem de erro
     * 
     * @return void
     */
    private function editAccessLevel(): void
    {
        // Instanciar a classe que valida os dados do formulário com Rakit
        $validationLevel = new ValidationAccessLevelService();
        $this->data['errors'] = $validationLevel->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados inclorretos
        if (!empty($this->data['errors'])) {

            // Chama o método carregar a view
            $this->viewAccessLevel();

            return;
        }

        // Instanciar o UsersRepository para chamar o método que faz a edição do Nível de acesso
        $levelUpdate = new AccessLevelsRepository();
        $result = $levelUpdate->updateAccessLevel($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar
            $_SESSION['success'] = "Nível de acesso editado com sucesso!";

            // Redirecionar o Nível de acesso para a página de visualizar Nível de acesso
            header("Location: {$_ENV['URL_ADM']}view-access-level/{$this->data['form']['id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar
            $this->data['errors'][] = "Nível de acesso não editado!";

            // Chamar o método carregar a view
            $this->viewAccessLevel();
        }
    }
}