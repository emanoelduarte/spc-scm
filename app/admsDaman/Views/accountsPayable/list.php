<?php

use App\admsDaman\Helpers\CSRFHelper;

$purchaseDocuments =
    $this->data['purchaseDocuments'] ?? [];

$paymentsByInstallment =
    $this->data['paymentsByInstallment'] ?? [];


/*
 * Tokens utilizados pelos formulários financeiros
 * existentes nesta tela.
 */
$paymentCsrfToken =
    CSRFHelper::generateCSRFToken(
        'form_purchase_installment_payment'
    );

$reversePaymentCsrfToken =
    CSRFHelper::generateCSRFToken(
        'form_reverse_purchase_installment_payment'
    );


/*
 * ============================================================
 * DESCOBRIR A MAIOR QUANTIDADE DE PARCELAS
 * ============================================================
 *
 * Isso permite criar dinamicamente as colunas:
 *
 * 1ª Parcela | 2ª Parcela | 3ª Parcela | ...
 *
 * sem limitar o sistema a uma quantidade fixa.
 */
$maxInstallments = 0;

foreach ($purchaseDocuments as $purchaseDocument) {

    $quantity =
        count(
            $purchaseDocument['installments']
                ?? []
        );

    if ($quantity > $maxInstallments) {

        $maxInstallments =
            $quantity;
    }
}


/*
 * ============================================================
 * STATUS VISUAL DA PARCELA
 * ============================================================
 *
 * OK e AP são manuais.
 *
 * AV, AT e ON são recalculados conforme
 * a data atual.
 */
$getInstallmentStatus =
    function (array $installment): string {

        $status =
            strtoupper(
                (string) (
                    $installment['status']
                    ?? 'AV'
                )
            );


        /*
         * Status manuais.
         */
        if (
            $status === 'OK'
            ||
            $status === 'AP'
        ) {
            return $status;
        }


        /*
         * Sem vencimento.
         */
        if (
            empty($installment['due_date'])
        ) {
            return $status;
        }


        $today =
            new DateTimeImmutable(
                'today'
            );

        $dueDate =
            new DateTimeImmutable(
                $installment['due_date']
            );


        /*
         * Já venceu.
         */
        if ($dueDate < $today) {
            return 'ON';
        }


        /*
         * Dias restantes.
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


        /*
         * Mais de 7 dias.
         */
        return 'AV';
    };


/*
 * ============================================================
 * APRESENTAÇÃO DOS STATUS
 * ============================================================
 */
$statusMeta = [

    'AV' => [
        'label' => 'AV',
        'class' => 'bg-primary',
        'title' => 'A Vencer',
    ],

    'AT' => [
        'label' => 'AT',
        'class' => 'bg-warning text-dark',
        'title' => 'Atenção',
    ],

    'ON' => [
        'label' => 'ON',
        'class' => 'bg-danger',
        'title' => 'Em Aberto / Vencido',
    ],

    'OK' => [
        'label' => 'OK',
        'class' => 'bg-success',
        'title' => 'Pago',
    ],

    'AP' => [
        'label' => 'AP',
        'class' => 'bg-info text-dark',
        'title' => 'Permuta',
    ],
];

?>


