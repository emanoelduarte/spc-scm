<?php

namespace App\admsDaman\Controllers\materialstock;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationMaterialStockService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\MaterialStockRepository;
use App\admsDaman\Models\Repository\MeasurementUnitsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Views\Services\LoadViewService;

class UpdateMaterialStock
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    public function index(string|int $id)
    {
        // Receber os dados do formulário de edição do material
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_update_material', $this->data['form']['csrf_token'])) {

            // Chamar o método Editar o material
            $this->editMaterial();
            // var_dump($this->data['form']);
        } else {
            // Instanciar o Repository para recuperar o registro do banco de dados
            $updateMaterial = new MaterialStockRepository();
            $this->data['form'] = $updateMaterial->getUniqueMaterial((int) $id);

            // Verificar se encontrou o registro no banco de dados
            if (!$this->data['form']) {
                // Chamar o método para salvar o log
                GenerateLog::generateLog("error", "Material não encontrado", ['id' => (int) $id]);

                // Criar a mensagem de erro
                $_SESSION['error'] = "Material não encontrado!";

                // Redirecionar o usuário para a página listar
                header("Location: {$_ENV['URL_ADM']}list-material-stock");
                return;
            }
            // Chamar o método carregar a view
            $this->viewUpdateMaterial();
        }
    }

    /**
     * Instanciar a classe responsável em carregar a VIEW e enviar os dados para View.
     */
    private function viewUpdateMaterial(): void
    {
        // Instanciar o repositório para preencher os selects.
        $getMeasurementUnits = new MeasurementUnitsRepository();
        $this->data['getAllMeasurementUnitsSelect'] = $getMeasurementUnits->getAllMeasurementUnitsSelect();

        // Instanciar o repositório para preencher os selects.
        $getAllProjectsSelectActive = new ProjectsRepository();
        $this->data['getAllProjectsSelectActive'] = $getAllProjectsSelectActive->getAllProjectsSelectActive();

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Editar Material do Estoque",
            'menu' => "list-material-stock",
            'buttonPermissions' => ["ListMaterialStock", "UpdateMaterialStock"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/materialstock/update", $this->data);
        $loadView->loadView();
    }

    /**
     * Editar Material
     * 
     * Este método realiza a edição de um material existente no sistema. Ele valida os dados do formulário usando a
     * Classe `ValidationMaterialStockService`, exibe a view com os erros caso existam campos compdados incorretos,
     * Chama o repositório para atualizar o material e, depedendo do resultado, redireciona o usuário ou exibe
     * uma mensagem de erro
     * 
     * @return void
     */
    private function editMaterial(): void
    {

        // Instanciar a classe validar os dados do formulário com Rakit
        $validationMaterial = new ValidationMaterialStockService();
        $this->data['errors'] = $validationMaterial->validate($this->data['form']);

        // Acessa o IF quando existir o campo dados inclorretos
        if (!empty($this->data['errors'])) {

            // Chama o método carregar a view
            $this->viewUpdateMaterial();

            return;
        }
        // Instanciar o MaterialStockRepository para chamar o método que faz a edição do material
        $materialUpdate = new MaterialStockRepository();
        $result = $materialUpdate->updateMaterial($this->data['form']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao editar
            $_SESSION['success'] = "Material editado com sucesso!";

            // Redirecionar o usuário para a página de listar
            header("Location: {$_ENV['URL_ADM']}view-material-stock/" . $this->data['form']['id']);

            return;
        } else {
            // Criar a mensagem de erro ao tentar editar
            $this->data['errors'][] = "Material não editado!";

            // Chamar o método carregar a view
            $this->viewUpdateMaterial();
        }
    }
}
