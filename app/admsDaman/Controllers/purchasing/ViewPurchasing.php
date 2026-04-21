<?php
namespace App\admsDaman\Controllers\purchasing;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\PurchasingRepository;
use App\admsDaman\Views\Services\LoadViewService;

class ViewPurchasing
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a View */
    private array|string|null $data = null;

    public function index(string|int $id)
    {
        // Acessa o IF se o id for valor do tipo inteiro
        if (!(int) $id) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Compra não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-purchasings");

            return;
        }

        // Instanciar o Repository para recuperar os registros do banco de dados
        $viewPurchasing = new PurchasingRepository();
        $this->data['purchasing'] = $viewPurchasing->getPurchasing((int) $id);
        
        // Verificar se encontrou o registro no banco de dados
        if (!$this->data['purchasing']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Compra não encontrada", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Compra não encontrada!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-purchasings");

            return;
        }

        // Instanciar o Repository para recuperar os registros do banco de dados
        $viewItemsPurchasing = new PurchasingRepository();
        $this->data['itemsPurchasing'] = $viewItemsPurchasing->getItems((int) $id);

        // Criar o título da página
        $this->data['title_head'] = "Compras";

        $this->data['menu'] = "list-purchasings";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/purchasing/view", $this->data);
        $loadView->loadView();
    }
}
?>