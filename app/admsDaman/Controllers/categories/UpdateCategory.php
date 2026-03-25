<?php

namespace App\admsDaman\Controllers\categories;

use App\admsDaman\Controllers\Services\Validation\ValidationCategoryService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\CategoriesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para editar uma categoria
 *
 * Esta classe é responsável por gerenciar a edição de informações de uma categoria existente. Inclui a validação dos dados
 * do formulário, a atualização das informações da categoria no repositório e a renderização da visualização apropriada.
 * Caso haja algum problema, como uma categoria não encontrada ou dados inválidos, mensagens de erro são exibidas e registradas.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\categories;
 */
class UpdateCategory
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Editar a categoria.
     *
     * Este método gerencia o processo de edição de uma categoria. Recebe os dados do formulário, valida o CSRF token e
     * a existência da categoria, e chama o método adequado para editar a categoria ou carregar a visualização de edição.
     *
     * @param int|string $id ID da categoria a ser editado.
     * 
     * @return void
     */
    public function index(int|string $id): void
    {
        // Receber os dados do formulário de cadastro de categoria
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_update_category', $this->data['form']['csrf_token'])) {

            // Chamar o método Editar a categoria
            $this->editCategory();
        } else {
            // Instanciar o Repository para recuperar o registro do banco de dados
            $viewCategory = new CategoriesRepository();
            $this->data['form'] = $viewCategory->getCategory((int) $id);

            // Verificar se encontrou o registro no banco de dados
            if (!$this->data['form']) {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Categoria não encontrada", ['id' => (int) $id]);

                // Criar a mensagem de erro
                $_SESSION['error'] = "Categoria não encontrada!";

                // Redirecionar o usuário para a página listar
                header("Location: {$_ENV['URL_ADM']}list-categories");

                return;
            }

            $this->viewUpdateCategory();
        }
    }

    /**
     * Instanciar a classe responsável em carregar a VIEW e enviar os dados para View.
     * 
     * @return void
     */
    private function viewUpdateCategory(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Editar Categoria";

        // Ativar o item de Menu
        $this->data['menu'] = "list-categories";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/categories/update", $this->data);
        $loadView->loadView();
    }

    /**
     * Editar categoria
     * 
     * Este método realiza a edição de uma categoria existente no sistema. Ele valida os dados do formulário usando a
     * Classe `ValidationCategoryService`, exibe a view com os erros caso existam campos com dados incorretos,
     * Chama o repositório para atualizar a categoria e, depedendo do resultado, redireciona o usuário ou exibe
     * uma mensagem de erro
     * 
     * @return void
     */
    private function editCategory(): void
    {
        // Instanciar a classe validar os dados do formulário com Rakit
        $validationCategory = new ValidationCategoryService();
        $this->data['errors'] = $validationCategory->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados incorretos
        if (!empty($this->data['errors'])) {

            // Chama o método carregar a view
            $this->viewUpdateCategory();

            return;
        }

        // Instanciar o CategoriesRepository para chamar o método que faz a edição da categoria
        $categoryUpdate = new CategoriesRepository();
        $result = $categoryUpdate->updateCategory($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar
            $_SESSION['success'] = "Categoria editada com sucesso!";

            // Redirecionar o usuário para a página de visualizar Categoria
            header("Location: {$_ENV['URL_ADM']}view-category/{$this->data['form']['id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar
            $this->data['errors'][] = "Category não editada!";

            // Chamar o método carregar a view
            $this->viewUpdateCategory();
        }
    }
}
?>