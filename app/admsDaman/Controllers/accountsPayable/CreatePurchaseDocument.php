<?php

namespace App\admsDaman\Controllers\accountsPayable;

use App\admsDaman\Controllers\Services\PageLayoutService;
use App\admsDaman\Models\Repository\NfeRepository;
use App\admsDaman\Models\Repository\PaymentMethodsRepository;
use App\admsDaman\Models\Repository\ProjectsRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;
use App\admsDaman\Models\Repository\UsersAccessLevelsRepository;
use App\admsDaman\Views\Services\LoadViewService;
use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Services\PurchaseDocumentService;
use Throwable;

class CreatePurchaseDocument
{
    /**
     * Dados enviados para a View.
     */
    private array|string|null $data = null;


    /**
     * Abrir formulário de lançamento financeiro da NF-e.
     */
    public function index(string|int $id): void
    {
        $nfeId = (int) $id;

        // Recuperar dados enviados pelo formulário
        $this->data['form'] = filter_input_array(
            INPUT_POST,
            FILTER_UNSAFE_RAW
        ) ?? [];

        /*
         * Recuperar NF-e.
         */
        $nfeRepository = new NfeRepository();

        $nfe = $nfeRepository->getNfeById($nfeId);


        /*
         * Verificar se a NF-e existe.
         */
        if (!$nfe) {

            $_SESSION['error'] = 'NF-e não encontrada.';

            header(
                'Location: ' . $_ENV['URL_ADM'] . 'list-nfes'
            );

            exit;
        }


        /*
         * Permitir lançamento somente de NF-e conferida.
         */
        if ((int) $nfe['is_checked'] !== 1) {

            $_SESSION['error'] =
                'A NF-e precisa ser conferida antes do lançamento.';

            header(
                'Location: ' . $_ENV['URL_ADM'] . 'list-nfes'
            );

            exit;
        }


        /*
         * Não permitir lançamento de NF-e cancelada
         * ou não autorizada.
         */
        if ($nfe['status'] !== 'authorized') {

            $_SESSION['error'] =
                'A NF-e não está autorizada para lançamento.';

            header(
                'Location: ' . $_ENV['URL_ADM'] . 'list-nfes'
            );

            exit;
        }


        /*
         * Verificar se a NF-e já possui lançamento.
         */
        $purchaseDocumentsRepository =
            new PurchaseDocumentsRepository();

        if (
            $purchaseDocumentsRepository
            ->existsByNfeId($nfeId)
        ) {

            $_SESSION['error'] =
                'Esta NF-e já possui um lançamento financeiro.';

            header(
                'Location: ' . $_ENV['URL_ADM'] . 'list-nfes'
            );

            exit;
        }


        /*
         * NF-e que será exibida no formulário.
         */
        $this->data['nfe'] = $nfe;

        /*
        * Se o formulário foi enviado.
        */
        /*
        * Se o formulário foi enviado.
        */
        if (!empty($this->data['form'])) {

            /*
            * Validar CSRF.
            */
            if (
                isset($this->data['form']['csrf_token'])
                &&
                CSRFHelper::validateCSRFToken(
                    'form_create_purchase_document',
                    $this->data['form']['csrf_token']
                )
            ) {

                $this->addPurchaseDocument($nfeId);

                return;
            }

            /*
            * POST realizado com CSRF inválido.
            */
            $this->data['errors'][] =
                'Token de segurança inválido ou expirado.';
        }

        /*
        * Carregar formulário.
        */
        $this->viewPurchaseDocument();
    }


    private function viewPurchaseDocument(): void
    {
        /*
         * Obras ativas.
         */
        $projectsRepository = new ProjectsRepository();

        $this->data['getAllProjectsSelectActive'] =
            $projectsRepository->getAllProjectsSelectActive();


        /*
         * Usuários que podem atuar como comprador.
         */
        $usersAccessLevelsRepository =
            new UsersAccessLevelsRepository();

        $this->data['getPurchaseUsersSelect'] =
            $usersAccessLevelsRepository->getPurchaseUsersSelect();


        /*
         * Condições de pagamento.
         */
        $paymentMethodsRepository =
            new PaymentMethodsRepository();

        $this->data['getAllPaymentSelect'] =
            $paymentMethodsRepository->getAllPaymentSelect();


        /*
         * Configuração da página.
         */
        $pageElements = [
            'title_head' => 'Lançar Compra',
            'menu' => 'list-nfes',
            'buttonPermissions' => [],
        ];

        $pageLayoutService = new PageLayoutService();

        $this->data = array_merge(
            $this->data,
            $pageLayoutService->configurePageElements(
                $pageElements
            )
        );

        /*
         * Carregar View.
         */
        $loadView = new LoadViewService(
            'admsDaman/Views/accountsPayable/create',
            $this->data
        );

        $loadView->loadView();
    }

    private function addPurchaseDocument(int $nfeId): void
    {
        try {

            /*
         * Garantir que o ID da NF-e venha da rota
         * e não de um valor manipulável do formulário.
         */
            $this->data['form']['adms_daman_nfe_id'] =
                $nfeId;

            /*
         * Usuário que está realizando o lançamento.
         */
            $this->data['form']['created_by'] =
                (int) $_SESSION['user_id'];

            $service = new PurchaseDocumentService();

            $purchaseDocumentId =
                $service->createFromNfe(
                    $this->data['form']
                );

            $_SESSION['success'] =
                'Lançamento financeiro cadastrado com sucesso.';

            header(
                'Location: '
                    . $_ENV['URL_ADM']
                    . 'view-purchase-document/'
                    . $purchaseDocumentId
            );

            exit;

            exit;
        } catch (Throwable $err) {

            $this->data['errors'][] =
                $err->getMessage();

            $this->viewPurchaseDocument();
        }
    }
}
