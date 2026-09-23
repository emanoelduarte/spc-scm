<?php

use App\admsDaman\Helpers\CSRFHelper;
use App\admsDaman\Models\Repository\FinancialPaymentMethodsRepository;

$purchaseDocument =
    $this->data['purchaseDocument'] ?? [];

$installments =
    $this->data['installments'] ?? [];

$paymentMethods =
    $this->data['getAllPaymentSelect']
    ?? [];

$paymentsByInstallment =
    $this->data['paymentsByInstallment']
    ?? [];


/*
 * Formas do pagamento efetivamente realizado.
 *
 * Carregamos este catálogo aqui para manter a alteração
 * restrita aos arquivos enviados e não substituir Controllers
 * que podem possuir mudanças mais recentes em produção.
 */
$financialPaymentMethodsRepository =
    new FinancialPaymentMethodsRepository();

$financialPaymentMethods =
    $financialPaymentMethodsRepository
        ->getAllActiveSelect();


/*
 * Exclusão integral só é permitida quando o lançamento
 * nunca teve movimentação financeira.
 *
 * Esta informação controla apenas a interface.
 * O Service valida novamente antes do DELETE.
 */
$canDeletePurchaseDocument =
    !empty(
        $this->data['canDeletePurchaseDocument']
    );

$paymentCsrfToken =
    CSRFHelper::generateCSRFToken(
        'form_purchase_installment_payment'
    );

$reversePaymentCsrfToken =
    CSRFHelper::generateCSRFToken(
        'form_reverse_purchase_installment_payment'
    );

/*
 * Verificar se o lançamento ainda está aguardando
 * confirmação dos boletos / parcelas.
 */
$isPaymentSchedulePending =
    (
        $purchaseDocument['payment_schedule_status']
        ?? 'confirmed'
    ) === 'pending';

/*
 * Rateio do lançamento entre obras.
 *
 * Todo lançamento novo deverá possuir pelo menos
 * uma alocação:
 *
 * 1 alocação  = uma única obra
 * 2+ alocações = lançamento rateado
 */
$allocations =
    $this->data['allocations'] ?? [];


$isProrated =
    count($allocations) > 1;

/*
 * Identificar a origem do lançamento.
 */
$isManual =
    ($purchaseDocument['document_origin'] ?? '')
    === 'MANUAL';


/*
 * ============================================================
 * STATUS VISUAL DA PARCELA
 * ============================================================
 *
 * OK e AP são status manuais e devem ser respeitados.
 *
 * AV, AT e ON são calculados conforme a data de vencimento:
 *
 * AV = vence em mais de 7 dias
 * AT = vence hoje ou nos próximos 7 dias
 * ON = vencida e ainda em aberto
 */
$getInstallmentStatus = function (array $installment): string {

    $storedStatus =
        strtoupper(
            (string) ($installment['status'] ?? 'AV')
        );


    /*
     * Status manuais.
     */
    if (
        $storedStatus === 'OK'
        ||
        $storedStatus === 'AP'
    ) {
        return $storedStatus;
    }


    /*
     * Sem vencimento.
     *
     * Normalmente ocorrerá apenas em Permuta,
     * mas mantemos uma proteção.
     */
    if (empty($installment['due_date'])) {
        return $storedStatus;
    }


    $today =
        new DateTimeImmutable('today');

    $dueDate =
        new DateTimeImmutable(
            $installment['due_date']
        );


    /*
     * Vencida.
     */
    if ($dueDate < $today) {
        return 'ON';
    }


    /*
     * Quantidade de dias até o vencimento.
     */
    $days =
        (int) $today
            ->diff($dueDate)
            ->days;


    /*
     * Hoje até 7 dias.
     */
    if ($days <= 7) {
        return 'AT';
    }


    return 'AV';
};


/*
 * ============================================================
 * CONFIGURAÇÃO VISUAL DOS STATUS
 * ============================================================
 */
$statusMeta = [

    'AV' => [
        'label' => 'A Vencer',
        'class' => 'bg-primary',
        'icon' => 'fa-clock',
    ],

    'AT' => [
        'label' => 'Atenção',
        'class' => 'bg-warning text-dark',
        'icon' => 'fa-triangle-exclamation',
    ],

    'ON' => [
        'label' => 'Em Aberto / Vencido',
        'class' => 'bg-danger',
        'icon' => 'fa-circle-exclamation',
    ],

    'OK' => [
        'label' => 'Pago',
        'class' => 'bg-success',
        'icon' => 'fa-circle-check',
    ],

    'AP' => [
        'label' => 'Permuta',
        'class' => 'bg-info text-dark',
        'icon' => 'fa-right-left',
    ],
];


/*
 * ============================================================
 * TOTAL DAS PARCELAS
 * ============================================================
 */
$installmentsTotal = 0;

foreach ($installments as $installment) {

    $installmentsTotal +=
        (float) (
            $installment['original_amount']
            ?? 0
        );
}


$nfeTotal =
    (float) (
        $purchaseDocument['total_value']
        ?? 0
    );

/*
 * Total das apropriações entre obras.
 *
 * Utilizado apenas para apresentação e conferência
 * nesta tela. A validação definitiva ocorreu no Service
 * durante a criação do lançamento.
 */
$allocationsTotal = 0;

foreach ($allocations as $allocation) {

    $allocationsTotal +=
        (float) (
            $allocation['allocated_amount']
            ?? 0
        );
}


$difference =
    $nfeTotal
    - $installmentsTotal;

?>


