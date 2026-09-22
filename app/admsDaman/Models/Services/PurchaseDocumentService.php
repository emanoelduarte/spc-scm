<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Services;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\NfeRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentsRepository;
use App\admsDaman\Models\Repository\PurchaseInstallmentsRepository;
use App\admsDaman\Models\Repository\PurchaseInstallmentPaymentsRepository;
use App\admsDaman\Models\Repository\PurchaseDocumentAllocationsRepository;
use RuntimeException;
use Throwable;

class PurchaseDocumentService extends DbConnection
{
    private NfeRepository $nfeRepository;
    private PurchaseDocumentsRepository $purchaseDocumentsRepository;
    private PurchaseInstallmentsRepository $purchaseInstallmentsRepository;
    private PurchaseInstallmentsService $purchaseInstallmentsService;
    private PurchaseDocumentAllocationsRepository $purchaseDocumentAllocationsRepository;
    private PurchaseDocumentAllocationService $purchaseDocumentAllocationService;

    public function __construct()
    {
        $this->nfeRepository = new NfeRepository();

        $this->purchaseDocumentsRepository =
            new PurchaseDocumentsRepository();

        $this->purchaseInstallmentsRepository =
            new PurchaseInstallmentsRepository();

        $this->purchaseInstallmentsService =
            new PurchaseInstallmentsService();

        /*
        * Repository responsável por persistir
        * a apropriação do lançamento entre obras.
        */
        $this->purchaseDocumentAllocationsRepository =
            new PurchaseDocumentAllocationsRepository();

        /*
        * Service responsável por normalizar e validar
        * os dados de rateio recebidos do formulário.
        */
        $this->purchaseDocumentAllocationService =
            new PurchaseDocumentAllocationService();
    }

    /**
     * Criar lançamento financeiro a partir de uma NF-e.
     */
    public function createFromNfe(array $data): int
    {
        /*
         * =====================================================
         * RECUPERAR E VALIDAR A NF-e
         * =====================================================
         */
        $nfeId =
            (int) (
                $data['adms_daman_nfe_id']
                ?? 0
            );


        if ($nfeId <= 0) {

            throw new RuntimeException(
                'NF-e inválida.'
            );
        }


        $nfe =
            $this->nfeRepository
            ->getNfeById(
                $nfeId
            );


        if (!$nfe) {

            throw new RuntimeException(
                'NF-e não encontrada.'
            );
        }


        /*
         * A NF-e precisa ter sido conferida antes
         * de entrar no fluxo financeiro.
         */
        if ((int) ($nfe['is_checked'] ?? 0) !== 1) {

            throw new RuntimeException(
                'A NF-e ainda não foi conferida.'
            );
        }


        /*
         * Não permitir lançamento de NF-e cancelada
         * ou que não esteja autorizada.
         */
        if (($nfe['status'] ?? '') !== 'authorized') {

            throw new RuntimeException(
                'A NF-e não está autorizada para lançamento.'
            );
        }


        /*
         * Impedir que a mesma NF-e gere dois
         * lançamentos financeiros.
         */
        if (
            $this->purchaseDocumentsRepository
            ->existsByNfeId(
                (int) $nfe['id']
            )
        ) {

            throw new RuntimeException(
                'Esta NF-e já possui um lançamento financeiro.'
            );
        }


        /*
         * =====================================================
         * VALIDAR DADOS PRINCIPAIS DA COMPRA
         * =====================================================
         */
        $projectId =
            (int) (
                $data['adms_daman_project_id']
                ?? 0
            );


        $buyerId =
            (int) (
                $data['adms_daman_user_id']
                ?? 0
            );


        $paymentMethodId =
            (int) (
                $data['adms_daman_payment_method_id']
                ?? 0
            );


        $createdBy =
            (int) (
                $data['created_by']
                ?? 0
            );


        /*
         * Se a data da compra não vier do formulário,
         * utilizar a data de emissão da NF-e.
         */
        $purchaseDate =
            trim(
                (string) (
                    $data['purchase_date']
                    ?? substr(
                        (string) ($nfe['issue_date'] ?? ''),
                        0,
                        10
                    )
                )
            );


        /*
         * =====================================================
         * SITUAÇÃO DO PARCELAMENTO
         * =====================================================
         *
         * Quando "Falta boleto" estiver marcado,
         * o lançamento será criado normalmente,
         * porém ainda sem parcelas definitivas.
         */
        $paymentSchedulePending =
            !empty($data['payment_schedule_pending']);


        $paymentScheduleStatus =
            $paymentSchedulePending
            ? 'pending'
            : 'confirmed';


        if ($projectId <= 0) {

            throw new RuntimeException(
                'Informe a obra da compra.'
            );
        }


        if ($buyerId <= 0) {

            throw new RuntimeException(
                'Informe o comprador responsável.'
            );
        }


        /*
         * A condição de pagamento é obrigatória
         * somente quando o parcelamento já estiver
         * confirmado.
         */
        if (
            !$paymentSchedulePending
            &&
            $paymentMethodId <= 0
        ) {

            throw new RuntimeException(
                'Informe a condição de pagamento.'
            );
        }


        if ($createdBy <= 0) {

            throw new RuntimeException(
                'Usuário responsável pelo lançamento inválido.'
            );
        }


        if (empty($purchaseDate)) {

            throw new RuntimeException(
                'Informe a data da compra.'
            );
        }


        /*
         * =====================================================
         * VALOR OFICIAL DA COMPRA
         * =====================================================
         *
         * Para lançamento originado de NF-e, o valor oficial
         * continua vindo da própria nota fiscal importada.
         */
        /*
        * O valor da NF-e vem do banco em formato decimal SQL:
        *
        * 160.00
        * 1234.56
        *
        * Portanto não devemos passar pelo conversor
        * de moeda brasileira.
        */
        $totalValueCents =
            (int) round(
                (float) (
                    $nfe['total_value']
                    ?? 0
                )
                    * 100
            );


        if ($totalValueCents <= 0) {

            throw new RuntimeException(
                'A NF-e possui valor total inválido.'
            );
        }


        $totalValue =
            number_format(
                $totalValueCents / 100,
                2,
                '.',
                ''
            );


        /*
         * =====================================================
         * VALIDAR E PREPARAR RATEIO ENTRE OBRAS
         * =====================================================
         *
         * Sem rateio:
         * uma única allocation será criada com 100% do valor.
         *
         * Com rateio:
         * duas ou mais obras dividirão exatamente o valor da NF-e.
         */
        $allocations =
            $this->purchaseDocumentAllocationService
            ->normalizeAllocations(
                $data,
                $totalValue
            );


        /*
         * =====================================================
         * VALIDAR PARCELAS
         * =====================================================
         *
         * Fluxo normal:
         * validar e preparar as parcelas definitivas.
         *
         * FB / Falta boleto:
         * não criar parcelas falsas ou provisórias.
         */
        $installments = [];


        if (!$paymentSchedulePending) {

            $installments =
                $this->prepareInstallments(
                    $data['installments']
                        ?? [],
                    $totalValue
                );
        }


        $connection =
            $this->getConnection();


        try {

            /*
             * =================================================
             * INICIAR TRANSAÇÃO
             * =================================================
             */
            $connection->beginTransaction();


            /*
             * =================================================
             * CRIAR CABEÇALHO DO LANÇAMENTO
             * =================================================
             */
            $purchaseDocumentId =
                $this->purchaseDocumentsRepository
                ->create([

                    'adms_daman_nfe_id' =>
                    (int) $nfe['id'],

                    /*
                         * Mantido temporariamente por compatibilidade.
                         * A fonte completa das obras será a tabela
                         * de allocations.
                         */
                    'adms_daman_project_id' =>
                    $projectId,

                    'adms_daman_user_id' =>
                    $buyerId,

                    'adms_daman_payment_method_id' =>
                    $paymentMethodId > 0
                        ? $paymentMethodId
                        : null,

                    'payment_schedule_status' =>
                    $paymentScheduleStatus,

                    'purchase_date' =>
                    $purchaseDate,

                    'observation' =>
                    $data['observation']
                        ?? null,

                    'status' =>
                    'open',

                    'created_by' =>
                    $createdBy,
                ]);


            if ($purchaseDocumentId <= 0) {

                throw new RuntimeException(
                    'Não foi possível criar o lançamento financeiro.'
                );
            }


            /*
             * =================================================
             * CRIAR RATEIO / APROPRIAÇÃO POR OBRA
             * =================================================
             *
             * Todo lançamento passa a possuir ao menos
             * uma allocation, mesmo quando não há rateio.
             */
            $allocationsCreated =
                $this->purchaseDocumentAllocationsRepository
                ->createMany(
                    $purchaseDocumentId,
                    $allocations,
                    $createdBy
                );


            if (!$allocationsCreated) {

                throw new RuntimeException(
                    'Não foi possível criar o rateio do lançamento financeiro.'
                );
            }


            /*
             * =================================================
             * CRIAR PARCELAS
             * =================================================
             *
             * Em um lançamento FB, nenhuma parcela definitiva
             * é criada neste momento.
             */
            if (!$paymentSchedulePending) {

                $this->purchaseInstallmentsRepository
                    ->createMany(
                        $purchaseDocumentId,
                        $installments
                    );


                /*
                 * =============================================
                 * BAIXAR PARCELAS MARCADAS AO SALVAR
                 * =============================================
                 *
                 * A NF-e utiliza o mesmo mecanismo do
                 * lançamento manual. A parcela é criada
                 * normalmente e, depois de possuir ID real
                 * no banco, recebe uma baixa financeira real.
                 *
                 * Como esta transação já está aberta, o
                 * PurchaseInstallmentPaymentService não fará
                 * commit isolado. Se qualquer baixa falhar,
                 * todo o lançamento da NF-e será revertido.
                 */
                $this->createPaymentsOnSave(
                    $purchaseDocumentId,
                    $installments,
                    $createdBy,
                    $purchaseDate
                );


                /*
                 * Caso todas as parcelas tenham sido baixadas
                 * automaticamente, encerrar também o documento.
                 */
                $this->closePurchaseDocumentIfFullyPaid(
                    $purchaseDocumentId
                );
            }


            /*
             * =================================================
             * CONFIRMAR TRANSAÇÃO
             * =================================================
             */
            $connection->commit();


            return $purchaseDocumentId;
        } catch (Throwable $err) {

            if ($connection->inTransaction()) {

                $connection->rollBack();
            }


            GenerateLog::generateLog(
                'error',
                'Erro ao criar lançamento financeiro da NF-e.',
                [
                    'nfe_id' =>
                    $nfeId,

                    'project_id' =>
                    $projectId,

                    'payment_method_id' =>
                    $paymentMethodId > 0
                        ? $paymentMethodId
                        : null,

                    'payment_schedule_status' =>
                    $paymentScheduleStatus,

                    'error' =>
                    $err->getMessage(),
                ]
            );


            throw $err;
        }
    }

