<?php

namespace App\admsDaman\Controllers\categories;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\CategoriesRepository;

/**
 * Controller para exclusão de Categorias
 *
 * Esta classe gerencia o processo de exclusão de categorias no sistema. Ela lida com a validação dos dados
 * do formulário, a exclusão da categoria do banco de dados e o registro de logs para operações bem-sucedidas ou
 * falhas. Além disso, redireciona o usuário para a página de listagem de categorias com mensagens de sucesso ou erro.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * 
 * @package App\adms\Controllers\categories
 */
class DeleteCategory
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes da categoria e processar a exclusão.
     *
     * Este método verifica a validade do token CSRF e a existência do ID da categoria. Se válido, recupera os
     * detalhes da categoria do banco de dados e tenta excluir a categoria. Redireciona o usuário para a página de 
     * listagem de categorias com mensagens apropriadas baseadas no sucesso ou falha da operação.
     * 
     * @return void
     */
    public function index(): void
    {
        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessar o IF se existir o CSRF e for valido o CSRF
        if (!isset($this->data['form']['csrf_token']) or !CSRFHelper::validateCSRFToken('form_delete_category', $this->data['form']['csrf_token']) or empty($this->data['form']['id'])) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Categoria não encontrada", []);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Categoria não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-categories");

            return;
        }

        // Instanciar o Repository para recuperar o registro do banco de dados
        $deleteCategory = new CategoriesRepository();
        $this->data['category'] = $deleteCategory->getCategory((int) $this->data['form']['id']);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['category']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Categoria não encontrada", ['id' => (int) $this->data['form']['id']]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Categoria não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-categories");

            return;
        }

        // Instanciar o Repository para apagar o registro do banco de dados
        $result = $deleteCategory->deleteCategory($this->data['form']['id']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao apagar
            $_SESSION['success'] = "Categoria apagada com sucesso!";

            // Redirecionar o usuário para a página de listar
            header("Location: {$_ENV['URL_ADM']}list-categories");

            return;
        } else {
            // Criar a mensagem de erro ao tentar apagar
            $_SESSION['error'] = "Categoria não apagada!";

            // Redirecionar o usuário para a página de listar
            header("Location: {$_ENV['URL_ADM']}list-categories");
        }
    }
}
?>