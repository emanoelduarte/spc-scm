<?php

namespace App\admsDaman\Controllers\projects;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\ProjectsRepository;

/**
 * Controller para exclusão de Obra
 *
 * Esta classe gerencia o processo de exclusão de Obras no sistema. Ela lida com a validação dos dados
 * do formulário, a exclusão da Obra do banco de dados e o registro de logs para operações bem-sucedidas ou
 * falhas. Além disso, redireciona o Usuário para a página de listagem de Obras com mensagens de sucesso ou erro.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * 
 * @package App\adms\Controllers\projects
 */
class DeleteProject
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes da Obra e processar a exclusão.
     *
     * Este método verifica a validade do token CSRF e a existência do ID do Obra. Se válido, recupera os
     * detalhes da Obra do banco de dados e tenta excluir a Obra. Redireciona o Usuário para a página de 
     * listagem de Obras com mensagens apropriadas baseadas no sucesso ou falha da operação.
     * 
     * @return void
     */
    public function index(): void
    {
        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessar o IF se existir o CSRF e for valido o CSRF
        if (!isset($this->data['form']['csrf_token']) or !CSRFHelper::validateCSRFToken('form_delete_project', $this->data['form']['csrf_token']) or empty($this->data['form']['id'])) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Obra não encontrada", []);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Obra não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-projects");

            return;
        }

        // Instanciar o Repository para recuperar o registro do banco de dados
        $deleteProject = new ProjectsRepository();
        $this->data['project'] = $deleteProject->getProject((int) $this->data['form']['id']);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['project']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Obra não encontrada", ['id' => (int) $this->data['form']['id']]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Obra não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-projects");

            return;
        }

        // Instanciar o Repository para apagar o registro do banco de dados
        $result = $deleteProject->deleteProject($this->data['form']['id']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao apagar
            $_SESSION['success'] = "Obra apagada com sucesso!";

            // Redirecionar o usuário para a página de listar
            header("Location: {$_ENV['URL_ADM']}list-projects");

            return;
        } else {
            // Criar a mensagem de erro ao tentar apagar
            $_SESSION['error'] = "Obra não apagada!";

            // Redirecionar o usuário para a página de listar
            header("Location: {$_ENV['URL_ADM']}list-projects");
        }
    }
}
?>