    /**
     * Validar e preparar as parcelas para gravação.
     */
    private function prepareInstallments(
        array $installments,
        string $totalValue
    ): array {

        if (empty($installments)) {
            throw new RuntimeException(
                'Nenhuma parcela foi informada.'
            );
        }

        $allowedStatuses = [
            'AV',
            'AT',
            'ON',
            'AP',
        ];

        $prepared = [];

        $totalInstallmentsCents = 0;

        foreach ($installments as $installment) {

            $installmentNumber =
                (int) ($installment['installment_number'] ?? 0);

            $status =
                strtoupper(
                    trim(
                        (string) ($installment['status'] ?? '')
                    )
                );

            $dueDate =
                !empty($installment['due_date'])
                ? $installment['due_date']
                : null;

            $amountCents =
                $this->moneyToCents(
                    (string) (
                        $installment['original_amount'] ?? '0'
                    )
                );


            /*
             * Baixa automática no momento do lançamento.
             *
             * O checkbox envia pay_on_save = 1.
             * A parcela continuará sendo criada normalmente
             * e, depois de possuir um ID real no banco,
             * será registrada uma baixa financeira verdadeira.
             */
            $payOnSave =
                !empty(
                    $installment['pay_on_save']
                );


            /*
         * Número da parcela.
         */
            if ($installmentNumber <= 0) {
                throw new RuntimeException(
                    'Número de parcela inválido.'
                );
            }


            /*
         * Status permitido.
         */
            if (!in_array(
                $status,
                $allowedStatuses,
                true
            )) {
                throw new RuntimeException(
                    "Status inválido na parcela {$installmentNumber}."
                );
            }


            /*
         * Somente permuta pode ficar
         * sem vencimento.
         */
            if (
                $status !== 'AP'
                &&
                empty($dueDate)
            ) {
                throw new RuntimeException(
                    "Informe o vencimento da parcela {$installmentNumber}."
                );
            }


            /*
         * Valor precisa ser maior que zero.
         */
            if ($amountCents <= 0) {
                throw new RuntimeException(
                    "Valor inválido na parcela {$installmentNumber}."
                );
            }


            /*
             * Permuta não representa uma baixa financeira.
             * Mesmo que o HTML seja manipulado, o backend
             * não permitirá pay_on_save em uma parcela AP.
             */
            if (
                $payOnSave
                &&
                $status === 'AP'
            ) {

                throw new RuntimeException(
                    "A parcela {$installmentNumber} é uma permuta e não pode ser baixada ao salvar."
                );
            }


            $totalInstallmentsCents += $amountCents;


            $prepared[] = [
                'installment_number' =>
                $installmentNumber,

                'due_date' =>
                $status === 'AP'
                    ? null
                    : $dueDate,

                'original_amount' =>
                number_format(
                    $amountCents / 100,
                    2,
                    '.',
                    ''
                ),

                'status' =>
                $status,

                'observation' =>
                $installment['observation'] ?? null,

                /*
                 * Campo de controle do Service.
                 * O Repository de parcelas ignora esta chave;
                 * ela será utilizada após a criação das parcelas
                 * para registrar o pagamento correspondente.
                 */
                'pay_on_save' =>
                $payOnSave,
            ];
        }


        /*
     * Conferir se a soma das parcelas
     * é exatamente igual ao valor da NF-e.
     */
        $documentTotalCents =
            (int) round(
                ((float) $totalValue) * 100
            );


        if ($totalInstallmentsCents !== $documentTotalCents) {

            $difference =
                abs(
                    $documentTotalCents
                        - $totalInstallmentsCents
                );

            throw new \RuntimeException(
                'A soma das parcelas é diferente '
                    . 'do valor total da compra. Diferença: R$ '
                    . number_format(
                        $difference / 100,
                        2,
                        ',',
                        '.'
                    )
            );
        }


        return $prepared;
    }