<div class="container-fluid px-4">


    <!-- ====================================================== -->
    <!-- TÍTULO / BREADCRUMB                                    -->
    <!-- ====================================================== -->

    <div
        class="mb-1 d-flex flex-column flex-sm-row gap-2">

        <div>

            <h2 class="mt-3 mb-1">
                Lançamento de Compra
            </h2>

            <p class="text-muted">
                <?= $isManual
                    ? 'Detalhes do lançamento financeiro da compra avulsa'
                    : 'Detalhes do lançamento financeiro da NF-e'; ?>
            </p>

        </div>


        <ol
            class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">

                <a
                    class="text-decoration-none"
                    href="<?= $_ENV['URL_ADM']; ?>dashboard">

                    Dashboard

                </a>

            </li>

            <?php if ($isManual): ?>

                <li class="breadcrumb-item">

                    <a
                        class="text-decoration-none"
                        href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents">

                        Compras

                    </a>

                </li>

            <?php else: ?>

                <li class="breadcrumb-item">

                    <a
                        class="text-decoration-none"
                        href="<?= $_ENV['URL_ADM']; ?>list-nfes">

                        NF-e Recebidas

                    </a>

                </li>

            <?php endif; ?>

            <li
                class="breadcrumb-item active"
                aria-current="page">

                Ver Lançamento

            </li>

        </ol>

    </div>


    <?php

    /*
     * Alertas gerais do sistema.
     */
    include './app/admsDaman/Views/partials/alerts.php';

    ?>


    <!-- ====================================================== -->
    <!-- DADOS DA NF-e OU COMPRA                                -->
    <!-- ====================================================== -->

    <?php if ($isManual): ?>

        <!-- Card Compra Avulsa -->

        <div class="card mb-4 border-light shadow">

            <div class="card-header d-flex align-items-center">

                <div>

                    <i class="fa-solid fa-receipt me-2"></i>

                    <strong>
                        Dados do Documento
                    </strong>

                </div>

                <span class="badge bg-secondary ms-auto">
                    Compra Avulsa
                </span>

            </div>


            <div class="card-body">

                <div class="row g-4">

                    <!-- Tipo -->
                    <div class="col-md-2">

                        <span class="text-muted d-block mb-1">
                            Tipo
                        </span>

                        <strong>

                            <?= !empty($purchaseDocument['document_type'])
                                ? htmlspecialchars(
                                    $purchaseDocument['document_type']
                                )
                                : '-'; ?>

                        </strong>

                    </div>


                    <!-- Número -->
                    <div class="col-md-2">

                        <span class="text-muted d-block mb-1">
                            Documento
                        </span>

                        <strong>

                            <?= !empty($purchaseDocument['document_number'])
                                ? htmlspecialchars(
                                    $purchaseDocument['document_number']
                                )
                                : '-'; ?>

                        </strong>

                    </div>


                    <!-- Fornecedor -->
                    <div class="col-md-4">

                        <span class="text-muted d-block mb-1">
                            Fornecedor
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $purchaseDocument['supplier_name']
                                    ?? '-'
                            ); ?>

                        </strong>


                        <div class="text-muted small mt-1">

                            CNPJ:

                            <?= htmlspecialchars(
                                $purchaseDocument['supplier_cnpj']
                                    ?? '-'
                            ); ?>

                        </div>

                    </div>


                    <!-- Data -->
                    <div class="col-md-2">

                        <span class="text-muted d-block mb-1">
                            Data do Documento
                        </span>

                        <strong>

                            <?php

                            if (
                                !empty($purchaseDocument['document_date'])
                            ) {

                                echo date(
                                    'd/m/Y',
                                    strtotime(
                                        $purchaseDocument['document_date']
                                    )
                                );
                            } else {

                                echo '-';
                            }

                            ?>

                        </strong>

                    </div>


                    <!-- Total -->
                    <div class="col-md-2 text-md-end">

                        <span class="text-muted d-block mb-1">
                            Valor Total
                        </span>

                        <strong class="fs-5 text-success">

                            R$
                            <?= number_format(
                                (float) (
                                    $purchaseDocument['total_value']
                                    ?? 0
                                ),
                                2,
                                ',',
                                '.'
                            ); ?>

                        </strong>

                    </div>

                </div>

            </div>

        </div>

    <?php else: ?>

        <!-- Seu card atual Dados da NF-e -->

        <div class="card mb-4 border-light shadow">

            <div class="card-header hstack gap-2">

                <span>

                    <i class="fa-solid fa-file-invoice me-2"></i>

                    <strong>
                        Dados da NF-e
                    </strong>

                </span>


                <span class="ms-auto">

                    <span class="badge bg-success">

                        <i class="fa-solid fa-circle-check me-1"></i>

                        Lançada

                    </span>

                </span>

            </div>


            <div class="card-body">

                <div class="row g-4">


                    <!-- Número -->
                    <div class="col-xl-2 col-md-4">

                        <span class="text-muted d-block mb-1">
                            NF-e
                        </span>

                        <strong class="fs-5">

                            <?= htmlspecialchars(
                                $purchaseDocument['nfe_number']
                                    ?? '-'
                            ); ?>

                        </strong>

                    </div>


                    <!-- Série -->
                    <div class="col-xl-1 col-md-2">

                        <span class="text-muted d-block mb-1">
                            Série
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $purchaseDocument['series']
                                    ?? '-'
                            ); ?>

                        </strong>

                    </div>


                    <!-- Fornecedor -->
                    <div class="col-xl-4 col-md-6">

                        <span class="text-muted d-block mb-1">
                            Fornecedor
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $purchaseDocument['issuer_name']
                                    ?? '-'
                            ); ?>

                        </strong>

                        <div class="small text-muted mt-1">

                            CNPJ:

                            <?= htmlspecialchars(
                                $purchaseDocument['issuer_cnpj']
                                    ?? '-'
                            ); ?>

                        </div>

                    </div>


                    <!-- Emissão -->
                    <div class="col-xl-3 col-md-6">

                        <span class="text-muted d-block mb-1">
                            Emissão
                        </span>

                        <strong>

                            <?php

                            if (
                                !empty($purchaseDocument['issue_date'])
                            ) {

                                echo date(
                                    'd/m/Y H:i',
                                    strtotime(
                                        $purchaseDocument['issue_date']
                                    )
                                );
                            } else {

                                echo '-';
                            }

                            ?>

                        </strong>

                    </div>


                    <!-- Valor -->
                    <div class="col-xl-2 col-md-6">

                        <span class="text-muted d-block mb-1">
                            Valor Total NF
                        </span>

                        <strong class="fs-4 text-success">

                            R$
                            <?= number_format(
                                $nfeTotal,
                                2,
                                ',',
                                '.'
                            ); ?>

                        </strong>

                    </div>


                    <!-- Chave -->
                    <div class="col-12">

                        <hr>

                        <span class="text-muted d-block mb-1">
                            Chave de Acesso
                        </span>

                        <span class="font-monospace small">

                            <?= htmlspecialchars(
                                $purchaseDocument['access_key']
                                    ?? '-'
                            ); ?>

                        </span>

                    </div>

                </div>

            </div>

        </div>

    <?php endif; ?>




    <!-- ====================================================== -->
    <!-- DADOS DA COMPRA                                       -->
    <!-- ====================================================== -->

    <div class="card mb-4 border-light shadow">

        <div class="card-header">

            <i class="fa-solid fa-cart-shopping me-2"></i>

            <strong>
                Dados da Compra
            </strong>

        </div>


        <div class="card-body">

            <div class="row g-4">


                <!-- Obra / Rateio -->
                <div class="col-xl-3 col-md-6">

                    <span class="text-muted d-block mb-1">
                        <?= $isProrated
                            ? 'Obras'
                            : 'Obra'; ?>
                    </span>

                    <?php if ($isProrated): ?>

                        <strong>
                            <i class="fa-solid fa-code-branch me-1"></i>

                            Rateado entre
                            <?= count($allocations); ?>
                            obras
                        </strong>

                        <div class="mt-1">

                            <span class="badge bg-info text-dark">

                                <i class="fa-solid fa-share-nodes me-1"></i>
                                Rateado

                            </span>

                        </div>

                    <?php elseif (!empty($allocations)): ?>

                        <strong>
                            <?= htmlspecialchars(
                                $allocations[0]['project_name']
                                    ?? '-'
                            ); ?>
                        </strong>

                    <?php else: ?>

                        <strong>
                            <?= htmlspecialchars(
                                $purchaseDocument['project_name']
                                    ?? '-'
                            ); ?>
                        </strong>

                    <?php endif; ?>

                </div>


                <!-- Comprador -->
                <div class="col-xl-3 col-md-6">

                    <span class="text-muted d-block mb-1">
                        Comprador
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $purchaseDocument['buyer_name']
                                ?? '-'
                        ); ?>

                    </strong>

                </div>


                <!-- Data Compra -->
                <div class="col-xl-2 col-md-6">

                    <span class="text-muted d-block mb-1">
                        Data da Compra
                    </span>

                    <strong>

                        <?php

                        if (
                            !empty($purchaseDocument['purchase_date'])
                        ) {

                            echo date(
                                'd/m/Y',
                                strtotime(
                                    $purchaseDocument['purchase_date']
                                )
                            );
                        } else {

                            echo '-';
                        }

                        ?>

                    </strong>

                </div>


                <!-- Condição -->
                <div class="col-xl-2 col-md-6">

                    <span class="text-muted d-block mb-1">
                        Condição de Pagamento
                    </span>

                    <?php if ($isPaymentSchedulePending): ?>

                        <div>

                            <span class="badge bg-warning text-dark">

                                <i class="fa-solid fa-clock me-1"></i>

                                FB

                            </span>

                        </div>

                        <small class="text-warning">
                            Falta boleto
                        </small>

                    <?php else: ?>

                        <strong>

                            <?= htmlspecialchars(
                                $purchaseDocument['payment_method_name']
                                    ?? '-'
                            ); ?>

                        </strong>

                    <?php endif; ?>

                </div>


                <!-- Situação -->
                <div class="col-xl-2 col-md-6">

                    <span class="text-muted d-block mb-1">
                        Situação
                    </span>

                    <?php

                    $documentStatus =
                        $purchaseDocument['status']
                        ?? 'open';

                    $documentStatusLabel =
                        match ($documentStatus) {

                            'closed' =>
                            'Pago',

                            'open' =>
                            'Em Aberto',

                            default =>
                            ucfirst($documentStatus),
                        };

                    $documentStatusClass =
                        match ($documentStatus) {

                            'closed' =>
                            'bg-success',

                            'open' =>
                            'bg-primary',

                            default =>
                            'bg-secondary',
                        };

                    ?>

                    <span class="badge <?= $documentStatusClass; ?>">

                        <?php if ($documentStatus === 'closed'): ?>

                            <i class="fa-solid fa-circle-check me-1"></i>

                        <?php else: ?>

                            <i class="fa-solid fa-clock me-1"></i>

                        <?php endif; ?>

                        <?= htmlspecialchars(
                            $documentStatusLabel
                        ); ?>

                    </span>

                </div>


                <!-- Criado por -->
                <div class="col-xl-3 col-md-6">

                    <span class="text-muted d-block mb-1">
                        Lançado por
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $purchaseDocument['created_by_name']
                                ?? '-'
                        ); ?>

                    </strong>

                </div>


                <!-- Data cadastro -->
                <div class="col-xl-3 col-md-6">

                    <span class="text-muted d-block mb-1">
                        Data do Lançamento
                    </span>

                    <strong>

                        <?php

                        if (
                            !empty($purchaseDocument['created_at'])
                        ) {

                            echo date(
                                'd/m/Y H:i',
                                strtotime(
                                    $purchaseDocument['created_at']
                                )
                            );
                        } else {

                            echo '-';
                        }

                        ?>

                    </strong>

                </div>

                <!-- ====================================================== -->
                <!-- RATEIO ENTRE OBRAS                                    -->
                <!-- ====================================================== -->

                <?php if ($isProrated): ?>

                    <div class="col-12">

                        <div class="border rounded p-3">

                            <div
                                class="d-flex flex-column flex-md-row
                       align-items-md-center gap-2 mb-3">

                                <div>

                                    <div class="fw-semibold">

                                        <i class="fa-solid fa-code-branch me-1"></i>

                                        Rateio entre obras

                                    </div>

                                    <div class="small text-muted">

                                        Apropriação do valor deste lançamento
                                        entre as obras participantes.

                                    </div>

                                </div>


                                <span
                                    class="badge bg-secondary
                           ms-md-auto">

                                    <?= count($allocations); ?>

                                    obras

                                </span>

                            </div>


                            <div class="table-responsive">

                                <table
                                    class="table table-sm
                           align-middle mb-0">

                                    <thead>

                                        <tr>

                                            <th>
                                                Obra
                                            </th>

                                            <th class="text-end">
                                                Valor
                                            </th>

                                            <th
                                                class="text-end"
                                                style="width: 120px;">

                                                Percentual

                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        <?php foreach (
                                            $allocations
                                            as $allocation
                                        ): ?>

                                            <?php

                                            $allocatedAmount =
                                                (float) (
                                                    $allocation['allocated_amount']
                                                    ?? 0
                                                );


                                            /*
                                            * O percentual é calculado,
                                            * nunca armazenado.
                                            */
                                            $percentage =
                                                $nfeTotal > 0
                                                ? (
                                                    $allocatedAmount
                                                    / $nfeTotal
                                                ) * 100
                                                : 0;

                                            ?>

                                            <tr>

                                                <td>

                                                    <strong>

                                                        <?= htmlspecialchars(
                                                            $allocation['project_name']
                                                                ?? '-'
                                                        ); ?>

                                                    </strong>

                                                </td>


                                                <td class="text-end">

                                                    R$

                                                    <?= number_format(
                                                        $allocatedAmount,
                                                        2,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </td>


                                                <td class="text-end">

                                                    <?= number_format(
                                                        $percentage,
                                                        2,
                                                        ',',
                                                        '.'
                                                    ); ?>%

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>


                                    <tfoot>

                                        <tr class="fw-semibold">

                                            <td>
                                                Total Rateado
                                            </td>


                                            <td class="text-end text-success">

                                                R$

                                                <?= number_format(
                                                    $allocationsTotal,
                                                    2,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>


                                            <td class="text-end text-success">

                                                <?= $nfeTotal > 0
                                                    ? number_format(
                                                        (
                                                            $allocationsTotal
                                                            / $nfeTotal
                                                        ) * 100,
                                                        2,
                                                        ',',
                                                        '.'
                                                    )
                                                    : '0,00'; ?>%

                                            </td>

                                        </tr>

                                    </tfoot>

                                </table>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>


                <!-- Observação -->
                <?php if (
                    !empty(trim(
                        (string) (
                            $purchaseDocument['observation']
                            ?? ''
                        )
                    ))
                ): ?>

                    <div class="col-12">

                        <span class="text-muted d-block mb-1">
                            Observação
                        </span>

                        <div
                            class="border rounded p-3">

                            <?= nl2br(
                                htmlspecialchars(
                                    $purchaseDocument['observation']
                                )
                            ); ?>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- PARCELAS                                               -->
    <!-- ====================================================== -->

    <div class="card mb-4 border-light shadow">

        <div class="card-header hstack gap-2">

            <span>

                <i class="fa-solid fa-calendar-days me-2"></i>

                <strong>
                    Parcelas
                </strong>

            </span>


            <span class="ms-auto">

                <?php if ($isPaymentSchedulePending): ?>

                    <div class="d-flex align-items-center gap-2">

                        <span class="badge bg-warning text-dark">

                            <i class="fa-solid fa-clock me-1"></i>

                            Parcelas a confirmar

                        </span>


                        <button
                            type="button"
                            class="btn btn-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmPaymentScheduleModal">

                            <i class="fa-solid fa-check me-1"></i>

                            Confirmar Parcelas

                        </button>

                    </div>

                <?php else: ?>

                    <span class="badge bg-secondary">

                        <?= count($installments); ?>

                        parcela<?= count($installments) !== 1
                                    ? 's'
                                    : ''; ?>

                    </span>

                <?php endif; ?>

            </span>

        </div>


        <div class="card-body">


            <?php if (!empty($installments)): ?>

                <div class="row g-3">

                    <?php foreach ($installments as $installment): ?>

                        <?php

                        $displayStatus =
                            $getInstallmentStatus(
                                $installment
                            );

                        $meta =
                            $statusMeta[$displayStatus]
                            ?? $statusMeta['AV'];

                        /*
 * ============================================================
 * DADOS FINANCEIROS DA PARCELA
 * ============================================================
 */

                        $installmentId =
                            (int) (
                                $installment['id']
                                ?? 0
                            );


                        $originalAmount =
                            (float) (
                                $installment['original_amount']
                                ?? 0
                            );


                        $principalPaid =
                            (float) (
                                $installment['principal_paid']
                                ?? 0
                            );


                        $remainingPrincipal =
                            (float) (
                                $installment['remaining_principal']
                                ?? $originalAmount
                            );


                        $installmentPayments =
                            $paymentsByInstallment[$installmentId]
                            ?? [];

                        ?>

                        <div
                            class="col-xl-4 col-md-6">

                            <div
                                class="card h-100 border-secondary">

                                <div
                                    class="card-header
                                           d-flex
                                           align-items-center">

                                    <strong>

                                        <i
                                            class="fa-solid
                                                   fa-calendar-day
                                                   me-1">
                                        </i>

                                        <?= (int) (
                                            $installment['installment_number']
                                            ?? 0
                                        ); ?>ª Parcela

                                    </strong>


                                    <span
                                        class="badge
                                               <?= $meta['class']; ?>
                                               ms-auto">

                                        <i
                                            class="fa-solid
                                                   <?= $meta['icon']; ?>
                                                   me-1">
                                        </i>

                                        <?= $displayStatus; ?>

                                    </span>

                                </div>


                                <div class="card-body">

                                    <div class="mb-3">

                                        <span
                                            class="text-muted
                                                   d-block">

                                            Vencimento

                                        </span>

                                        <strong class="fs-5">

                                            <?php

                                            if (
                                                !empty($installment['due_date'])
                                            ) {

                                                echo date(
                                                    'd/m/Y',
                                                    strtotime(
                                                        $installment['due_date']
                                                    )
                                                );
                                            } else {

                                                echo 'Indefinido';
                                            }

                                            ?>

                                        </strong>

                                    </div>


                                    <div class="mb-3">

                                        <span class="text-muted d-block">
                                            Valor da Parcela
                                        </span>

                                        <strong class="fs-4">

                                            R$
                                            <?= number_format(
                                                $originalAmount,
                                                2,
                                                ',',
                                                '.'
                                            ); ?>

                                        </strong>

                                    </div>


                                    <div class="row g-2 mb-3">

                                        <!-- Valor já pago -->
                                        <div class="col-6">

                                            <div
                                                class="border rounded p-2 h-100">

                                                <span
                                                    class="text-muted small d-block">

                                                    Pago

                                                </span>

                                                <strong class="text-success">

                                                    R$
                                                    <?= number_format(
                                                        $principalPaid,
                                                        2,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </strong>

                                            </div>

                                        </div>


                                        <!-- Saldo da parcela -->
                                        <div class="col-6">

                                            <div
                                                class="border rounded p-2 h-100">

                                                <span
                                                    class="text-muted small d-block">

                                                    Saldo

                                                </span>

                                                <strong
                                                    class="<?= $remainingPrincipal > 0
                                                                ? 'text-warning'
                                                                : 'text-success'; ?>">

                                                    R$
                                                    <?= number_format(
                                                        $remainingPrincipal,
                                                        2,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </strong>

                                            </div>

                                        </div>

                                    </div>


                                    <div>

                                        <span
                                            class="text-muted
                                                   d-block mb-1">

                                            Situação

                                        </span>

                                        <span
                                            class="badge
                                                   <?= $meta['class']; ?>">

                                            <?= htmlspecialchars(
                                                $meta['label']
                                            ); ?>

                                        </span>

                                    </div>

                                    <hr>


                                    <div class="d-flex flex-wrap gap-2">

                                        <?php if ($remainingPrincipal > 0): ?>

                                            <!--
            Botão preparado para utilizar o mesmo modal
            de pagamento da listagem.
        -->
                                            <button
                                                type="button"
                                                class="btn btn-success btn-sm btn-installment-payment"
                                                data-bs-toggle="modal"
                                                data-bs-target="#installmentPaymentModal"

                                                data-installment-id="<?= $installmentId; ?>"

                                                data-installment-number="<?= (int) (
                                                                                $installment['installment_number']
                                                                                ?? 0
                                                                            ); ?>"

                                                data-due-date="<?= htmlspecialchars(
                                                                    $installment['due_date']
                                                                        ?? ''
                                                                ); ?>"

                                                data-original-amount="<?= htmlspecialchars(
                                                                            (string) $originalAmount
                                                                        ); ?>"

                                                data-remaining-principal="<?= htmlspecialchars(
                                                                                (string) $remainingPrincipal
                                                                            ); ?>">

                                                <i class="fa-solid fa-money-bill-wave me-1"></i>

                                                Realizar Pagamento

                                            </button>

                                        <?php endif; ?>


                                        <?php if (!empty($installmentPayments)): ?>

                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btn-sm"

                                                data-bs-toggle="modal"
                                                data-bs-target="#installmentHistoryModal"

                                                data-installment-number="<?= (int) (
                                                                                $installment['installment_number']
                                                                                ?? 0
                                                                            ); ?>"

                                                data-payments="<?= htmlspecialchars(
                                                                    json_encode(
                                                                        $installmentPayments,
                                                                        JSON_UNESCAPED_UNICODE
                                                                            | JSON_UNESCAPED_SLASHES
                                                                    ),
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                ); ?>"

                                                title="Histórico de pagamentos">

                                                <i class="fa-solid fa-clock-rotate-left me-1"></i>

                                                Histórico

                                                <span class="badge bg-secondary text-white ms-1">
                                                    <?= count($installmentPayments); ?>
                                                </span>

                                            </button>

                                        <?php endif; ?>

                                    </div>


                                    <?php if (
                                        !empty($installment['observation'])
                                    ): ?>

                                        <hr>

                                        <small class="text-muted">

                                            <?= htmlspecialchars(
                                                $installment['observation']
                                            ); ?>

                                        </small>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


            <?php else: ?>

                <?php if ($isPaymentSchedulePending): ?>

                    <div class="alert alert-warning mb-0">

                        <div class="d-flex align-items-center gap-2">

                            <i class="fa-solid fa-clock fs-4"></i>

                            <div>

                                <strong>
                                    Parcelas ainda não confirmadas
                                </strong>

                                <div class="small mt-1">
                                    Este lançamento está aguardando
                                    os boletos, valores ou vencimentos definitivos.
                                </div>

                            </div>

                        </div>

                    </div>

                <?php else: ?>

                    <div class="alert alert-warning mb-0">

                        Nenhuma parcela cadastrada
                        para este lançamento.

                    </div>

                <?php endif; ?>

            <?php endif; ?>


            <!-- ================================================== -->
            <!-- RESUMO                                             -->
            <!-- ================================================== -->

            <hr class="my-4">


            <div class="row">

                <div
                    class="col-lg-5 col-xl-4 ms-auto">

                    <div
                        class="d-flex
                               justify-content-between
                               mb-2">

                        <span class="text-muted">

                            <?= $isManual
                                ? 'Valor do Documento:'
                                : 'Valor da NF-e:'; ?>

                        </span>

                        <strong>

                            R$
                            <?= number_format(
                                $nfeTotal,
                                2,
                                ',',
                                '.'
                            ); ?>

                        </strong>

                    </div>


                    <?php if (!$isPaymentSchedulePending): ?>

                        <!-- aqui ficam os blocos atuais:
         Soma das Parcelas
         Diferença
    -->

                    <?php else: ?>

                        <div
                            class="d-flex
               justify-content-between
               align-items-center">

                            <span class="text-muted">
                                Parcelamento:
                            </span>

                            <span class="badge bg-warning text-dark">

                                <i class="fa-solid fa-clock me-1"></i>

                                A confirmar

                            </span>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- AÇÕES                                                  -->
    <!-- ====================================================== -->

    <div class="d-flex justify-content-end gap-2 mb-4">

        <?php if ($canDeletePurchaseDocument): ?>

            <button
                type="button"
                class="btn btn-danger"
                data-bs-toggle="modal"
                data-bs-target="#deletePurchaseDocumentModal">

                <i class="fa-solid fa-trash me-1"></i>

                Excluir Lançamento

            </button>

        <?php endif; ?>


        <a
            href="<?= $_ENV['URL_ADM']; ?>edit-purchase-document/<?= (int) $purchaseDocument['id']; ?>"
            class="btn btn-warning">

            <i class="fa-solid fa-pen-to-square me-1"></i>
            Editar Lançamento

        </a>

        <a
            href="<?= $_ENV['URL_ADM']; ?>list-nfes"
            class="btn btn-outline-secondary">

            <i class="fa-solid fa-file-invoice me-1"></i>
            NF-e Recebidas

        </a>

        <a
            href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents"
            class="btn btn-primary">

            <i class="fa-solid fa-list me-1"></i>
            Lista de Compras

        </a>

    </div>

    <?php if ($canDeletePurchaseDocument): ?>

        <!-- ====================================================== -->
        <!-- EXCLUIR LANÇAMENTO                                     -->
        <!-- ====================================================== -->

        <div
            class="modal fade"
            id="deletePurchaseDocumentModal"
            tabindex="-1"
            aria-labelledby="deletePurchaseDocumentModalLabel"
            aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <form
                        method="POST"
                        action="<?= $_ENV['URL_ADM']; ?>delete-purchase-document">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(
                                        CSRFHelper::generateCSRFToken(
                                            'form_delete_purchase_document'
                                        )
                                    ); ?>">

                        <input
                            type="hidden"
                            name="adms_daman_purchase_document_id"
                            value="<?= (int) $purchaseDocument['id']; ?>">


                        <div class="modal-header">

                            <h5
                                class="modal-title text-danger"
                                id="deletePurchaseDocumentModalLabel">

                                <i class="fa-solid fa-triangle-exclamation me-1"></i>

                                Excluir lançamento financeiro?

                            </h5>


                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Fechar">
                            </button>

                        </div>


                        <div class="modal-body">

                            <div class="alert alert-danger">

                                <strong>
                                    Esta ação é definitiva.
                                </strong>

                                <div class="mt-2">
                                    O lançamento, suas parcelas e o rateio
                                    entre obras serão removidos.
                                </div>

                            </div>


                            <?php if (!$isManual): ?>

                                <div class="alert alert-info">

                                    <i class="fa-solid fa-file-invoice me-1"></i>

                                    A NF-e não será excluída nem desconferida.
                                    Ela permanecerá conferida e poderá ser
                                    lançada novamente.

                                </div>

                            <?php else: ?>

                                <div class="alert alert-info">

                                    <i class="fa-solid fa-receipt me-1"></i>

                                    Como este é um lançamento manual,
                                    será necessário cadastrá-lo novamente
                                    caso a compra ainda deva permanecer no financeiro.

                                </div>

                            <?php endif; ?>


                            <div class="mb-3">

                                <strong>
                                    Documento:
                                </strong>

                                <?= htmlspecialchars(
                                    trim(
                                        (string) (
                                            $purchaseDocument['document_type']
                                            ?? ''
                                        )
                                        . ' '
                                        . (
                                            $purchaseDocument['document_number']
                                            ?? ''
                                        )
                                    )
                                ); ?>

                            </div>


                            <div class="mb-3">

                                <strong>
                                    Valor:
                                </strong>

                                R$
                                <?= number_format(
                                    (float) (
                                        $purchaseDocument['total_value']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </div>


                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="confirm_delete"
                                    id="confirm_delete_purchase_document"
                                    value="1">

                                <label
                                    class="form-check-label"
                                    for="confirm_delete_purchase_document">

                                    Estou ciente de que este lançamento
                                    será excluído e precisará ser refeito
                                    caso os dados estejam incorretos.

                                </label>

                            </div>

                        </div>


                        <div class="modal-footer">

                            <button
                                type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">

                                Cancelar

                            </button>


                            <button
                                type="submit"
                                class="btn btn-danger">

                                <i class="fa-solid fa-trash me-1"></i>

                                Excluir lançamento

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    <?php endif; ?>


    <?php if ($isPaymentSchedulePending): ?>

        <!-- ====================================================== -->
        <!-- CONFIRMAR PARCELAMENTO                                 -->
        <!-- ====================================================== -->

        <div
            class="modal fade"
            id="confirmPaymentScheduleModal"
            tabindex="-1"
            aria-labelledby="confirmPaymentScheduleModalLabel"
            aria-hidden="true">

            <div class="modal-dialog modal-lg">

                <div class="modal-content">

                    <form
                        method="POST"
                        action="<?= $_ENV['URL_ADM']; ?>confirm-purchase-payment-schedule"
                        id="confirmPaymentScheduleForm">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(
                                        CSRFHelper::generateCSRFToken(
                                            'form_confirm_purchase_payment_schedule'
                                        )
                                    ); ?>">

                        <input
                            type="hidden"
                            name="adms_daman_purchase_document_id"
                            value="<?= (int) $purchaseDocument['id']; ?>">

                        <div class="modal-header">

                            <h5
                                class="modal-title"
                                id="confirmPaymentScheduleModalLabel">

                                <i class="fa-solid fa-calendar-check me-1"></i>

                                Confirmar Parcelas

                            </h5>


                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Fechar">
                            </button>

                        </div>


                        <div class="modal-body">

                            <div class="alert alert-info">

                                <i class="fa-solid fa-circle-info me-1"></i>

                                Informe a condição de pagamento para gerar
                                as parcelas definitivas deste lançamento.

                            </div>


                            <div class="row g-3">

                                <!-- Documento -->
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Documento
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                                    ($purchaseDocument['document_type'] ?? '')
                                                        . ' '
                                                        . ($purchaseDocument['document_number'] ?? '')
                                                ); ?>"
                                        disabled>

                                </div>


                                <!-- Valor -->
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Valor do Documento
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control fw-bold"
                                        value="R$ <?= number_format(
                                                        (float) (
                                                            $purchaseDocument['total_value']
                                                            ?? 0
                                                        ),
                                                        2,
                                                        ',',
                                                        '.'
                                                    ); ?>"
                                        disabled>

                                </div>


                                <!-- Condição -->
                                <div class="col-12">

                                    <label
                                        for="confirm_payment_method_id"
                                        class="form-label">

                                        Condição de Pagamento

                                    </label>


                                    <select
                                        name="adms_daman_payment_method_id"
                                        id="confirm_payment_method_id"
                                        class="form-select">

                                        <option value="">
                                            Selecione
                                        </option>

                                        <?php foreach (
                                            $paymentMethods
                                            as $paymentMethod
                                        ): ?>

                                            <option
                                                value="<?= (int) $paymentMethod['id']; ?>">

                                                <?= htmlspecialchars(
                                                    $paymentMethod['name']
                                                ); ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>


                            <!-- Parcelas geradas entrarão aqui depois -->
                            <div
                                id="confirmPaymentScheduleInstallments"
                                class="mt-4">
                            </div>

                        </div>


                        <div class="modal-footer">

                            <button
                                type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">

                                Cancelar

                            </button>


                            <button
                                type="button"
                                id="btnGenerateConfirmedInstallments"
                                class="btn btn-primary"
                                data-endpoint="<?= $_ENV['URL_ADM']; ?>get-payment-method-items"
                                data-purchase-date="<?= htmlspecialchars(
                                                        $purchaseDocument['purchase_date'] ?? ''
                                                    ); ?>"
                                data-total-value="<?= htmlspecialchars(
                                                        $purchaseDocument['total_value'] ?? '0'
                                                    ); ?>">

                                <i class="fa-solid fa-gears me-1"></i>

                                Gerar Parcelas

                            </button>

                            <button
                                type="submit"
                                id="btnConfirmPaymentSchedule"
                                class="btn btn-success"
                                disabled>

                                <i class="fa-solid fa-check me-1"></i>

                                Confirmar e Salvar Parcelas

                            </button>

                        </div>
                    </form>
                </div>

            </div>

        </div>

    <?php endif; ?>

    <!-- ====================================================== -->
    <!-- REALIZAR PAGAMENTO DA PARCELA                         -->
    <!-- ====================================================== -->

    <div
        class="modal fade"
        id="installmentPaymentModal"
        tabindex="-1"
        aria-labelledby="installmentPaymentModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <form
                    action="<?= $_ENV['URL_ADM']; ?>create-purchase-installment-payment"
                    method="POST"
                    id="installmentPaymentForm">

                    <input
                        type="hidden"
                        name="redirect_purchase_document_id"
                        value="<?= (int) $purchaseDocument['id']; ?>">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                                    $paymentCsrfToken
                                ); ?>">

                    <input
                        type="hidden"
                        name="adms_daman_purchase_installment_id"
                        id="payment_installment_id">


                    <div class="modal-header">

                        <h5
                            class="modal-title"
                            id="installmentPaymentModalLabel">

                            <i class="fa-solid fa-money-bill-wave me-1"></i>

                            Realizar Pagamento

                        </h5>


                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Fechar">
                        </button>

                    </div>


                    <div class="modal-body">

                        <div class="row g-3">

                            <!-- Parcela -->
                            <div class="col-md-4">

                                <label class="form-label">
                                    Parcela
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="payment_installment_number"
                                    disabled>

                            </div>


                            <!-- Vencimento -->
                            <div class="col-md-4">

                                <label class="form-label">
                                    Vencimento
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    id="payment_due_date"
                                    disabled>

                            </div>


                            <!-- Valor original -->
                            <div class="col-md-4">

                                <label class="form-label">
                                    Valor original
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="payment_original_amount"
                                    disabled>

                            </div>


                            <!-- Saldo -->
                            <div class="col-md-4">

                                <label class="form-label">
                                    Saldo em aberto
                                </label>

                                <input
                                    type="text"
                                    class="form-control fw-bold"
                                    id="payment_remaining_principal"
                                    disabled>

                            </div>


                            <!-- Data -->
                            <div class="col-md-4">

                                <label
                                    for="payment_date"
                                    class="form-label">

                                    Data do pagamento

                                </label>

                                <input
                                    type="date"
                                    name="payment_date"
                                    id="payment_date"
                                    class="form-control"
                                    value="<?= date('Y-m-d'); ?>">

                            </div>



                            <div class="col-md-4">

                                <label
                                    for="payment_financial_payment_method_id"
                                    class="form-label">

                                    Forma de pagamento

                                </label>

                                <select
                                    name="adms_daman_financial_payment_method_id"
                                    id="payment_financial_payment_method_id"
                                    class="form-select"
                                    required>

                                    <option value="">
                                        Selecione
                                    </option>

                                    <?php foreach (
                                        $financialPaymentMethods
                                        as $financialPaymentMethod
                                    ): ?>

                                        <option
                                            value="<?= (int) $financialPaymentMethod['id']; ?>">

                                            <?= htmlspecialchars(
                                                $financialPaymentMethod['name']
                                            ); ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <!-- Principal -->
                            <div class="col-md-4">

                                <label
                                    for="principal_amount"
                                    class="form-label">

                                    Principal pago

                                </label>

                                <input
                                    type="text"
                                    name="principal_amount"
                                    id="principal_amount"
                                    class="form-control payment-money"
                                    inputmode="decimal">

                            </div>


                            <!-- Juros -->
                            <div class="col-md-4">

                                <label
                                    for="interest_amount"
                                    class="form-label">

                                    Juros

                                </label>

                                <input
                                    type="text"
                                    name="interest_amount"
                                    id="interest_amount"
                                    class="form-control payment-money"
                                    value="0,00"
                                    inputmode="decimal">

                            </div>


                            <!-- Multa -->
                            <div class="col-md-4">

                                <label
                                    for="penalty_amount"
                                    class="form-label">

                                    Multa

                                </label>

                                <input
                                    type="text"
                                    name="penalty_amount"
                                    id="penalty_amount"
                                    class="form-control payment-money"
                                    value="0,00"
                                    inputmode="decimal">

                            </div>


                            <!-- Desconto -->
                            <div class="col-md-4">

                                <label
                                    for="discount_amount"
                                    class="form-label">

                                    Desconto

                                </label>

                                <input
                                    type="text"
                                    name="discount_amount"
                                    id="discount_amount"
                                    class="form-control payment-money"
                                    value="0,00"
                                    inputmode="decimal">

                            </div>


                            <!-- Total efetivamente pago -->
                            <div class="col-md-4">

                                <label class="form-label">
                                    Total pago
                                </label>

                                <input
                                    type="text"
                                    id="payment_total_paid"
                                    class="form-control fw-bold"
                                    disabled>

                            </div>


                            <!-- Observação -->
                            <div class="col-12">

                                <label
                                    for="payment_observation"
                                    class="form-label">

                                    Observação

                                </label>

                                <textarea
                                    name="observation"
                                    id="payment_observation"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Observações sobre o pagamento..."></textarea>

                            </div>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                            Cancelar

                        </button>


                        <button
                            type="submit"
                            class="btn btn-success">

                            <i class="fa-solid fa-check me-1"></i>

                            Confirmar Baixa

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- ====================================================== -->
    <!-- HISTÓRICO DE PAGAMENTOS DA PARCELA                    -->
    <!-- ====================================================== -->

    <div
        class="modal fade"
        id="installmentHistoryModal"
        tabindex="-1"
        aria-labelledby="installmentHistoryModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="installmentHistoryModalLabel">

                        <i class="fa-solid fa-clock-rotate-left me-1"></i>

                        Histórico da Parcela

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar">
                    </button>

                </div>


                <div
                    class="modal-body"
                    id="installmentHistoryContent">

                    <!--
                        Conteúdo preenchido pelo JavaScript
                        conforme a parcela selecionada.
                    -->

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Fechar

                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- ====================================================== -->
    <!-- ESTORNO DE PAGAMENTO                                   -->
    <!-- ====================================================== -->

    <div
        class="modal fade"
        id="reverseInstallmentPaymentModal"
        tabindex="-1"
        aria-labelledby="reverseInstallmentPaymentModalLabel"
        aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <form
                    method="POST"
                    action="<?= $_ENV['URL_ADM']; ?>reverse-purchase-installment-payment"
                    id="reverseInstallmentPaymentForm">

                    <input
                        type="hidden"
                        name="redirect_purchase_document_id"
                        value="<?= (int) $purchaseDocument['id']; ?>">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                                    $reversePaymentCsrfToken
                                ); ?>">

                    <input
                        type="hidden"
                        name="payment_id"
                        id="reverse_payment_id">


                    <div class="modal-header">

                        <h5
                            class="modal-title"
                            id="reverseInstallmentPaymentModalLabel">

                            <i
                                class="fa-solid
                                   fa-arrow-rotate-left
                                   me-1">
                            </i>

                            Estornar Pagamento

                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Fechar">
                        </button>

                    </div>


                    <div class="modal-body">

                        <div
                            class="alert alert-warning"
                            id="reversePaymentSummary">

                            <!-- Preenchido pelo JavaScript -->

                        </div>


                        <div>

                            <label
                                for="reversal_reason"
                                class="form-label">

                                Motivo do estorno

                            </label>

                            <textarea
                                name="reversal_reason"
                                id="reversal_reason"
                                class="form-control"
                                rows="3"
                                placeholder="Informe por que esta baixa está sendo estornada..."></textarea>

                            <div class="invalid-feedback">
                                Informe o motivo do estorno.
                            </div>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                            Cancelar

                        </button>


                        <button
                            type="submit"
                            class="btn btn-danger">

                            <i
                                class="fa-solid
                                   fa-arrow-rotate-left
                                   me-1">
                            </i>

                            Confirmar Estorno

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>