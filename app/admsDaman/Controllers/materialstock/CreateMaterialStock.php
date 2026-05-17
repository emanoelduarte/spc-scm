<?php

namespace App\admsDaman\Controllers\materialstock;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Controllers\Services\Validation\ValidationMaterialStockService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\MaterialStockRepository;
use App\admsDaman\Models\Repository\MeasurementUnitsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Views\Services\LoadViewService;

class CreateMaterialStock
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    public function index(): void
    {
        // Receber os dados do formulário de cadastro de obra
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_create_material', $this->data['form']['csrf_token'])) {

            // Chamar método cadastrar passando pelas validações necessárias
            $this->addMaterial();
            // var_dump($this->data['form']);
            // exit;
        } else {
            // Chamar o método carregar a view
            $this->viewMaterial();
        }
    }

    /**
     * Carregar a visualização de criação do Material.
     * 
     * Este método configura os dados necessários e carrega a view para a criação de um novo Material.
     * 
     * @return void
     */
    private function viewMaterial(): void
    {
        // Instanciar o repositório para preencher os selects.
        $getMeasurementUnits = new MeasurementUnitsRepository();
        $this->data['getAllMeasurementUnitsSelect'] = $getMeasurementUnits->getAllMeasurementUnitsSelect();

        // Instanciar o repositório para preencher os selects.
        $getAllProjectsSelectActive = new ProjectsRepository();
        $this->data['getAllProjectsSelectActive'] = $getAllProjectsSelectActive->getAllProjectsSelectActive();

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Criar Material no Estoque",
            'menu' => "list-material-stock",
            'buttonPermissions' => ["ListMaterialStock"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/materialstock/create", $this->data);
        $loadView->loadView();
    }

    /**
     * Adicionar um novo material ao sistema.
     * 
     * Este método valida os dados do formulário usando a classe de validação `ValidationMaterialStockService` e,
     * se não houver erros, cria o material no banco de dados usando o `MaterialStockRepository`. Caso contrário, ele
     * recarrega a visualização de criação com mensagens de erro.
     * 
     * @return void
     */
    private function addMaterial(): void
    {
        // Instaciar a classe que valida os dados do formulário com Rakit
        $validationMaterial = new ValidationMaterialStockService();
        $this->data['errors'] = $validationMaterial->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Chamar o método carregar a view
            $this->viewMaterial();

            return;
        }

        // Instanciar o Repository para cadastrar o material
        $materialCreate = new MaterialStockRepository();
        $result = $materialCreate->createMaterial($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {
            // Criar a mensagem de sucesso ao cadastrar
            $_SESSION['success'] = "Material cadastrado com sucesso!";

            // Redirecionar o usuário para a página de visualizar o materia recem criado
            header("Location: {$_ENV['URL_ADM']}view-material-stock/$result");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            $this->data['errors'][] = "Material não cadastrado!";

            // Chamar o método carregar a view
            $this->viewMaterial();
        }
    }
}