    /**
     * Converter valor no formato brasileiro
     * para centavos.
     */
    private function moneyToCents(string $value): int
    {
        $value = trim($value);

        $value = str_replace(
            ['R$', ' '],
            '',
            $value
        );

        /*
     * Exemplo:
     *
     * 1.234,56
     *     ↓
     * 1234.56
     */
        $value = str_replace(
            '.',
            '',
            $value
        );

        $value = str_replace(
            ',',
            '.',
            $value
        );

        if (!is_numeric($value)) {
            return 0;
        }

        return (int) round(
            ((float) $value) * 100
        );
    }

    /**
     * Criar um lançamento financeiro avulso.
     *
     * Diferente do createFromNfe(), este lançamento não possui
     * uma NF-e importada como origem. O fornecedor, documento
     * e valor total são informados pelo usuário.
     *
     * O lançamento e suas parcelas são gravados dentro da mesma
     * transação. Se qualquer parte falhar, nada é persistido.
     *
     * @param array $data Dados enviados pelo formulário.
     *
     * @return int ID do lançamento financeiro criado.
     *
     * @throws RuntimeException
     * @throws Throwable
     */
    public function createManual(array $data): int
    {
        /*
     * =====================================================
     * VALIDAR DADOS PRINCIPAIS
     * =====================================================
     */

        $supplierId =
            (int) (
                $data['adms_daman_supplier_id']
                ?? 0
            );

        $projectId =
            (int) (
                $data['adms_daman_project_id']
                ?? 0
            );

        $buyerId =
            (int) (
                $data['adms_daman_user_id']
                ?? 0
            );

        $paymentMethodId =
            (int) (
                $data['adms_daman_payment_method_id']
                ?? 0
            );

        $createdBy =
            (int) (
                $data['created_by']
                ?? 0
            );

        $purchaseDate =
            trim(
                (string) (
                    $data['purchase_date']
                    ?? ''
                )
            );

        /*
        * =====================================================
        * SITUAÇÃO DO PARCELAMENTO
        * =====================================================
        *
        * Quando "Falta boleto" estiver marcado,
        * o lançamento poderá ser criado sem parcelas
        * e sem condição de pagamento definida.
        */
        $paymentSchedulePending =
            !empty($data['payment_schedule_pending']);


        $paymentScheduleStatus =
            $paymentSchedulePending
            ? 'pending'
            : 'confirmed';


        if ($supplierId <= 0) {

            throw new \RuntimeException(
                'Informe o fornecedor da compra.'
            );
        }


        if ($projectId <= 0) {

            throw new \RuntimeException(
                'Informe a obra da compra.'
            );
        }


        if ($buyerId <= 0) {

            throw new \RuntimeException(
                'Informe o comprador responsável.'
            );
        }


        /*
        * A condição de pagamento é obrigatória
        * somente quando o parcelamento já estiver
        * confirmado.
        */
        if (
            !$paymentSchedulePending
            &&
            $paymentMethodId <= 0
        ) {

            throw new \RuntimeException(
                'Informe a condição de pagamento.'
            );
        }


        if ($createdBy <= 0) {

            throw new \RuntimeException(
                'Usuário responsável pelo lançamento inválido.'
            );
        }


        if (empty($purchaseDate)) {

            throw new \RuntimeException(
                'Informe a data da compra.'
            );
        }


        /*
     * =====================================================
     * VALIDAR VALOR TOTAL
     * =====================================================
     */

        $totalValueCents =
            $this->moneyToCents(
                (string) (
                    $data['total_value']
                    ?? '0'
                )
            );


        if ($totalValueCents <= 0) {

            throw new \RuntimeException(
                'Informe um valor válido para a compra.'
            );
        }


        /*
        * Converter para o formato decimal esperado
        * pelo banco:
        *
        * 1.234,56
        *      ↓
        * 1234.56
        */
        $totalValue =
            number_format(
                $totalValueCents / 100,
                2,
                '.',
                ''
            );

        /*
        * =====================================================
        * VALIDAR E PREPARAR RATEIO ENTRE OBRAS
        * =====================================================
        *
        * Independentemente de o usuário marcar ou não
        * "Existe rateio", daqui em diante sempre teremos
        * uma lista padronizada de alocações.
        *
        * Sem rateio:
        * uma única obra recebe 100% do lançamento.
        *
        * Com rateio:
        * duas ou mais obras recebem os respectivos valores.
        */
        $allocations =
            $this->purchaseDocumentAllocationService
            ->normalizeAllocations(
                $data,
                $totalValue
            );


        /*
        * =====================================================
        * VALIDAR PARCELAS
        * =====================================================
        *
        * Parcelamento confirmado:
        * validar normalmente as parcelas informadas.
        *
        * Parcelamento pendente:
        * nenhuma parcela definitiva será criada ainda.
        */
        $installments = [];


        if (!$paymentSchedulePending) {

            $installments =
                $this->prepareInstallments(
                    $data['installments'] ?? [],
                    $totalValue
                );
        }


        try {

            /*
         * =================================================
         * INICIAR TRANSAÇÃO
         * =================================================
         */
            $this->getConnection()
                ->beginTransaction();


            /*
         * Dados preparados para o Repository.
         */
            $purchaseData = [

                'adms_daman_supplier_id' =>
                $supplierId,

                'document_type' =>
                !empty($data['document_type'])
                    ? trim(
                        (string) $data['document_type']
                    )
                    : null,

                'document_number' =>
                !empty($data['document_number'])
                    ? trim(
                        (string) $data['document_number']
                    )
                    : null,

                'document_date' =>
                !empty($data['document_date'])
                    ? $data['document_date']
                    : null,

                'total_value' =>
                $totalValue,

                'adms_daman_project_id' =>
                $projectId,

                'adms_daman_user_id' =>
                $buyerId,

                'adms_daman_payment_method_id' =>
                $paymentMethodId > 0
                    ? $paymentMethodId
                    : null,

                'payment_schedule_status' =>
                $paymentScheduleStatus,

                'purchase_date' =>
                $purchaseDate,

                'observation' =>
                $data['observation']
                    ?? null,

                'status' =>
                'open',

                'created_by' =>
                $createdBy,
            ];


            /*
            * =================================================
            * CRIAR CABEÇALHO DO LANÇAMENTO
            * =================================================
            */
            $purchaseDocumentId =
                $this->purchaseDocumentsRepository
                ->createManual(
                    $purchaseData
                );


            if ($purchaseDocumentId <= 0) {

                throw new \RuntimeException(
                    'Não foi possível criar o lançamento financeiro.'
                );
            }

            /*
            * =====================================================
            * CRIAR RATEIO / APROPRIAÇÃO POR OBRA
            * =====================================================
            *
            * Todo lançamento possui ao menos uma alocação.
            *
            * Quando não existe rateio informado pelo usuário,
            * a única obra recebe 100% do valor do documento.
            */
            $allocationsCreated =
                $this->purchaseDocumentAllocationsRepository
                ->createMany(
                    $purchaseDocumentId,
                    $allocations,
                    $createdBy
                );


            if (!$allocationsCreated) {

                throw new RuntimeException(
                    'Não foi possível criar o rateio do lançamento financeiro.'
                );
            }


            /*
            * =================================================
            * CRIAR PARCELAS
            * =================================================
            *
            * Em um lançamento FB não existem parcelas
            * definitivas ainda.
            */
            if (!$paymentSchedulePending) {

                $this->purchaseInstallmentsRepository
                    ->createMany(
                        $purchaseDocumentId,
                        $installments
                    );


                /*
                 * =============================================
                 * BAIXAR PARCELAS MARCADAS AO SALVAR
                 * =============================================
                 *
                 * A baixa é registrada como um pagamento real,
                 * utilizando o mesmo Service usado pela tela
                 * "Realizar Pagamento".
                 *
                 * Como já existe uma transação aberta, o Service
                 * de pagamento não fará commit isoladamente.
                 * Se qualquer baixa falhar, todo o lançamento
                 * será desfeito pelo rollback deste método.
                 */
                $this->createPaymentsOnSave(
                    $purchaseDocumentId,
                    $installments,
                    $createdBy,
                    $purchaseDate
                );


                /*
                 * Se todas as parcelas tiverem sido baixadas
                 * automaticamente, encerrar também o documento.
                 */
                $this->closePurchaseDocumentIfFullyPaid(
                    $purchaseDocumentId
                );
            }


            /*
            * =================================================
            * CONFIRMAR TRANSAÇÃO
            * =================================================
            */
            $this->getConnection()
                ->commit();


            return $purchaseDocumentId;
        } catch (\Throwable $err) {

            /*
            * Se qualquer parte falhar,
            * desfazer todas as alterações.
            */
            if (
                $this->getConnection()
                ->inTransaction()
            ) {

                $this->getConnection()
                    ->rollBack();
            }


            GenerateLog::generateLog(
                'error',
                'Erro ao criar lançamento financeiro avulso.',
                [
                    'error' =>
                    $err->getMessage(),
                ]
            );


            throw $err;
        }
    }

