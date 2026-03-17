<?php
    
namespace App\admsDaman\Controllers\packages;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\PackagesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para visualizar um Pacotes
 *
 * Esta classe é responsável por exibir as informações detalhadas de um Pacotes específico. Ela recupera os dados
 * do Pacotes a partir do repositório, valida se o Pacotes existe e carrega a visualização apropriada. Se o Pacotes
 * não for encontrado, uma mensagem de erro é exibida e o Pacotes é redirecionado para a página de lista.
 *
 * @package App\adms\Controllers\packages
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 */
class ViewPackage
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes do pacote.
     *
     * Este método gerencia a recuperação e exibição dos detalhes de um pacote específico. Ele valida o ID fornecido,
     * recupera os dados do pacote do repositório e carrega a visualização. Se o pacote não for encontrado, registra
     * um erro, exibe uma mensagem e redireciona para a página de lista de pacotes.
     *
     * @param int|string $id ID do pacote a ser visualizado.
     * 
     * @return void
     */
    public function index(int|string $id)
    {
        // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pacote não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Pacote não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-packages");

            return;
        }

        // Instanciar o Repository para recuperar o registro do banco de dados
        $viewPackage = new PackagesRepository();
        $this->data['package'] = $viewPackage->getPackage((int) $id);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['package']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pacote não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Pacote não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-packages");

            return;
        }

        // Criar o título da página
        $this->data['title_head'] = "Visualizar Pacotes";

        // Ativar o item de Menu
        $this->data['menu'] = "list-packages";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/packages/view", $this->data);
        $loadView->loadView();
    }
}
?>