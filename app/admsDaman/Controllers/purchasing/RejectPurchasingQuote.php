<?php

namespace App\admsDaman\Controllers\purchasing;

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\OrderCommentsRepository;
use App\admsDaman\Models\Repository\PurchasingQuoteRepository;

class RejectPurchasingQuote
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
        if (isset($this->data['form']['csrf_token']) and CSRFHelper::validateCSRFToken('form_reject_quote', $this->data['form']['csrf_token'])) {
            $this->rejectPurchasing();
            // var_dump($this->data['form']);
            // exit;
        } else {
        }
    }

    private function rejectPurchasing()
    {
        // Buscar dados gerais
        $dataPurchasingQuote = new PurchasingQuoteRepository();
        $this->data['dataPurchasingQuote'] = $dataPurchasingQuote->getPurchasingQuote((int) $this->idPurchasingQuote);

        $this->data['form'] = array_merge($this->data['form'], $this->data['dataPurchasingQuote']);

        $dataPurchasingQuoteStatus = new PurchasingQuoteRepository();
        $result = $dataPurchasingQuoteStatus->updateStatusPurchasingQuote($this->data['form']);

        // Acesso o IF se o repository retornou true
        if ($result) {

            // Criar comentário com a data da rejeição relacionada ao pedido
            $createComment = new OrderCommentsRepository();
            $createComment->createAutomaticOrderRejected($this->data['form']);

            // Criar a mensagem de sucesso ao cadastrar/autorizar
            $_SESSION['success'] = "Compra rejeitada com sucesso!";

            // Redirecionar o usuário para a página de visualizar a compra recem criada
            header("Location: {$_ENV['URL_ADM']}view-purchasing-quote/{$this->data['form']['id']}");

            return;
        } else {
            // Criar a mensagem de erro ao tentar cadastrar
            $this->data['errors'][] = "Compra não cadastrada!";

            // Chamar o método carregar a view
            header("Location: {$_ENV['URL_ADM']}view-purchasing-quote/{$this->idPurchasingQuote}");
        }
    }
}