    /**
     * Registrar pagamentos automáticos das parcelas marcadas
     * com "Baixar esta parcela ao salvar".
     *
     * As parcelas precisam existir primeiro no banco para que
     * possuam seus respectivos IDs. Por isso, após o createMany(),
     * recuperamos as parcelas do lançamento e relacionamos cada
     * uma pelo installment_number.
     *
     * O pagamento é criado pelo PurchaseInstallmentPaymentService,
     * mantendo exatamente as mesmas regras de uma baixa realizada
     * manualmente pela tela:
     *
     * - cria histórico real de pagamento;
     * - liquida o principal;
     * - altera a parcela para OK quando quitada;
     * - permite estorno posteriormente.
     */
    private function createPaymentsOnSave(
        int $purchaseDocumentId,
        array $installments,
        int $createdBy,
        string $paymentDate,
        string $observation =
            'Baixa automática registrada no momento do lançamento da compra.'
    ): void {

        /*
         * Separar somente as parcelas efetivamente marcadas.
         */
        $installmentsToPay =
            array_values(
                array_filter(
                    $installments,
                    static fn(array $installment): bool =>
                        !empty($installment['pay_on_save'])
                )
            );


        if (empty($installmentsToPay)) {
            return;
        }


        /*
         * Recuperar as parcelas recém-gravadas para obter
         * seus IDs reais no banco.
         */
        $createdInstallments =
            $this->purchaseInstallmentsRepository
            ->getByPurchaseDocumentId(
                $purchaseDocumentId
            );


        if (empty($createdInstallments)) {

            throw new RuntimeException(
                'Não foi possível recuperar as parcelas para registrar a baixa automática.'
            );
        }


        /*
         * Indexar por número da parcela para realizar
         * uma associação determinística.
         */
        $createdByNumber = [];


        foreach ($createdInstallments as $createdInstallment) {

            $number =
                (int) (
                    $createdInstallment['installment_number']
                    ?? 0
                );


            if ($number > 0) {

                $createdByNumber[$number] =
                    $createdInstallment;
            }
        }


        $paymentService =
            new PurchaseInstallmentPaymentService();


        foreach ($installmentsToPay as $installment) {

            $installmentNumber =
                (int) (
                    $installment['installment_number']
                    ?? 0
                );


            $createdInstallment =
                $createdByNumber[$installmentNumber]
                ?? null;


            $installmentId =
                (int) (
                    $createdInstallment['id']
                    ?? 0
                );


            if ($installmentId <= 0) {

                throw new RuntimeException(
                    "Não foi possível localizar a parcela {$installmentNumber} para registrar a baixa automática."
                );
            }


            /*
             * A baixa automática liquida integralmente
             * o principal da parcela.
             *
             * Juros, multa e desconto permanecem zerados;
             * se houver algum desses valores, a baixa deve ser
             * realizada posteriormente pela tela de pagamento.
             */
            $paymentService->createPayment([

                'adms_daman_purchase_installment_id' =>
                    $installmentId,

                'payment_date' =>
                    $paymentDate,

                'principal_amount' =>
                    (string) $installment['original_amount'],

                'interest_amount' =>
                    '0.00',

                'penalty_amount' =>
                    '0.00',

                'discount_amount' =>
                    '0.00',

                'observation' =>
                    $observation,

                'created_by' =>
                    $createdBy,
            ]);
        }
    }


    /**
     * Encerrar o lançamento quando todas as parcelas estiverem
     * efetivamente quitadas.
     *
     * AP (Permuta) não é considerada quitada automaticamente;
     * portanto mantém o documento em aberto.
     */
    private function closePurchaseDocumentIfFullyPaid(
        int $purchaseDocumentId
    ): void {

        $installments =
            $this->purchaseInstallmentsRepository
            ->getByPurchaseDocumentId(
                $purchaseDocumentId
            );


        if (empty($installments)) {
            return;
        }


        foreach ($installments as $installment) {

            if (
                ($installment['status'] ?? '')
                !== 'OK'
            ) {
                return;
            }
        }


        $updated =
            $this->purchaseDocumentsRepository
            ->updateStatus(
                $purchaseDocumentId,
                'closed'
            );


        if (!$updated) {

            throw new RuntimeException(
                'Não foi possível encerrar o lançamento após as baixas automáticas.'
            );
        }
    }