<div class="container-fluid px-4">


    <!-- ====================================================== -->
    <!-- TÍTULO / BREADCRUMB                                    -->
    <!-- ====================================================== -->

    <div
        class="mb-1 d-flex flex-column flex-sm-row gap-2">

        <div>

            <h2 class="mt-3 mb-1">
                Compras
            </h2>

            <p class="text-muted mb-3">
                Acompanhamento financeiro das compras lançadas
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

            <li
                class="breadcrumb-item active"
                aria-current="page">

                Compras

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
    <!-- FILTROS                                                -->
    <!-- ====================================================== -->

    <div class="card mb-3 border-light shadow">

        <div class="card-header">

            <i class="fa-solid fa-filter me-1"></i>

            Filtros de Pesquisa

        </div>


        <div class="card-body">

            <form
                method="GET"
                action="<?= $_ENV['URL_ADM']; ?>list-purchase-documents"
                class="row g-3 align-items-end">

                <!-- Obra -->
                <div class="col-lg-4 col-md-6">

                    <label
                        for="project_id"
                        class="form-label">

                        Obra

                    </label>


                    <select
                        name="project_id"
                        id="project_id"
                        class="form-select">

                        <option value="">
                            Todas as obras
                        </option>

                        <?php
                        foreach (
                            $this->data['projects'] ?? []
                            as $project
                        ):
                        ?>

                            <option
                                value="<?= (int) $project['id']; ?>"
                                <?= (
                                    (int) (
                                        $this->data['filters']['project_id']
                                        ?? 0
                                    )
                                    ===
                                    (int) $project['id']
                                )
                                    ? 'selected'
                                    : ''; ?>>

                                <?= htmlspecialchars(
                                    $project['name']
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Fornecedor -->
                <div class="col-lg-3 col-md-6">

                    <label
                        for="supplier_cnpj"
                        class="form-label">

                        Fornecedor

                    </label>

                    <select
                        name="supplier_key"
                        id="supplier_key"
                        class="form-select">

                        <option value="">
                            Todos
                        </option>

                        <?php
                        foreach (
                            $this->data['suppliers'] ?? []
                            as $supplier
                        ):
                        ?>

                            <option
                                value="<?= htmlspecialchars(
                                            $supplier['supplier_key']
                                        ); ?>"
                                <?= (
                                    (
                                        $this->data['filters']['supplier_key']
                                        ?? ''
                                    )
                                    ===
                                    $supplier['supplier_key']
                                )
                                    ? 'selected'
                                    : ''; ?>>

                                <?= htmlspecialchars(
                                    $supplier['supplier_name']
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Número do documento -->
                <div class="col-lg-3 col-md-6">

                    <label
                        for="document_number"
                        class="form-label">

                        Documento

                    </label>

                    <input
                        type="text"
                        name="document_number"
                        id="document_number"
                        class="form-control"
                        placeholder="NF-e, recibo, cupom..."
                        value="<?= htmlspecialchars(
                                    $this->data['filters']['document_number']
                                        ?? ''
                                ); ?>">

                </div>

                <!-- Status da parcela -->
                <div class="col-lg-2 col-md-6">

                    <label
                        for="installment_status"
                        class="form-label">

                        Status da Parcela

                    </label>

                    <?php

                    $currentInstallmentStatus =
                        $this->data['filters']['installment_status']
                        ?? '';

                    $installmentStatuses = [
                        'AV' => 'A vencer',
                        'AT' => 'Atenção - até 7 dias',
                        'ON' => 'Vencida',
                        'OK' => 'Paga',
                        'AP' => 'Permuta',
                    ];

                    ?>

                    <select
                        name="installment_status"
                        id="installment_status"
                        class="form-select">

                        <option
                            value=""
                            <?= $currentInstallmentStatus === ''
                                ? 'selected'
                                : ''; ?>>

                            Todos

                        </option>

                        <?php foreach (
                            $installmentStatuses as $value => $label
                        ): ?>

                            <option
                                value="<?= htmlspecialchars($value); ?>"
                                <?= $currentInstallmentStatus === $value
                                    ? 'selected'
                                    : ''; ?>>

                                <?= htmlspecialchars($label); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Situação do parcelamento -->
                <div class="col-lg-3 col-md-6">

                    <label
                        for="payment_schedule_status"
                        class="form-label">

                        Situação do Parcelamento

                    </label>

                    <?php

                    $currentPaymentScheduleStatus =
                        $this->data['filters']['payment_schedule_status']
                        ?? '';

                    ?>

                    <select
                        name="payment_schedule_status"
                        id="payment_schedule_status"
                        class="form-select">

                        <option
                            value=""
                            <?= $currentPaymentScheduleStatus === ''
                                ? 'selected'
                                : ''; ?>>

                            Todos

                        </option>

                        <option
                            value="confirmed"
                            <?= $currentPaymentScheduleStatus === 'confirmed'
                                ? 'selected'
                                : ''; ?>>

                            Confirmadas

                        </option>

                        <option
                            value="pending"
                            <?= $currentPaymentScheduleStatus === 'pending'
                                ? 'selected'
                                : ''; ?>>

                            FB - Falta boleto

                        </option>

                    </select>

                </div>


                <!-- Vencimento inicial -->
                <div class="col-lg-2 col-md-6">

                    <label
                        for="due_date_start"
                        class="form-label">

                        Vencimento de

                    </label>

                    <input
                        type="date"
                        name="due_date_start"
                        id="due_date_start"
                        class="form-control"
                        value="<?= htmlspecialchars(
                                    $this->data['filters']['due_date_start']
                                        ?? ''
                                ); ?>">

                </div>


                <!-- Vencimento final -->
                <div class="col-lg-2 col-md-6">

                    <label
                        for="due_date_end"
                        class="form-label">

                        Vencimento até

                    </label>

                    <input
                        type="date"
                        name="due_date_end"
                        id="due_date_end"
                        class="form-control"
                        value="<?= htmlspecialchars(
                                    $this->data['filters']['due_date_end']
                                        ?? ''
                                ); ?>">

                </div>


                <!-- Filtrar -->
                <div class="col-auto">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fa-solid fa-filter me-1"></i>

                        Filtrar

                    </button>

                </div>


                <!-- Limpar -->
                <div class="col-auto">

                    <a
                        href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents"
                        class="btn btn-secondary">

                        <i class="fa-solid fa-eraser me-1"></i>

                        Limpar

                    </a>

                </div>

                <?php

                /*
                 * =================================================
                 * ALERTAS RÁPIDOS DO FINANCEIRO
                 * =================================================
                 *
                 * FB conta lançamentos.
                 * AT e ON contam parcelas individuais.
                 */
                $pendingPaymentScheduleCount =
                    (int) (
                        $this->data['pendingPaymentScheduleCount']
                        ?? 0
                    );

                $attentionInstallmentsCount =
                    (int) (
                        $this->data['attentionInstallmentsCount']
                        ?? 0
                    );

                $overdueInstallmentsCount =
                    (int) (
                        $this->data['overdueInstallmentsCount']
                        ?? 0
                    );

                $hasFinancialAlerts =
                    $pendingPaymentScheduleCount > 0
                    || $attentionInstallmentsCount > 0
                    || $overdueInstallmentsCount > 0;

                ?>


                <?php if ($hasFinancialAlerts): ?>

                    <div
                        class="col-auto ms-lg-auto
                               d-flex flex-wrap gap-2">


                        <?php if ($pendingPaymentScheduleCount > 0): ?>

                            <a
                                href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents?payment_schedule_status=pending"
                                class="btn btn-outline-warning text-decoration-none"
                                title="Lançamentos aguardando boleto ou confirmação das parcelas">

                                <i class="fa-solid fa-clock me-1"></i>

                                <strong>FB pendentes</strong>

                                <span class="badge bg-warning text-dark ms-1">
                                    <?= $pendingPaymentScheduleCount; ?>
                                </span>

                            </a>

                        <?php endif; ?>


                        <?php if ($attentionInstallmentsCount > 0): ?>

                            <a
                                href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents?installment_status=AT"
                                class="btn btn-outline-warning text-decoration-none"
                                title="Parcelas com vencimento entre hoje e os próximos 7 dias">

                                <i
                                    class="fa-solid
                                           fa-triangle-exclamation
                                           me-1">
                                </i>

                                <strong>AT</strong>

                                <span class="badge bg-warning text-dark ms-1">
                                    <?= $attentionInstallmentsCount; ?>
                                </span>

                            </a>

                        <?php endif; ?>


                        <?php if ($overdueInstallmentsCount > 0): ?>

                            <a
                                href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents?installment_status=ON"
                                class="btn btn-outline-danger text-decoration-none"
                                title="Parcelas vencidas e ainda não baixadas">

                                <i
                                    class="fa-solid
                                           fa-circle-exclamation
                                           me-1">
                                </i>

                                <strong>ON</strong>

                                <span class="badge bg-danger text-white ms-1">
                                    <?= $overdueInstallmentsCount; ?>
                                </span>

                            </a>

                        <?php endif; ?>


                    </div>

                <?php endif; ?>

            </form>

        </div>

    </div>

    <!-- ====================================================== -->
    <!-- CARD RESUMOS                                          -->
    <!-- ====================================================== -->

    <div class="row g-3 mb-4">

        <!-- Total em Aberto -->
        <div class="col-xl-3 col-md-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div
                        class="d-flex
                           justify-content-between
                           align-items-center">

                        <div>

                            <div class="text-muted small mb-1">
                                Total em Aberto
                            </div>

                            <div class="fs-4 fw-bold">

                                R$
                                <?= number_format(
                                    (float) (
                                        $this->data['totalOpenAmount']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </div>

                        </div>

                        <div class="fs-3 text-primary">

                            <i class="fa-solid fa-wallet"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Vence em até 7 dias -->
        <div class="col-xl-3 col-md-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div
                        class="d-flex
                           justify-content-between
                           align-items-center">

                        <div>

                            <div class="text-muted small mb-1">

                                Vence em até 7 dias

                            </div>

                            <div class="fs-4 fw-bold">

                                R$
                                <?= number_format(
                                    (float) (
                                        $this->data['dueSoonAmount']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </div>

                        </div>

                        <div class="fs-3 text-warning">

                            <i class="fa-solid fa-clock"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- Total Vencido -->
        <div class="col-xl-3 col-md-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div
                        class="d-flex
                       justify-content-between
                       align-items-center">

                        <div>

                            <div class="text-muted small mb-1">

                                Total Vencido

                            </div>

                            <div class="fs-4 fw-bold">

                                R$
                                <?= number_format(
                                    (float) (
                                        $this->data['overdueAmount']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </div>

                        </div>


                        <div class="fs-3 text-danger">

                            <i
                                class="fa-solid
                               fa-triangle-exclamation">
                            </i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- Valor Total dos Lançamentos -->
        <div class="col-xl-3 col-md-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div
                        class="d-flex
                       justify-content-between
                       align-items-center">

                        <div>

                            <div class="text-muted small mb-1">

                                Valor dos Lançamentos

                            </div>

                            <div class="fs-4 fw-bold">

                                R$
                                <?= number_format(
                                    (float) (
                                        $this->data['totalPurchaseDocumentsAmount']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </div>

                        </div>

                        <div class="fs-3 text-success">

                            <i class="fa-solid fa-receipt"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- ====================================================== -->
    <!-- CARD PRINCIPAL                                         -->
    <!-- ====================================================== -->

    <div class="card mb-4 border-light shadow">

        <div class="card-header hstack gap-2">

            <span>

                <i class="fa-solid fa-money-check-dollar me-2"></i>

                <strong>
                    Lançamentos Financeiros
                </strong>

            </span>


            <span class="ms-auto">

                <span class="badge bg-secondary">

                    <?= count($purchaseDocuments); ?>

                    lançamento<?= count($purchaseDocuments) !== 1
                                    ? 's'
                                    : ''; ?>

                </span>

            </span>

            <a
                href="<?= $_ENV['URL_ADM']; ?>create-manual-purchase-document"
                class="btn btn-success btn-sm">

                <i class="fa-solid fa-circle-plus me-1"></i>
                Novo Lançamento Manual

            </a>

        </div>

        <div class="card-body">


            <?php if (!empty($purchaseDocuments)): ?>


                <!--
                    A tabela pode ficar larga conforme
                    aumenta a quantidade de parcelas.

                    table-responsive permite rolagem
                    horizontal sem quebrar o layout.
                -->
                <div class="table-responsive">

                    <table
                        class="table table-striped table-hover align-middle">

                        <thead>

                            <!-- ================================= -->
                            <!-- CABEÇALHO PRINCIPAL               -->
                            <!-- ================================= -->

                            <tr>

                                <th rowspan="2">
                                    Obra
                                </th>

                                <th rowspan="2">
                                    Comprador
                                </th>

                                <th rowspan="2">
                                    Documento
                                </th>

                                <th rowspan="2">
                                    Fornecedor
                                </th>

                                <th rowspan="2">
                                    Data Compra
                                </th>

                                <th rowspan="2">
                                    Cond. Pgto
                                </th>

                                <th
                                    rowspan="2"
                                    class="text-end">

                                    Valor

                                </th>


                                <!--
                                    Criar um grupo para
                                    cada parcela existente.
                                -->
                                <?php for (
                                    $i = 1;
                                    $i <= $maxInstallments;
                                    $i++
                                ): ?>

                                    <th
                                        colspan="3"
                                        class="text-center border-start">

                                        <?= $i; ?>ª Parcela

                                    </th>

                                <?php endfor; ?>


                                <th
                                    rowspan="2"
                                    class="text-center">

                                    Ações

                                </th>

                            </tr>


                            <!-- ================================= -->
                            <!-- SUBCABEÇALHO DAS PARCELAS         -->
                            <!-- ================================= -->

                            <tr>

                                <?php for (
                                    $i = 1;
                                    $i <= $maxInstallments;
                                    $i++
                                ): ?>

                                    <th
                                        class="text-center border-start">

                                        Venc.

                                    </th>

                                    <th class="text-end">

                                        Valor

                                    </th>

                                    <th class="text-center">

                                        Status

                                    </th>

                                <?php endfor; ?>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach (
                                $purchaseDocuments
                                as $purchaseDocument
                            ): ?>


                                <?php

                                $installments =
                                    $purchaseDocument['installments']
                                    ?? [];

                                $allocations =
                                    $purchaseDocument['allocations']
                                    ?? [];

                                /*
                                * Verificar se o lançamento ainda está
                                * aguardando confirmação dos boletos/parcelas.
                                */
                                $isPaymentSchedulePending =
                                    (
                                        $purchaseDocument['payment_schedule_status']
                                        ?? 'confirmed'
                                    ) === 'pending';


                                $isProrated =
                                    count($allocations) > 1;

                                /*
                                * Obra atualmente selecionada no filtro.
                                */
                                $currentProjectId =
                                    (int) (
                                        $this->data['filters']['project_id']
                                        ?? 0
                                    );


                                /*
                                * Procurar a alocação correspondente
                                * à obra selecionada.
                                */
                                $selectedAllocation = null;

                                if ($currentProjectId > 0) {

                                    foreach ($allocations as $allocation) {

                                        if (
                                            (int) $allocation['adms_daman_project_id']
                                            === $currentProjectId
                                        ) {

                                            $selectedAllocation =
                                                $allocation;

                                            break;
                                        }
                                    }
                                }


                                /*
                                * Valor integral do documento.
                                */
                                $documentTotal =
                                    (float) (
                                        $purchaseDocument['total_value']
                                        ?? 0
                                    );


                                /*
                                * Sem filtro:
                                * mostrar valor integral.
                                *
                                * Com filtro:
                                * mostrar somente a apropriação da obra.
                                */
                                $displayDocumentAmount =
                                    $selectedAllocation
                                    ? (float) $selectedAllocation['allocated_amount']
                                    : $documentTotal;


                                /*
                                * Proporção da obra dentro do documento.
                                *
                                * Será utilizada apenas para apresentação
                                * das parcelas e saldos.
 */
                                $projectRatio =
                                    (
                                        $selectedAllocation
                                        && $documentTotal > 0
                                    )
                                    ? (
                                        (float) $selectedAllocation['allocated_amount']
                                        / $documentTotal
                                    )
                                    : 1;

                                ?>


                                <tr>


                                    <!-- ========================= -->
                                    <!-- OBRA                      -->
                                    <!-- ========================= -->

                                    <td>

                                        <?php if ($isProrated): ?>

                                            <div class="fw-semibold">

                                                <i class="fa-solid fa-code-branch me-1"> </i>

                                                Rateado

                                            </div>


                                            <div class="small text-muted">

                                                <?= count($allocations); ?>

                                                obras

                                            </div>


                                        <?php elseif (!empty($allocations)): ?>

                                            <!--
                                                Uma única allocation:
                                                usar a nova relação como fonte de verdade.
                                            -->
                                            <strong>

                                                <?= htmlspecialchars(
                                                    $allocations[0]['project_name']
                                                        ?? '-'
                                                ); ?>

                                            </strong>


                                        <?php else: ?>

                                            <!--
                                                Fallback temporário.
                                            -->
                                            <strong>

                                                <?= htmlspecialchars(
                                                    $purchaseDocument['project_name']
                                                        ?? '-'
                                                ); ?>

                                            </strong>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ========================= -->
                                    <!-- COMPRADOR                 -->
                                    <!-- ========================= -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $purchaseDocument['buyer_name']
                                                ?? '-'
                                        ); ?>

                                    </td>


                                    <!-- ========================= -->
                                    <!-- DOCUMENTO                 -->
                                    <!-- ========================= -->

                                    <td>

                                        <?php

                                        $isManual =
                                            ($purchaseDocument['document_origin'] ?? '')
                                            === 'MANUAL';


                                        $viewPurchaseDocumentUrl =
                                            $_ENV['URL_ADM']
                                            . 'view-purchase-document/'
                                            . (int) $purchaseDocument['id'];

                                        ?>


                                        <a
                                            href="<?= $viewPurchaseDocumentUrl; ?>"
                                            class="text-decoration-none d-block"
                                            title="Abrir lançamento">


                                            <?php if ($isManual): ?>

                                                <div class="fw-semibold text-primary">

                                                    <?= htmlspecialchars(
                                                        $purchaseDocument['document_type']
                                                            ?? 'Documento'
                                                    ); ?>

                                                    <?= htmlspecialchars(
                                                        $purchaseDocument['document_number']
                                                            ?? '-'
                                                    ); ?>

                                                </div>


                                                <small class="text-muted">

                                                    Compra Avulsa

                                                    <i
                                                        class="fa-solid
                           fa-arrow-up-right-from-square
                           ms-1">
                                                    </i>

                                                </small>


                                            <?php else: ?>

                                                <div class="fw-semibold text-primary">

                                                    NF-e

                                                    <?= htmlspecialchars(
                                                        $purchaseDocument['document_number']
                                                            ?? '-'
                                                    ); ?>

                                                </div>


                                                <small class="text-muted">

                                                    Série

                                                    <?= htmlspecialchars(
                                                        $purchaseDocument['series']
                                                            ?? '-'
                                                    ); ?>

                                                    <i
                                                        class="fa-solid
                           fa-arrow-up-right-from-square
                           ms-1">
                                                    </i>

                                                </small>

                                            <?php endif; ?>


                                        </a>

                                    </td>


                                    <!-- ========================= -->
                                    <!-- FORNECEDOR                -->
                                    <!-- ========================= -->

                                    <td>

                                        <div class="fw-semibold">

                                            <?= htmlspecialchars(
                                                $purchaseDocument['supplier_name']
                                                    ?? '-'
                                            ); ?>

                                        </div>


                                        <?php if (
                                            !empty($purchaseDocument['supplier_cnpj'])
                                        ): ?>

                                            <small class="text-muted">

                                                <?= htmlspecialchars(
                                                    $purchaseDocument['supplier_cnpj']
                                                ); ?>

                                            </small>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ========================= -->
                                    <!-- DATA DA COMPRA            -->
                                    <!-- ========================= -->

                                    <td class="text-nowrap">

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

                                    </td>

                                    <!-- ========================= -->
                                    <!-- CONDIÇÃO PAGAMENTO        -->
                                    <!-- ========================= -->

                                    <td class="text-nowrap">

                                        <?php if ($isPaymentSchedulePending): ?>

                                            <div>
                                                <span
                                                    class="badge bg-warning text-dark"
                                                    title="Boletos ou parcelas ainda não confirmados">

                                                    <i class="fa-solid fa-clock me-1"></i>
                                                    FB

                                                </span>
                                            </div>

                                            <small class="text-warning">
                                                Falta boleto
                                            </small>

                                        <?php else: ?>

                                            <?= htmlspecialchars(
                                                $purchaseDocument['payment_method_name']
                                                    ?? '-'
                                            ); ?>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ========================= -->
                                    <!-- VALOR TOTAL               -->
                                    <!-- ========================= -->

                                    <td class="text-end">

                                        <strong>

                                            R$
                                            <?= number_format(
                                                $displayDocumentAmount,
                                                2,
                                                ',',
                                                '.'
                                            ); ?>

                                        </strong>


                                        <?php if ($selectedAllocation): ?>

                                            <div class="small text-muted">
                                                Cota da obra
                                            </div>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ========================= -->
                                    <!-- PARCELAS                  -->
                                    <!-- ========================= -->

                                    <?php if (
                                        $isPaymentSchedulePending
                                        && $maxInstallments > 0
                                    ): ?>

                                        <td
                                            colspan="<?= $maxInstallments * 3; ?>"
                                            class="text-center border-start">

                                            <span class="badge bg-warning text-dark">

                                                <i class="fa-solid fa-clock me-1"></i>

                                                Parcelas a confirmar

                                            </span>

                                            <div class="small text-muted mt-1">
                                                Aguardando boletos / vencimentos
                                            </div>

                                        </td>

                                    <?php else: ?>

                                        <?php for (
                                            $i = 0;
                                            $i < $maxInstallments;
                                            $i++
                                        ): ?>


                                            <?php

                                            $installment =
                                                $installments[$i]
                                                ?? null;

                                            ?>


                                            <?php if ($installment): ?>


                                                <?php

                                                $status =
                                                    $getInstallmentStatus(
                                                        $installment
                                                    );

                                                $meta =
                                                    $statusMeta[$status]
                                                    ?? $statusMeta['AV'];


                                                /*
                                            * Pagamentos realizados nesta parcela.
                                            *
                                            * O histórico contém tanto pagamentos ativos
                                            * quanto pagamentos posteriormente estornados.
                                            */
                                                $installmentId =
                                                    (int) ($installment['id'] ?? 0);

                                                $installmentPayments =
                                                    $paymentsByInstallment[$installmentId] ?? [];

                                                ?>

                                                <!-- Vencimento -->
                                                <td
                                                    class="text-center text-nowrap border-start">

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

                                                        echo '-';
                                                    }

                                                    ?>

                                                </td>


                                                <?php

                                                $installmentOriginalAmount =
                                                    (float) (
                                                        $installment['original_amount']
                                                        ?? 0
                                                    );

                                                $displayInstallmentAmount =
                                                    round(
                                                        $installmentOriginalAmount
                                                            * $projectRatio,
                                                        2
                                                    );

                                                ?>

                                                <!-- Valor -->
                                                <td class="text-end">

                                                    <div>
                                                        R$
                                                        <?= number_format(
                                                            $displayInstallmentAmount,
                                                            2,
                                                            ',',
                                                            '.'
                                                        ); ?>
                                                    </div>


                                                    <?php

                                                    $principalPaid =
                                                        (float) (
                                                            $installment['principal_paid']
                                                            ?? 0
                                                        );

                                                    $remainingPrincipal =
                                                        (float) (
                                                            $installment['remaining_principal']
                                                            ?? $installment['original_amount']
                                                            ?? 0
                                                        );

                                                    ?>


                                                    <?php if (
                                                        $principalPaid > 0
                                                        && $remainingPrincipal > 0
                                                    ): ?>

                                                        <div class="small text-muted">
                                                            Pago:
                                                            R$
                                                            <?= number_format(
                                                                $principalPaid,
                                                                2,
                                                                ',',
                                                                '.'
                                                            ); ?>
                                                        </div>

                                                        <div class="small text-muted">
                                                            Saldo:
                                                            R$
                                                            <?= number_format(
                                                                $remainingPrincipal,
                                                                2,
                                                                ',',
                                                                '.'
                                                            ); ?>
                                                        </div>

                                                    <?php endif; ?>

                                                </td>


                                                <!-- Status -->
                                                <td class="text-center">

                                                    <div
                                                        class="d-flex flex-column
               align-items-center gap-1">

                                                        <!-- Status atual da parcela -->
                                                        <span
                                                            class="badge <?= $meta['class']; ?>"
                                                            title="<?= htmlspecialchars(
                                                                        $meta['title']
                                                                    ); ?>">

                                                            <?= $meta['label']; ?>

                                                        </span>


                                                        <?php if ($status !== 'OK'): ?>

                                                            <!-- Dar baixa -->
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
                                                                                            $installment['original_amount']
                                                                                                ?? '0'
                                                                                        ); ?>"

                                                                data-remaining-principal="<?= htmlspecialchars(
                                                                                                $installment['remaining_principal']
                                                                                                    ?? $installment['original_amount']
                                                                                                    ?? '0'
                                                                                            ); ?>"

                                                                title="Dar baixa">

                                                                <i class="fa-solid fa-money-bill-wave"></i>

                                                            </button>

                                                        <?php endif; ?>


                                                        <?php if (!empty($installmentPayments)): ?>

                                                            <!-- Histórico financeiro da parcela -->
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

                                                                <i class="fa-solid fa-clock-rotate-left"></i>

                                                                <span class="ms-1">
                                                                    <?= count($installmentPayments); ?>
                                                                </span>

                                                            </button>

                                                        <?php endif; ?>

                                                    </div>

                                                </td>


                                            <?php else: ?>


                                                <!--
                                                Preencher as colunas
                                                quando esta compra tiver
                                                menos parcelas que outras.
                                            -->

                                                <td
                                                    class="border-start text-center text-muted">
                                                    -
                                                </td>

                                                <td
                                                    class="text-center text-muted">
                                                    -
                                                </td>

                                                <td
                                                    class="text-center text-muted">
                                                    -
                                                </td>


                                            <?php endif; ?>


                                        <?php endfor; ?>

                                    <?php endif; ?>


                                    <!-- ========================= -->
                                    <!-- AÇÕES                     -->
                                    <!-- ========================= -->

                                    <td class="text-center">

                                        <a
                                            href="<?= $_ENV['URL_ADM']; ?>view-purchase-document/<?= (int) $purchaseDocument['id']; ?>"
                                            class="btn btn-primary btn-sm"
                                            title="Visualizar lançamento">

                                            <i class="fa-solid fa-eye"></i>

                                        </a>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>

                    </table>

                    <?php

                    /*
                    * Paginação padrão do sistema.
                    */
                    require_once
                        './app/admsDaman/Views/partials/pagination.php';

                    ?>

                </div>


            <?php else: ?>


                <div
                    class="alert alert-info mb-0">

                    <i class="fa-solid fa-circle-info me-1"></i>

                    Nenhum lançamento financeiro encontrado.

                </div>


            <?php endif; ?>


        </div>

    </div>

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
                            Dar Baixa na Parcela

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
                    O conteúdo é preenchido pelo JavaScript
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