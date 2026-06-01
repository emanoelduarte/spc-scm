<?php

namespace App\admsDaman\Controllers\materialstock;

use App\admsDaman\Controllers\Services\Validation\ValidationMovementStockService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\MaterialStockMovementRepository;
use App\admsDaman\Models\Repository\MaterialStockRepository;

class CreateStockMovement
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    public function index(): void
    {
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (!isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_stock_movement', $this->data['form']['csrf_token'])) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Material não encontrado", []);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Material não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-material-stock");

            return;
        }

        // Instanciar o Repository para recuperar o registro do banco de dados
        $movimentMaterial = new MaterialStockRepository();
        $this->data['movimentMaterial'] = $movimentMaterial->getUniqueMaterial((int) $this->data['form']['stock_id']);


        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['movimentMaterial']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Material não encontrado", ['id' => (int) $this->data['form']['stock_id']]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Material não encontrado! 2";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-material-stock");

            return;
        }

        // Instaciar a classe que valida os dados do formulário de itens do pedido com Rakit
        $validationItemns = new ValidationMovementStockService();
        $this->data['errors'] = $validationItemns->validate($this->data['form']);

        // Acessa o if quando existir algum campo com dados incorretos
        if (!empty($this->data['errors'])) {

            // Criar a mensagem de erro
            $_SESSION['error'] = implode(', ', $this->data['errors']);

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-material-stock");

            return;
        }

        // Bloquear transferência para a mesma obra de origem
        if ($this->data['form']['type'] === 'output' && ($this->data['form']['reason'] ?? '') === 'transfer') {

            // Buscar a obra de origem do item
            $movimentMaterial = new MaterialStockRepository();
            $stock = $movimentMaterial->getUniqueMaterial((int) $this->data['form']['stock_id']);

            if ($stock && $stock['adms_daman_project_id'] == $this->data['form']['adms_daman_project_id']) {
                $_SESSION['error'] = "Não é possível transferir para a mesma obra de origem.";
                header('Location: ' . $this->data['form']['redirect_to']);
                exit;
            }
        }

        // Criar a movimentação
        $movement = new MaterialStockMovementRepository();
        $result   = $movement->createMovement($this->data['form']);

        if ($result) {
            $type = $this->data['form']['type'] === 'input' ? 'Entrada' : 'Saída';
            $_SESSION['success'] = "{$type} registrada com sucesso!";
            GenerateLog::generateLog("info", "Movimentação registrada.", [
                'stock_id'   => $this->data['form']['stock_id'],
                'type'       => $this->data['form']['type'],
                'quantity'   => $this->data['form']['quantity'],
                'user_id'    => $_SESSION['user_id']
            ]);
        } else {
            $_SESSION['error'] = "Erro ao registrar movimentação. Tente novamente.";
        }

        $redirectTo = $this->data['form']['redirect_to'] ?? $_ENV['URL_ADM'] . 'list-material-stock';

        if ($result) {
            $_SESSION['success'] = "{$type} registrada com sucesso!";
            header('Location: ' . $redirectTo);
            return;
        } else {
            $_SESSION['error'] = "Erro ao registrar movimentação. Verifique o saldo do material e tente novamente.";
            header('Location: ' . $redirectTo);
            return;
        }
    }
}