    /**
     * Confirmar o parcelamento de um lançamento que estava
     * aguardando boletos / vencimentos definitivos.
     *
     * O processo é transacional:
     *
     * - valida o lançamento;
     * - valida e prepara as parcelas;
     * - grava as parcelas;
     * - vincula a condição de pagamento;
     * - altera payment_schedule_status para confirmed.
     *
     * Se qualquer etapa falhar, nenhuma alteração é persistida.
     *
     * @param array $data Dados enviados pelo formulário.
     *
     * @return int ID do lançamento confirmado.
     *
     * @throws RuntimeException
     * @throws Throwable
     */
    public function confirmPaymentSchedule(array $data): int
    {
        $purchaseDocumentId =
            (int) (
                $data['adms_daman_purchase_document_id']
                ?? 0
            );

        $paymentMethodId =
            (int) (
                $data['adms_daman_payment_method_id']
                ?? 0
            );


        /*
         * Usuário que está confirmando as parcelas.
         *
         * Este valor deve vir da Controller,
         * preenchido a partir da sessão autenticada.
         */
        $createdBy =
            (int) (
                $data['created_by']
                ?? 0
            );


        if ($purchaseDocumentId <= 0) {

            throw new RuntimeException(
                'Lançamento financeiro inválido.'
            );
        }


        if ($paymentMethodId <= 0) {

            throw new RuntimeException(
                'Informe a condição de pagamento.'
            );
        }


        if ($createdBy <= 0) {

            throw new RuntimeException(
                'Usuário responsável pela confirmação inválido.'
            );
        }


        $connection =
            $this->getConnection();


        try {

            /*
            * =====================================================
            * INICIAR TRANSAÇÃO
            * =====================================================
            */
            $connection->beginTransaction();


            /*
            * =====================================================
            * RECUPERAR LANÇAMENTO
            * =====================================================
            */
            $purchaseDocument =
                $this->purchaseDocumentsRepository
                ->getById(
                    $purchaseDocumentId
                );


            if (!$purchaseDocument) {

                throw new RuntimeException(
                    'Lançamento financeiro não encontrado.'
                );
            }


            /*
            * Somente um lançamento FB pode passar por este fluxo.
            */
            if (
                (
                    $purchaseDocument['payment_schedule_status']
                    ?? 'confirmed'
                ) !== 'pending'
            ) {

                throw new RuntimeException(
                    'As parcelas deste lançamento já foram confirmadas.'
                );
            }


            /*
            * =====================================================
            * PROTEGER CONTRA PARCELAS JÁ EXISTENTES
            * =====================================================
            */
            $existingInstallments =
                $this->purchaseInstallmentsRepository
                ->getByPurchaseDocumentId(
                    $purchaseDocumentId
                );


            if (!empty($existingInstallments)) {

                throw new RuntimeException(
                    'Este lançamento já possui parcelas cadastradas.'
                );
            }


            /*
            * =====================================================
            * PREPARAR PARCELAS
            * =====================================================
            *
            * AV, AT e ON são calculados dinamicamente conforme
            * a data. Neste fluxo, não permitimos que o POST
            * transforme diretamente uma parcela em OK ou AP.
            */
            $installmentsInput =
                $data['installments']
                ?? [];


            foreach (
                $installmentsInput
                as &$installment
            ) {

                $installment['status'] =
                    'AV';
            }

            unset($installment);


            $installments =
                $this->prepareInstallments(
                    $installmentsInput,
                    (string) (
                        $purchaseDocument['total_value']
                        ?? '0'
                    )
                );


            /*
            * =====================================================
            * GRAVAR PARCELAS DEFINITIVAS
            * =====================================================
            */
            $this->purchaseInstallmentsRepository
                ->createMany(
                    $purchaseDocumentId,
                    $installments
                );


            /*
            * =====================================================
            * BAIXAR PARCELAS MARCADAS NA CONFIRMAÇÃO
            * =====================================================
            *
            * Aqui a baixa acontece quando o FB é confirmado.
            *
            * A data financeira utilizada é a data atual,
            * pois o pagamento está sendo registrado agora,
            * e não no momento original da compra.
            */
            $this->createPaymentsOnSave(
                $purchaseDocumentId,
                $installments,
                $createdBy,
                (new \DateTimeImmutable('today'))
                    ->format('Y-m-d'),
                'Baixa automática registrada no momento da confirmação das parcelas.'
            );


            /*
            * =====================================================
            * CONFIRMAR CRONOGRAMA DO DOCUMENTO
            * =====================================================
            */
            $scheduleConfirmed =
                $this->purchaseDocumentsRepository
                ->confirmPaymentSchedule(
                    $purchaseDocumentId,
                    $paymentMethodId
                );


            if (!$scheduleConfirmed) {

                throw new RuntimeException(
                    'Não foi possível confirmar as parcelas do lançamento.'
                );
            }


            /*
             * Se todas as parcelas tiverem sido baixadas
             * durante a confirmação, encerrar também
             * o lançamento financeiro.
             */
            $this->closePurchaseDocumentIfFullyPaid(
                $purchaseDocumentId
            );


            /*
            * =====================================================
            * CONFIRMAR TRANSAÇÃO
            * =====================================================
            */
            $connection->commit();


            return $purchaseDocumentId;
        } catch (Throwable $err) {

            if ($connection->inTransaction()) {

                $connection->rollBack();
            }


            GenerateLog::generateLog(
                'error',
                'Erro ao confirmar parcelamento do lançamento financeiro.',
                [
                    'purchase_document_id' =>
                    $purchaseDocumentId,

                    'payment_method_id' =>
                    $paymentMethodId,

                    'error' =>
                    $err->getMessage(),
                ]
            );


            throw $err;
        }
    }



