<?php

namespace App\admsDaman\Controllers\categories;

use App\admsDaman\Controllers\Services\Validation\ValidationCategoryService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\CategoriesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para criação de categoria
 *
 * Esta classe é responsável pelo processo de criação de novas categorias. Ela lida com a recepção dos dados do
 * formulário, validação dos mesmos, e criação da categoria no sistema. Além disso, é responsável por carregar
 * a visualização apropriada com mensagens de sucesso ou erro.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\categories
 */
class CreateCategory
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Método principal que gerencia a criação da categoria.
     *
     * Este método é chamado para processar a criação de uma nova categoria. Ele verifica a validade do token CSRF,
     * valida os dados do formulário e, se tudo estiver correto, cria a categoria. Caso contrário, carrega a
     * visualização de criação de categoria com mensagens de erro.
     * 
     * @return void
     */
    public function index()
    {
        // Receber os dados do formulário de cadastro de categoria
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_create_category', $this->data['form']['csrf_token'])) {

            // Chamar método cadastrar passando pelas validações necessárias
            $this->addCategory();
        } else {
            // Chamar o método carregar a view
            $this->viewCategory();
        }
    }

    /**
     * Carregar a visualização de criação de categoria.
     * 
     * Este método configura os dados necessários e carrega a view para a criação de uma nova categoria.
     * 
     * @return void
     */
    private function viewCategory(): void
    {
        // Criar o título da página
        $this->data['title_head'] = "Cadastrar Categoria";

        // Ativar o item de Menu
        $this->data['menu'] = "list-categories";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/categories/create", $this->data);
        $loadView->loadView();
    }

    /**
     * Adicionar um nova categoria ao sistema.
     * 
     * Este método valida os dados do formulário usando a classe de validação `ValidationCategoryService` e,
     * se não houver erros, cria a categoria no banco de dados usando o `CategoriesRepository`. Caso contrário, ele
     * recarrega a visualização de criação com mensagens de erro.
     * 
     * @return void
     */
    private function addCategory(): void
    {
        // Instaciar a classe que valida os dados do formulário com Rakit
        $validationCategory = new ValidationCategoryService();
        $this->data['errors'] = $validationCategory->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewCategory();

            return;
        }

        // Instanciar o Repository para cadastrar a categoria
        $categoryCreate = new CategoriesRepository();
        $result = $categoryCreate->createCategory($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Categoria cadastrada com sucesso!";

            // Redirecionar o usuário para a página de visualizar a categoria recem criada
            header("Location: {$_ENV['URL_ADM']}view-category/$result");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            $this->data['errors'][] = "Categoria não cadastrada!";

            // Chamar o método carregar a view
            $this->viewCategory();
        }
    }
}
?>