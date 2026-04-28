<?php

namespace App\admsDaman\Controllers\accessLevels;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\AccessLevelsRepository;

/**
 * Controller responsável em Deletar o níveis de acesso.
 * 
 * Esta classe é responsável por recuperar um lista de níveis de acesso do banco de dados do sistema. 
 * Utiliza um repositório para obter esses dados.
 * Sem seguida Deleta os dados para o usuário ao chamar a visualização correspondente com os dados recuperados
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\accessLevels
 */
class DeleteAccessLevel
{

    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes do nível de acesso e processar a exclusão.
     *
     * Este método verifica a validade do token CSRF e a existência do ID do nível. Se válido, recupera os
     * detalhes do nível no banco de dados e tenta excluir o nível de acess. Redireciona o usuário para a página de 
     * listagem de níveis de acesso com mensagens apropriadas baseadas no sucesso ou falha da operação.
     * 
     * @return void
     */
    public function index(): void
    {
        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessar o IF se existir o CSRF e for valido o CSRF
        if (!isset($this->data['form']['csrf_token']) or !CSRFHelper::validateCSRFToken('form_delete_level', $this->data['form']['csrf_token']) or empty($this->data['form']['id'])) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Nível não encontrado", []);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Nível não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-access-levels");

            return;
        }

        // Instanciar o Repository para recuperar o registro do banco de dados para verificar se encontrou algum registro com o id enviado caso contrário ele retorna
        $deleteAccessLevel = new AccessLevelsRepository();
        $this->data['accessLevel'] = $deleteAccessLevel->getAccessLevel((int) $this->data['form']['id']);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['accessLevel']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Nível de acesso não encontrado", ['id' => (int) $this->data['form']['id']]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Nível de acesso não encontrado!";

            // Redirecionar o usuário para a página listar níveis de acesso
            header("Location: {$_ENV['URL_ADM']}list-access-levels");

            return;
        }

        // Instanciar o Repository para apagar o registro do banco de dados
        $result = $deleteAccessLevel->deleteAccessLevel($this->data['form']['id']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao apagar
            $_SESSION['success'] = "Nível de acesso apagado com sucesso!";

            // Redirecionar o usuário para a página de listar níveis de acesso
            header("Location: {$_ENV['URL_ADM']}list-access-levels");

            return;
        } else {
            // Criar a mensagem de erro ao tentar apagar
            $_SESSION['error'] = "Nível de acesso não apagado! 22";

            // Redirecionar o usuário para a página de listar níveis de acesso
            header("Location: {$_ENV['URL_ADM']}list-access-levels");
        }
    }
}