    /**
     * Atualizar um lançamento financeiro já existente.
     *
     * Nesta etapa também é permitido reorganizar parcelas
     * que ainda não possuem histórico financeiro.
     *
     * Regras principais:
     *
     * - parcelas já movimentadas nunca são alteradas/excluídas;
     * - parcelas sem histórico podem ser alteradas ou excluídas;
     * - novas parcelas podem ser incluídas;
     * - números das parcelas existentes são preservados;
     * - novas parcelas recebem automaticamente o menor número
     *   positivo disponível;
     * - um número liberado por uma exclusão pode ser reutilizado
     *   por uma nova parcela na mesma edição;
     * - a soma final das parcelas deve ser exatamente igual
     *   ao valor oficial do documento.
     */
    public function updatePurchaseDocument(array $data): int
    {
        $purchaseDocumentId =
            (int) (
                $data['adms_daman_purchase_document_id']
                ?? 0
            );


        $buyerId =
            (int) (
                $data['adms_daman_user_id']
                ?? 0
            );


        $updatedBy =
            (int) (
                $data['updated_by']
                ?? 0
            );


        $purchaseDate =
            trim(
                (string) (
                    $data['purchase_date']
                    ?? ''
                )
            );


        $observation =
            trim(
                (string) (
                    $data['observation']
                    ?? ''
                )
            );


        if ($purchaseDocumentId <= 0) {

            throw new RuntimeException(
                'Lançamento financeiro inválido.'
            );
        }


        if ($buyerId <= 0) {

            throw new RuntimeException(
                'Informe o comprador responsável.'
            );
        }


        if ($updatedBy <= 0) {

            throw new RuntimeException(
                'Usuário responsável pela alteração inválido.'
            );
        }


        if (!$this->isValidDate($purchaseDate)) {

            throw new RuntimeException(
                'Informe uma data de compra válida.'
            );
        }


        $connection =
            $this->getConnection();


        $transactionStarted =
            false;


        try {

            if (!$connection->inTransaction()) {

                $connection->beginTransaction();

                $transactionStarted =
                    true;
            }


            /*
             * =================================================
             * LANÇAMENTO OFICIAL
             * =================================================
             */
            $purchaseDocument =
                $this->purchaseDocumentsRepository
                    ->getById(
                        $purchaseDocumentId
                    );


            if (!$purchaseDocument) {

                throw new RuntimeException(
                    'Lançamento financeiro não encontrado.'
                );
            }


            /*
             * Um lançamento FB continua usando o fluxo
             * específico de confirmação de parcelas.
             *
             * Aqui só permitimos reorganizar parcelas quando
             * o parcelamento já está confirmado.
             */
            $paymentScheduleStatus =
                $purchaseDocument['payment_schedule_status']
                ?? 'confirmed';


            /*
             * =================================================
             * PARCELAS EXISTENTES
             * =================================================
             */
            $existingInstallments =
                $this->purchaseInstallmentsRepository
                    ->getByPurchaseDocumentIds(
                        [
                            $purchaseDocumentId
                        ]
                    );


            $existingById = [];

            $installmentIds = [];


            foreach (
                $existingInstallments
                as $existingInstallment
            ) {

                $installmentId =
                    (int) (
                        $existingInstallment['id']
                        ?? 0
                    );


                if ($installmentId <= 0) {
                    continue;
                }


                $existingById[$installmentId] =
                    $existingInstallment;


                $installmentIds[] =
                    $installmentId;
            }


            /*
             * =================================================
             * HISTÓRICO FINANCEIRO
             * =================================================
             */
            $paymentsByInstallment = [];


            if (!empty($installmentIds)) {

                $paymentsRepository =
                    new PurchaseInstallmentPaymentsRepository();


                $payments =
                    $paymentsRepository
                        ->getByInstallmentIds(
                            $installmentIds
                        );


                foreach ($payments as $payment) {

                    $installmentId =
                        (int) (
                            $payment['adms_daman_purchase_installment_id']
                            ?? 0
                        );


                    if ($installmentId <= 0) {
                        continue;
                    }


                    $paymentsByInstallment[$installmentId][] =
                        $payment;
                }
            }


            /*
             * =================================================
             * POST DAS PARCELAS
             * =================================================
             */
            $installmentsInput =
                $data['installments']
                ?? [];


            if (!is_array($installmentsInput)) {

                throw new RuntimeException(
                    'Dados das parcelas inválidos.'
                );
            }


            if (
                $paymentScheduleStatus === 'pending'
                &&
                !empty($installmentsInput)
            ) {

                throw new RuntimeException(
                    'Este lançamento ainda está como FB. '
                    . 'Confirme as parcelas pelo fluxo de confirmação antes de editá-las.'
                );
            }


            /*
             * Separar parcelas existentes das novas.
             */
            $submittedExistingById = [];

            $newInstallmentsInput = [];


            foreach ($installmentsInput as $installmentInput) {

                if (!is_array($installmentInput)) {
                    continue;
                }


                $installmentId =
                    (int) (
                        $installmentInput['id']
                        ?? 0
                    );


                if ($installmentId > 0) {

                    if (!isset($existingById[$installmentId])) {

                        throw new RuntimeException(
                            'Foi informada uma parcela que não pertence a este lançamento.'
                        );
                    }


                    if (isset($submittedExistingById[$installmentId])) {

                        throw new RuntimeException(
                            'Parcela existente informada mais de uma vez.'
                        );
                    }


                    $submittedExistingById[$installmentId] =
                        $installmentInput;


                    continue;
                }


                $newInstallmentsInput[] =
                    $installmentInput;
            }


            /*
             * =================================================
             * PREPARAR EXISTENTES
             * =================================================
             */
            $preparedUpdates = [];

            $installmentIdsToDelete = [];

            $occupiedNumbers = [];

            $totalInstallmentsCents = 0;


            foreach (
                $existingById
                as $installmentId => $existingInstallment
            ) {

                $existingStatus =
                    strtoupper(
                        trim(
                            (string) (
                                $existingInstallment['status']
                                ?? 'AV'
                            )
                        )
                    );


                $hasPaymentHistory =
                    !empty(
                        $paymentsByInstallment[$installmentId]
                        ?? []
                    );


                /*
                 * Uma parcela armazenada como OK também fica
                 * protegida mesmo em eventual registro legado
                 * sem histórico correspondente.
                 */
                $isLocked =
                    $hasPaymentHistory
                    ||
                    $existingStatus === 'OK';


                $existingAmountCents =
                    (int) round(
                        (float) (
                            $existingInstallment['original_amount']
                            ?? 0
                        )
                        * 100
                    );


                if ($existingAmountCents <= 0) {

                    throw new RuntimeException(
                        "A parcela {$installmentId} possui valor original inválido."
                    );
                }


                $existingNumber =
                    (int) (
                        $existingInstallment['installment_number']
                        ?? 0
                    );


                if ($existingNumber <= 0) {

                    throw new RuntimeException(
                        "A parcela {$installmentId} possui numeração inválida."
                    );
                }


                /*
                 * Parcela omitida do POST significa exclusão.
                 */
                if (
                    !isset(
                        $submittedExistingById[$installmentId]
                    )
                ) {

                    if ($isLocked) {

                        throw new RuntimeException(
                            'Uma parcela com histórico financeiro não pode ser excluída.'
                        );
                    }


                    $installmentIdsToDelete[] =
                        $installmentId;


                    /*
                     * O número NÃO entra em occupiedNumbers,
                     * portanto poderá ser reaproveitado por
                     * uma nova parcela nesta mesma edição.
                     */
                    continue;
                }


                $installmentInput =
                    $submittedExistingById[$installmentId];


                /*
                 * Toda parcela existente mantida preserva
                 * seu número atual.
                 */
                $occupiedNumbers[$existingNumber] =
                    true;


                if ($isLocked) {

                    /*
                     * Blindagem contra manipulação do HTML.
                     */
                    $submittedAmountCents =
                        $this->moneyToCents(
                            (string) (
                                $installmentInput['original_amount']
                                ?? ''
                            )
                        );


                    $submittedDueDate =
                        !empty(
                            $installmentInput['due_date']
                        )
                            ? trim(
                                (string) $installmentInput['due_date']
                            )
                            : null;


                    $expectedNature =
                        $existingStatus === 'AP'
                            ? 'AP'
                            : 'NORMAL';


                    $submittedNature =
                        strtoupper(
                            trim(
                                (string) (
                                    $installmentInput['nature']
                                    ?? ''
                                )
                            )
                        );


                    $existingDueDate =
                        !empty(
                            $existingInstallment['due_date']
                        )
                            ? (string) $existingInstallment['due_date']
                            : null;


                    if (
                        $submittedAmountCents
                            !== $existingAmountCents
                        ||
                        $submittedDueDate
                            !== $existingDueDate
                        ||
                        $submittedNature
                            !== $expectedNature
                    ) {

                        throw new RuntimeException(
                            'Uma parcela com histórico financeiro não pode ser alterada.'
                        );
                    }


                    $totalInstallmentsCents +=
                        $existingAmountCents;


                    continue;
                }


                $prepared =
                    $this->prepareEditableInstallment(
                        $installmentInput
                    );


                $preparedUpdates[] = [
                    'id' =>
                        $installmentId,

                    'due_date' =>
                        $prepared['due_date'],

                    'original_amount' =>
                        $prepared['original_amount'],

                    'status' =>
                        $prepared['status'],
                ];


                $totalInstallmentsCents +=
                    $prepared['amount_cents'];
            }


            /*
             * =================================================
             * PREPARAR NOVAS PARCELAS
             * =================================================
             */
            $preparedCreates = [];


            foreach (
                $newInstallmentsInput
                as $newInstallmentInput
            ) {

                $prepared =
                    $this->prepareEditableInstallment(
                        $newInstallmentInput
                    );


                /*
                 * Menor número positivo ainda disponível.
                 *
                 * Exemplo:
                 * existentes mantidas: 1 e 3
                 * número 2 foi excluído
                 * nova parcela recebe 2.
                 */
                $installmentNumber =
                    1;


                while (
                    isset(
                        $occupiedNumbers[$installmentNumber]
                    )
                ) {

                    $installmentNumber++;
                }


                $occupiedNumbers[$installmentNumber] =
                    true;


                $preparedCreates[] = [
                    'installment_number' =>
                        $installmentNumber,

                    'due_date' =>
                        $prepared['due_date'],

                    'original_amount' =>
                        $prepared['original_amount'],

                    'status' =>
                        $prepared['status'],

                    'observation' =>
                        null,
                ];


                $totalInstallmentsCents +=
                    $prepared['amount_cents'];
            }


            /*
             * Parcelamento confirmado não pode terminar
             * completamente sem parcelas.
             */
            $finalInstallmentCount =
                count($existingById)
                - count($installmentIdsToDelete)
                + count($preparedCreates);


            if (
                $paymentScheduleStatus === 'confirmed'
                &&
                $finalInstallmentCount <= 0
            ) {

                throw new RuntimeException(
                    'O lançamento precisa possuir ao menos uma parcela.'
                );
            }


            /*
             * =================================================
             * CONFERIR TOTAL
             * =================================================
             */
            $documentTotalCents =
                (int) round(
                    (float) (
                        $purchaseDocument['total_value']
                        ?? 0
                    )
                    * 100
                );


            if ($documentTotalCents <= 0) {

                throw new RuntimeException(
                    'O lançamento possui valor total inválido.'
                );
            }


            if (
                $paymentScheduleStatus === 'confirmed'
                &&
                $totalInstallmentsCents
                    !== $documentTotalCents
            ) {

                $difference =
                    abs(
                        $documentTotalCents
                        - $totalInstallmentsCents
                    );


                throw new RuntimeException(
                    'A soma das parcelas deve continuar igual '
                    . 'ao valor total do documento. Diferença: R$ '
                    . number_format(
                        $difference / 100,
                        2,
                        ',',
                        '.'
                    )
                    . '.'
                );
            }


            /*
             * =================================================
             * DADOS ADMINISTRATIVOS
             * =================================================
             */
            $documentUpdated =
                $this->purchaseDocumentsRepository
                    ->updateEditableData(
                        $purchaseDocumentId,
                        $buyerId,
                        $purchaseDate,
                        $observation !== ''
                            ? $observation
                            : null
                    );


            if (!$documentUpdated) {

                throw new RuntimeException(
                    'Não foi possível atualizar os dados do lançamento.'
                );
            }


            /*
             * =================================================
             * EXCLUIR PARCELAS LIVRES
             * =================================================
             *
             * A exclusão acontece antes da criação para liberar
             * um installment_number que poderá ser reutilizado.
             */
            foreach ($installmentIdsToDelete as $installmentId) {

                $deleted =
                    $this->purchaseInstallmentsRepository
                        ->deleteById(
                            $installmentId,
                            $purchaseDocumentId
                        );


                if (!$deleted) {

                    throw new RuntimeException(
                        'Não foi possível excluir uma das parcelas selecionadas.'
                    );
                }
            }


            /*
             * =================================================
             * ATUALIZAR EXISTENTES LIVRES
             * =================================================
             */
            foreach ($preparedUpdates as $preparedUpdate) {

                $updated =
                    $this->purchaseInstallmentsRepository
                        ->updateEditableData(
                            (int) $preparedUpdate['id'],
                            $preparedUpdate['due_date'],
                            (string) $preparedUpdate['original_amount'],
                            (string) $preparedUpdate['status']
                        );


                if (!$updated) {

                    throw new RuntimeException(
                        'Não foi possível atualizar uma das parcelas do lançamento.'
                    );
                }
            }


            /*
             * =================================================
             * CRIAR NOVAS
             * =================================================
             */
            if (!empty($preparedCreates)) {

                $created =
                    $this->purchaseInstallmentsRepository
                        ->createMany(
                            $purchaseDocumentId,
                            $preparedCreates
                        );


                if (!$created) {

                    throw new RuntimeException(
                        'Não foi possível criar as novas parcelas.'
                    );
                }
            }


            if ($transactionStarted) {

                $connection->commit();
            }


            return $purchaseDocumentId;

        } catch (Throwable $err) {

            if (
                $transactionStarted
                &&
                $connection->inTransaction()
            ) {

                $connection->rollBack();
            }


            GenerateLog::generateLog(
                'error',
                'Erro ao editar lançamento financeiro.',
                [
                    'purchase_document_id' =>
                        $purchaseDocumentId,

                    'updated_by' =>
                        $updatedBy,

                    'error' =>
                        $err->getMessage(),
                ]
            );


            throw $err;
        }
    }


