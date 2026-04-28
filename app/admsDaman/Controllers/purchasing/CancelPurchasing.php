<?php

namespace App\admsDaman\Controllers\purchasing;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\PurchasingRepository;

/**
 * Controller Responsável por Cancelar uma compra
 */
class CancelPurchasing 
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Recuperar os detalhes da compra e processar o cancelamento.
     *
     * Este método verifica a validade do token CSRF e a existência do ID da compra. Se válido, recupera os
     * detalhes da compra do banco de dados e tenta cancelar a compra. Redireciona o usuário para a página de 
     * visualização da compra com mensagens apropriadas baseadas no sucesso ou falha da operação.
     * 
     * @return void
     */
    public function index(): void
    {
        // Receber os dados do formulário
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        // Acessar o IF se existir o CSRF e for valido o CSRF
        if (!isset($this->data['form']['csrf_token']) or !CSRFHelper::validateCSRFToken('form_cancel_purchasing', $this->data['form']['csrf_token']) or empty($this->data['form']['id'])) {

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra não encontrada", []);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Compra não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}view-purchasing/{$this->data['form']['id']}");

            return;
        }
        
        // Instanciar o Repository para recuperar o registro do banco de dados
        $cancelPurchasing = new PurchasingRepository();
        $this->data['purchasing'] = $cancelPurchasing->getPurchasing((int) $this->data['form']['id']);

        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['purchasing']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra não encontrada", ['id' => (int) $this->data['form']['id']]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Compra não encontrada";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}view-purchasing/{$this->data['form']['id']}");

            return;
        }

        // Verificar se a compra já está cancelada e negar um novo cancelamento
        if(isset($this->data['purchasing']['purchasing_status_id']) && $this->data['purchasing']['purchasing_status_id'] === 2) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra já está cancelada!", ['id' => (int) $this->data['form']['id']]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Compra já está cancelada, impossível continuar.";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}view-purchasing/{$this->data['form']['id']}");

            return;
        }

        // Instanciar o Repository para cancelar o registro do banco de dados
        $result = $cancelPurchasing->updateCancelPurchasing((int) $this->data['form']['id']);

        // Acessa o IF se o repositório retornou TRUE
        if ($result) {
            // Criar a mensagem de sucesso ao apagar
            $_SESSION['success'] = "Compra cancelada com sucesso!";

            // Redirecionar o usuário para a página de listar
            header("Location: {$_ENV['URL_ADM']}view-purchasing/{$this->data['form']['id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar apagar
            $_SESSION['error'] = "Compra não cancelada!";

            // Redirecionar o usuário para a página de listar
            header("Location: {$_ENV['URL_ADM']}view-purchasing/{$this->data['form']['id']}");
        }
    }
}
?>