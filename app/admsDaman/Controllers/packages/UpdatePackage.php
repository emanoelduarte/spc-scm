<?php

namespace App\admsDaman\Controllers\packages;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationPackageService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\PackagesRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para editar pacote
 *
 * Esta classe é responsável por gerenciar a edição de informações de um pacote existente. Inclui a validação dos dados
 * do formulário, a atualização das informações do pacote no repositório e a renderização da visualização apropriada.
 * Caso haja algum problema, como um pacote não encontrado ou dados inválidos, mensagens de erro são exibidas e registradas.
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 * @package App\admsDaman\Controllers\packages;
 */
class UpdatePackage
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Editar o pacote.
     *
     * Este método gerencia o processo de edição de um pacote. Recebe os dados do formulário, valida o CSRF token e
     * a existência do pacote, e chama o método adequado para editar o pacote ou carregar a visualização de edição.
     *
     * @param int|string $id ID do pacote a ser editado.
     * 
     * @return void
     */
    public function index(int|string $id): void
    {
        // Receber os dados do formulário de cadastro de Pacote
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_update_package', $this->data['form']['csrf_token'])) {

            // Chamar o método Editar o Pacote
            $this->editPackage();
        } else {
            // Instanciar o Repository para recuperar o registro do banco de dados
            $viewPackage = new PackagesRepository();
            $this->data['form'] = $viewPackage->getPackage((int) $id);

            // Verificar se encontrou o registro no banco de dados
            if (!$this->data['form']) {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Pacote não encontrado", ['id' => (int) $id]);

                // Criar a mensagem de erro
                $_SESSION['error'] = "Pacote não encontrado!";

                // Redirecionar o usuário para a página listar
                header("Location: {$_ENV['URL_ADM']}list-packages");

                return;
            }

            $this->viewUpdatePackage();
        }
    }

    /**
     * Instanciar a classe responsável em carregar a VIEW e enviar os dados para View.
     * 
     * @return void
     */
    private function viewUpdatePackage(): void
    {
        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Editar Pacote",
            'menu' => "list-packages",
            'buttonPermissions' => ["ListPackages", "ViewPackage"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/packages/update", $this->data);
        $loadView->loadView();
    }

    /**
     * Editar Pacote
     * 
     * Este método realiza a edição de um pacote existente no sistema. Ele valida os dados do formulário usando a
     * Classe `ValidationPackageService`, exibe a view com os erros caso existam campos compdados incorretos,
     * Chama o repositório para atualizar o pacote e, depedendo do resultado, redireciona o pacote ou exibe
     * uma mensagem de erro
     * 
     * @return void
     */
    private function editPackage(): void
    {
        // Instanciar a classe validar os dados do formulário com Rakit
        $validationPackage = new ValidationPackageService();
        $this->data['errors'] = $validationPackage->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados inclorretos
        if (!empty($this->data['errors'])) {

            // Chama o método carregar a view
            $this->viewUpdatePackage();

            return;
        }

        // Instanciar o PackageRepository para chamar o método que faz a edição do pacote
        $userUpdate = new PackagesRepository();
        $result = $userUpdate->updatePackage($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar
            $_SESSION['success'] = "Pacote editado com sucesso!";

            // Redirecionar o usuário para a página de visualizar Pacote
            header("Location: {$_ENV['URL_ADM']}view-package/{$this->data['form']['id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar
            $this->data['errors'][] = "Pacote não editado!";

            // Chamar o método carregar a view
            $this->viewUpdatePackage();
        }
    }
}
?>