    /**
     * Preparar uma parcela que ainda pode ser modificada.
     *
     * Retorna o valor em centavos para conferência do total
     * e também o decimal SQL para persistência.
     */
    private function prepareEditableInstallment(
        array $installmentInput
    ): array {

        $nature =
            strtoupper(
                trim(
                    (string) (
                        $installmentInput['nature']
                        ?? 'NORMAL'
                    )
                )
            );


        if (
            !in_array(
                $nature,
                [
                    'NORMAL',
                    'AP',
                ],
                true
            )
        ) {

            throw new RuntimeException(
                'Natureza da parcela inválida.'
            );
        }


        $amountCents =
            $this->moneyToCents(
                (string) (
                    $installmentInput['original_amount']
                    ?? '0'
                )
            );


        if ($amountCents <= 0) {

            throw new RuntimeException(
                'O valor da parcela deve ser maior que zero.'
            );
        }


        $dueDate =
            !empty(
                $installmentInput['due_date']
            )
                ? trim(
                    (string) $installmentInput['due_date']
                )
                : null;


        if ($nature === 'NORMAL') {

            if (
                $dueDate === null
                ||
                !$this->isValidDate($dueDate)
            ) {

                throw new RuntimeException(
                    'Toda parcela normal precisa possuir um vencimento válido.'
                );
            }


            $status =
                'AV';

        } else {

            $dueDate =
                null;

            $status =
                'AP';
        }


        return [
            'amount_cents' =>
                $amountCents,

            'original_amount' =>
                number_format(
                    $amountCents / 100,
                    2,
                    '.',
                    ''
                ),

            'due_date' =>
                $dueDate,

            'status' =>
                $status,
        ];
    }


