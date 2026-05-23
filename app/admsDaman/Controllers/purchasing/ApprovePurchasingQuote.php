<?php

namespace App\admsDaman\Controllers\purchasing;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\OrderCommentsRepository;
use App\admsDaman\Models\Repository\OrdersRepository;
use App\admsDaman\Models\Repository\PurchasingQuoteRepository;
use App\admsDaman\Models\Repository\PurchasingRepository;

class ApprovePurchasingQuote
{
    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * @var int $id ID da compra pendente
     */
    private int $idPurchasingQuote;

    public function index(int $idPurchasingQuote)
    {
        $this->idPurchasingQuote = $idPurchasingQuote;

        // Receber os dados do formulário de cadastro de pedido
        $this->data['form'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);


        // Acessa o IF se existir o CSRF e for válido o CSRF
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_aprovation_quote', $this->data['form']['csrf_token'])) {
            $this->addPurchasing();
            // var_dump($this->data['form']);
            // exit;
        } else {
        }
    }

    private function addPurchasing()
    {
        // Buscar dados gerais
        $dataPurchasingQuote = new PurchasingQuoteRepository();
        $this->data['dataPurchasingQuote'] =
            $dataPurchasingQuote->getPurchasingQuote((int) $this->idPurchasingQuote);

        // Buscar itens
        $dataPurchasingQuoteItems = new PurchasingQuoteRepository();
        $this->data['items'] =
            $dataPurchasingQuoteItems->getItemsQuote((int) $this->idPurchasingQuote);

        // Montar form
        $this->data['form'] = array_merge(
            $this->data['form'],
            $this->data['dataPurchasingQuote']
        );

        // Adicionar items dentro da chave "items"
        $this->data['form']['items'] = $this->data['items'];

        $dataPurchasingQuoteStatus = new PurchasingQuoteRepository();
        $dataPurchasingQuoteStatus->updateStatusPurchasingQuote($this->data['form']);

        // Instanciar o Repository para cadastrar o Compra
        $generatePurchasing = new PurchasingRepository();
        $result = $generatePurchasing->generatePurchasing($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {
            // Atualizar Status do pedido
            $changeStatus = new OrdersRepository();
            $changeStatus->updateAutomaticOrderStatus($this->data['form']['adms_daman_order_id']);

            // Criar comentário com a data da compra relacionada ao pedido
            $createComment = new OrderCommentsRepository();
            $createComment->createAutomaticOrderPurchased($this->data['form']['adms_daman_order_id']);

            // Criar a mensagem de sucesso ao cadastrar/autorizar
            $_SESSION['success'] = "Compra autorizada com sucesso!";

            // Redirecionar o usuário para a página de visualizar a compra recem criada
            header("Location: {$_ENV['URL_ADM']}view-purchasing/$result");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            $this->data['errors'][] = "Compra não cadastrada!";

            // Chamar o método carregar a view
            header("Location: {$_ENV['URL_ADM']}view-purchasing-quote/{$this->idPurchasingQuote}");
        }
    }
}
