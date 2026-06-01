<?php

namespace App\admsDaman\Controllers\materialstock;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\MaterialStockMovementRepository;
use App\admsDaman\Models\Repository\MaterialStockRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ViewMaterialStock
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    public function index(string|int $id): void
    {

        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Material não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Material não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-material-stock");

            return;
        }

        // Instanciar o repositório para preencher os selects.
        $getAllProjectsSelectActive = new ProjectsRepository();
        $this->data['getAllProjectsSelectActive'] = $getAllProjectsSelectActive->getAllProjectsSelectActive();

        // Buscar Dados gerais de um Material específico da obra
        $viewMaterial = new MaterialStockRepository();
        $this->data['material'] = $viewMaterial->getUniqueMaterial((int) $id);

        // Buscar informações de movimentação do material na obra:
        $viewMaterialMovement = new MaterialStockMovementRepository();
        $this->data['movements'] = $viewMaterialMovement->getMaterialMovement((int) $id);

        // Solicitar do repositório de níveis de acesso do usuário os níveis do usuário logado para configurar o conteúdo que ele tem acesso para manipular saídas do estoque
        $userAccessLevel = new UsersAccessLevelsRepository();
        $this->data['userAccessLevelsArray'] = $userAccessLevel->getUsersAccessLevels($_SESSION['user_id']);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['material']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Pedido não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Material não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-material-stock");

            return;
        }

        // Configurar os elementos da página
        $pageElements = [
            'title_head' => "Visualizar Material do Estoque",
            'menu' => "list-material-stock",
            'buttonPermissions' => ["CreateStockMovement", "UpdateMaterialStock", "ListMaterialStock"],
        ];

        $pageLayoutService = new PageLayoutService();
        $this->data = array_merge($this->data, $pageLayoutService->configurePageElements($pageElements));

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/materialstock/view", $this->data);
        $loadView->loadView();
    }
}