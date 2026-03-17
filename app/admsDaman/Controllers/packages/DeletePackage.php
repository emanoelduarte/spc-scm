<?php

namespace App\admsDaman\Controllers\packages;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\PackagesRepository;

/**
 * Controller para exclusão de Pacote
 *
 * Esta classe gerencia o processo de exclusão de Pacotes no sistema. Ela lida com a validação dos dados
 * do formulário, a exclusão do Pacote do banco de dados e o registro de logs para operações bem-sucedidas ou
 * falhas. Além disso, redireciona o Pacote para a página de listagem de Pacotes com mensagens de sucesso ou erro.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * 
 * @package App\adms\Controllers\users
 */
class DeletePackage
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes do Pacote e processar a exclusão.
     *
     * Este método verifica a validade do token CSRF e a existência do ID do Pacote. Se válido, recupera os
     * detalhes do Pacote do banco de dados e tenta excluir o Pacote. Redireciona o Pacote para a página de 
     * listagem de Pacotes com mensagens apropriadas baseadas no sucesso ou falha da operação.
     * 
     * @return void
     */
    public function index(): void
    {
        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessar o IF se existir o CSRF e for valido o CSRF
        if (!isset($this->data['form']['csrf_token']) or !CSRFHelper::validateCSRFToken('form_delete_package', $this->data['form']['csrf_token']) or empty($this->data['form']['id'])) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pacote não encontrado", []);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Pacote não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-packages");

            return;
        }

        // Instanciar o Repository para recuperar o registro do banco de dados
        $deletePackage = new PackagesRepository();
        $this->data['package'] = $deletePackage->getPackage((int) $this->data['form']['id']);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['package']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pacote não encontrado", ['id' => (int) $this->data['form']['id']]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Pacote não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-packages");

            return;
        }

        // Instanciar o Repository para apagar o registro do banco de dados
        $result = $deletePackage->deletePackage($this->data['form']['id']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao apagar
            $_SESSION['success'] = "Pacote apagado com sucesso!";

            // Redirecionar o usuário para a página de listar
            header("Location: {$_ENV['URL_ADM']}list-packages");

            return;
        } else {
            // Criar a mensagem de erro ao tentar apagar
            $_SESSION['error'] = "Pacote não apagado!";

            // Redirecionar o usuário para a página de listar
            header("Location: {$_ENV['URL_ADM']}list-packages");
        }
    }
}
?>