    /**
     * Excluir um lançamento financeiro ainda sem movimentação.
     *
     * Regra definitiva:
     *
     * - qualquer pagamento ativo bloqueia;
     * - qualquer pagamento estornado também bloqueia;
     * - parcela armazenada como OK bloqueia;
     * - documento fechado bloqueia;
     * - rateio NÃO é editado: quando estiver incorreto,
     *   o lançamento deve ser excluído e recriado antes
     *   de existir movimentação financeira.
     *
     * Para NF-e:
     * a nota NÃO é excluída nem desconferida.
     * Após remover o lançamento, ela volta a poder ser lançada.
     *
     * @return array Dados necessários para o redirecionamento.
     */
    public function deletePurchaseDocument(
        array $data
    ): array {

        $purchaseDocumentId =
            (int) (
                $data['adms_daman_purchase_document_id']
                ?? 0
            );


        $deletedBy =
            (int) (
                $data['deleted_by']
                ?? 0
            );


        if ($purchaseDocumentId <= 0) {

            throw new RuntimeException(
                'Lançamento financeiro inválido.'
            );
        }


        if ($deletedBy <= 0) {

            throw new RuntimeException(
                'Usuário responsável pela exclusão inválido.'
            );
        }


        $connection =
            $this->getConnection();


        $transactionStarted =
            false;


        try {

            if (!$connection->inTransaction()) {

                $connection->beginTransaction();

                $transactionStarted =
                    true;
            }


            /*
             * =================================================
             * RECUPERAR LANÇAMENTO
             * =================================================
             */
            $purchaseDocument =
                $this->purchaseDocumentsRepository
                    ->getById(
                        $purchaseDocumentId
                    );


            if (!$purchaseDocument) {

                throw new RuntimeException(
                    'Lançamento financeiro não encontrado.'
                );
            }


            /*
             * Documento encerrado é tratado como movimentado,
             * inclusive para proteger registros legados.
             */
            if (
                (
                    $purchaseDocument['status']
                    ?? 'open'
                ) === 'closed'
            ) {

                throw new RuntimeException(
                    'Não é possível excluir este lançamento, '
                    . 'pois ele já possui histórico financeiro.'
                );
            }


            /*
             * =================================================
             * RECUPERAR PARCELAS
             * =================================================
             */
            $installments =
                $this->purchaseInstallmentsRepository
                    ->getByPurchaseDocumentIds(
                        [
                            $purchaseDocumentId
                        ]
                    );


            $installmentIds = [];


            foreach ($installments as $installment) {

                $installmentId =
                    (int) (
                        $installment['id']
                        ?? 0
                    );


                if ($installmentId > 0) {

                    $installmentIds[] =
                        $installmentId;
                }


                /*
                 * Uma parcela OK também protege o lançamento
                 * mesmo se existir algum registro legado
                 * inconsistente sem pagamento correspondente.
                 */
                if (
                    strtoupper(
                        (string) (
                            $installment['status']
                            ?? ''
                        )
                    ) === 'OK'
                ) {

                    throw new RuntimeException(
                        'Não é possível excluir este lançamento, '
                        . 'pois ele já possui histórico financeiro.'
                    );
                }
            }


            /*
             * =================================================
             * VERIFICAR PAGAMENTOS ATIVOS OU ESTORNADOS
             * =================================================
             *
             * getByInstallmentIds() devolve o histórico completo.
             * Portanto pagamento estornado também impede exclusão.
             */
            if (!empty($installmentIds)) {

                $paymentsRepository =
                    new PurchaseInstallmentPaymentsRepository();


                $payments =
                    $paymentsRepository
                        ->getByInstallmentIds(
                            $installmentIds
                        );


                if (!empty($payments)) {

                    throw new RuntimeException(
                        'Não é possível excluir este lançamento, '
                        . 'pois ele já possui histórico financeiro.'
                    );
                }
            }


            /*
             * =================================================
             * DADOS DE AUDITORIA ANTES DA EXCLUSÃO
             * =================================================
             *
             * O log é gerado ANTES do DELETE para preservar
             * os principais dados do lançamento.
             */
            $auditContext = [
                'purchase_document_id' =>
                    $purchaseDocumentId,

                'document_origin' =>
                    $purchaseDocument['document_origin']
                    ?? null,

                'document_type' =>
                    $purchaseDocument['document_type']
                    ?? null,

                'document_number' =>
                    $purchaseDocument['document_number']
                    ?? null,

                'nfe_id' =>
                    $purchaseDocument['adms_daman_nfe_id']
                    ?? null,

                'total_value' =>
                    $purchaseDocument['total_value']
                    ?? null,

                'purchase_date' =>
                    $purchaseDocument['purchase_date']
                    ?? null,

                'deleted_by' =>
                    $deletedBy,
            ];


            /*
             * O projeto utiliza GenerateLog principalmente com
             * nível "error". Tentamos registrar a auditoria como
             * "info"; caso a implementação atual do helper não
             * aceite esse nível, fazemos fallback para "error"
             * sem perder o registro antes da exclusão.
             */
            try {

                GenerateLog::generateLog(
                    'info',
                    'Auditoria: exclusão de lançamento financeiro autorizada.',
                    $auditContext
                );

            } catch (Throwable $logErr) {

                $auditContext['audit_log_fallback_error'] =
                    $logErr->getMessage();


                GenerateLog::generateLog(
                    'error',
                    'Auditoria: exclusão de lançamento financeiro autorizada.',
                    $auditContext
                );
            }


            /*
             * =================================================
             * EXCLUIR LANÇAMENTO
             * =================================================
             *
             * Parcelas e rateios são removidos pelos FKs
             * configurados com ON DELETE CASCADE.
             *
             * A NF-e permanece intacta e conferida.
             */
            $deleted =
                $this->purchaseDocumentsRepository
                    ->deleteById(
                        $purchaseDocumentId
                    );


            if (!$deleted) {

                throw new RuntimeException(
                    'Não foi possível excluir o lançamento financeiro.'
                );
            }


            if ($transactionStarted) {

                $connection->commit();
            }


            return [
                'purchase_document_id' =>
                    $purchaseDocumentId,

                'document_origin' =>
                    $purchaseDocument['document_origin']
                    ?? 'MANUAL',

                'nfe_id' =>
                    (int) (
                        $purchaseDocument['adms_daman_nfe_id']
                        ?? 0
                    ),
            ];

        } catch (Throwable $err) {

            if (
                $transactionStarted
                &&
                $connection->inTransaction()
            ) {

                $connection->rollBack();
            }


            GenerateLog::generateLog(
                'error',
                'Erro ao excluir lançamento financeiro.',
                [
                    'purchase_document_id' =>
                        $purchaseDocumentId,

                    'deleted_by' =>
                        $deletedBy,

                    'error' =>
                        $err->getMessage(),
                ]
            );


            throw $err;
        }
    }

    /**
     * Validar uma data no formato Y-m-d.
     */
    private function isValidDate(string $date): bool
    {
        $parsed =
            \DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $date
            );


        return
            $parsed !== false
            &&
            $parsed->format('Y-m-d')
                === $date;
    }
}
