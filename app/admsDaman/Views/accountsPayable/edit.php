<?php

use App\admsDaman\Helpers\CSRFHelper;


$purchaseDocument =
    $this->data['purchaseDocument']
    ?? [];

$installments =
    $this->data['installments']
    ?? [];

$allocations =
    $this->data['allocations']
    ?? [];

$buyers =
    $this->data['getPurchaseUsersSelect']
    ?? [];

$form =
    $this->data['form']
    ?? [];

$hasPost =
    !empty($form);

$postedInstallments =
    is_array(
        $form['installments']
        ?? null
    )
        ? $form['installments']
        : [];

$isPaymentSchedulePending =
    (
        $purchaseDocument['payment_schedule_status']
        ?? 'confirmed'
    ) === 'pending';

$hasFinancialMovement =
    !empty(
        $this->data['hasFinancialMovement']
    );

$isManual =
    (
        $purchaseDocument['document_origin']
        ?? ''
    ) === 'MANUAL';


$currentBuyerId =
    (int) (
        $form['adms_daman_user_id']
        ?? $purchaseDocument['adms_daman_user_id']
        ?? 0
    );

$currentPurchaseDate =
    (string) (
        $form['purchase_date']
        ?? $purchaseDocument['purchase_date']
        ?? ''
    );

$currentObservation =
    (string) (
        $form['observation']
        ?? $purchaseDocument['observation']
        ?? ''
    );


/*
 * ============================================================
 * STATUS DINÂMICO
 * ============================================================
 */
$getInstallmentStatus =
    function (
        string $nature,
        ?string $dueDate,
        string $storedStatus = 'AV'
    ): string {

        $storedStatus =
            strtoupper(
                trim(
                    $storedStatus
                )
            );


        if ($storedStatus === 'OK') {
            return 'OK';
        }


        if ($nature === 'AP') {
            return 'AP';
        }


        if (empty($dueDate)) {
            return 'AV';
        }


        $today =
            new DateTimeImmutable(
                'today'
            );


        $date =
            new DateTimeImmutable(
                $dueDate
            );


        if ($date < $today) {
            return 'ON';
        }


        $days =
            (int) $today
                ->diff($date)
                ->days;


        if ($days <= 7) {
            return 'AT';
        }


        return 'AV';
    };


$statusMeta = [

    'AV' => [
        'label' => 'A Vencer',
        'class' => 'bg-primary',
    ],

    'AT' => [
        'label' => 'Atenção',
        'class' => 'bg-warning text-dark',
    ],

    'ON' => [
        'label' => 'Vencida',
        'class' => 'bg-danger',
    ],

    'OK' => [
        'label' => 'Pago',
        'class' => 'bg-success',
    ],

    'AP' => [
        'label' => 'Permuta',
        'class' => 'bg-info text-dark',
    ],
];


$totalValue =
    (float) (
        $purchaseDocument['total_value']
        ?? 0
    );

$totalValueCents =
    (int) round(
        $totalValue * 100
    );


/*
 * ============================================================
 * INDEXAR POST POR ID EXISTENTE
 * ============================================================
 */
$postedExistingById = [];

$postedNewItems = [];


foreach (
    $postedInstallments
    as $formKey => $postedInstallment
) {

    if (!is_array($postedInstallment)) {
        continue;
    }


    $postedId =
        (int) (
            $postedInstallment['id']
            ?? 0
        );


    if ($postedId > 0) {

        $postedExistingById[$postedId] = [
            'form_key' =>
                (string) $formKey,

            'data' =>
                $postedInstallment,
        ];

    } else {

        $postedNewItems[] = [
            'form_key' =>
                (string) $formKey,

            'data' =>
                $postedInstallment,
        ];
    }
}


/*
 * ============================================================
 * MONTAR ITENS DA TELA
 * ============================================================
 *
 * Em erro de validação:
 *
 * - parcela editável removida do POST permanece visualmente
 *   excluída;
 * - parcela bloqueada sempre reaparece;
 * - novas parcelas informadas são restauradas.
 */
$displayInstallments = [];


foreach ($installments as $installment) {

    $installmentId =
        (int) (
            $installment['id']
            ?? 0
        );


    $isLocked =
        !empty(
            $installment['edit_locked']
        );


    if (
        $hasPost
        &&
        !$isLocked
        &&
        !isset(
            $postedExistingById[$installmentId]
        )
    ) {

        /*
         * Parcela livre removida antes de uma validação falhar.
         */
        continue;
    }


    $postedData =
        $postedExistingById[$installmentId]['data']
        ?? [];


    $formKey =
        $postedExistingById[$installmentId]['form_key']
        ?? (string) $installmentId;


    $storedStatus =
        strtoupper(
            (string) (
                $installment['status']
                ?? 'AV'
            )
        );


    if ($isLocked) {

        $nature =
            $storedStatus === 'AP'
                ? 'AP'
                : 'NORMAL';

        $dueDate =
            (string) (
                $installment['due_date']
                ?? ''
            );

        $amount =
            number_format(
                (float) (
                    $installment['original_amount']
                    ?? 0
                ),
                2,
                ',',
                '.'
            );

    } else {

        $nature =
            strtoupper(
                (string) (
                    $postedData['nature']
                    ?? (
                        $storedStatus === 'AP'
                            ? 'AP'
                            : 'NORMAL'
                    )
                )
            );

        $dueDate =
            (string) (
                $postedData['due_date']
                ?? $installment['due_date']
                ?? ''
            );

        $amount =
            (string) (
                $postedData['original_amount']
                ?? number_format(
                    (float) (
                        $installment['original_amount']
                        ?? 0
                    ),
                    2,
                    ',',
                    '.'
                )
            );
    }


    $displayStatus =
        $getInstallmentStatus(
            $nature,
            $dueDate !== ''
                ? $dueDate
                : null,
            $storedStatus
        );


    $displayInstallments[] = [
        'form_key' =>
            $formKey,

        'id' =>
            $installmentId,

        'is_new' =>
            false,

        'is_locked' =>
            $isLocked,

        'installment_number' =>
            (int) (
                $installment['installment_number']
                ?? 0
            ),

        'nature' =>
            $nature,

        'due_date' =>
            $dueDate,

        'original_amount' =>
            $amount,

        'display_status' =>
            $displayStatus,

        'payment_history_count' =>
            (int) (
                $installment['payment_history_count']
                ?? 0
            ),
    ];
}


/*
 * Restaurar novas parcelas após erro.
 */
foreach ($postedNewItems as $postedNewItem) {

    $postedData =
        $postedNewItem['data'];


    $nature =
        strtoupper(
            (string) (
                $postedData['nature']
                ?? 'NORMAL'
            )
        );


    $dueDate =
        (string) (
            $postedData['due_date']
            ?? ''
        );


    $amount =
        (string) (
            $postedData['original_amount']
            ?? '0,00'
        );


    $displayInstallments[] = [
        'form_key' =>
            $postedNewItem['form_key'],

        'id' =>
            0,

        'is_new' =>
            true,

        'is_locked' =>
            false,

        'installment_number' =>
            null,

        'nature' =>
            $nature,

        'due_date' =>
            $dueDate,

        'original_amount' =>
            $amount,

        'display_status' =>
            $getInstallmentStatus(
                $nature,
                $dueDate !== ''
                    ? $dueDate
                    : null
            ),

        'payment_history_count' =>
            0,
    ];
}


/*
 * ============================================================
 * COMPRADOR ATUAL
 * ============================================================
 */
$currentBuyerExists =
    false;


foreach ($buyers as $buyer) {

    if (
        (int) (
            $buyer['id']
            ?? 0
        )
        === $currentBuyerId
    ) {

        $currentBuyerExists =
            true;

        break;
    }
}


/*
 * Próximo índice para novas parcelas criadas via JS.
 */
$nextNewIndex =
    1;


foreach ($postedNewItems as $postedNewItem) {

    if (
        preg_match(
            '/new_(\d+)/',
            $postedNewItem['form_key'],
            $matches
        )
    ) {

        $nextNewIndex =
            max(
                $nextNewIndex,
                ((int) $matches[1]) + 1
            );
    }
}

?>


<div class="container-fluid px-4">


    <div
        class="mb-1 d-flex flex-column flex-sm-row gap-2">

        <div>

            <h2 class="mt-3 mb-1">
                Editar Lançamento
            </h2>

            <p class="text-muted mb-3">
                Alterar dados administrativos e organizar parcelas ainda não movimentadas
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


            <li class="breadcrumb-item">

                <a
                    class="text-decoration-none"
                    href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents">

                    Compras

                </a>

            </li>


            <li
                class="breadcrumb-item active"
                aria-current="page">

                Editar

            </li>

        </ol>

    </div>


    <?php

    include './app/admsDaman/Views/partials/alerts.php';

    ?>


    <form
        method="POST"
        action=""
        id="editPurchaseDocumentForm"
        data-total-cents="<?= $totalValueCents; ?>"
        data-payment-schedule-status="<?= htmlspecialchars(
            $purchaseDocument['payment_schedule_status']
            ?? 'confirmed'
        ); ?>">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                        CSRFHelper::generateCSRFToken(
                            'form_edit_purchase_document'
                        )
                    ); ?>">


        <!-- ================================================== -->
        <!-- LANÇAMENTO                                         -->
        <!-- ================================================== -->

        <div class="card mb-4 border-light shadow">

            <div class="card-header hstack gap-2">

                <span>

                    <i class="fa-solid fa-file-pen me-2"></i>

                    <strong>
                        Lançamento
                    </strong>

                </span>


                <span class="ms-auto">

                    <?php if ($hasFinancialMovement): ?>

                        <span class="badge bg-warning text-dark">

                            <i class="fa-solid fa-lock me-1"></i>

                            Possui movimentação financeira

                        </span>

                    <?php else: ?>

                        <span class="badge bg-success">

                            <i class="fa-solid fa-unlock me-1"></i>

                            Sem movimentação financeira

                        </span>

                    <?php endif; ?>

                </span>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <div class="col-md-2">

                        <label class="form-label text-muted">
                            Origem
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= $isManual
                                        ? 'Compra Manual'
                                        : 'NF-e'; ?>"
                            readonly>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label text-muted">
                            Documento
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars(
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
                                    ); ?>"
                            readonly>

                    </div>


                    <div class="col-md-4">

                        <label class="form-label text-muted">
                            Fornecedor
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                        $purchaseDocument['supplier_name']
                                        ?? $purchaseDocument['issuer_name']
                                        ?? '-'
                                    ); ?>"
                            readonly>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label text-muted">
                            Valor Total
                        </label>

                        <input
                            type="text"
                            class="form-control fw-bold"
                            value="R$ <?= number_format(
                                            $totalValue,
                                            2,
                                            ',',
                                            '.'
                                        ); ?>"
                            readonly>

                    </div>


                    <div class="col-md-4">

                        <label
                            for="adms_daman_user_id"
                            class="form-label">

                            Comprador

                        </label>


                        <select
                            name="adms_daman_user_id"
                            id="adms_daman_user_id"
                            class="form-select">

                            <option value="">
                                Selecione
                            </option>


                            <?php if (
                                $currentBuyerId > 0
                                &&
                                !$currentBuyerExists
                            ): ?>

                                <option
                                    value="<?= $currentBuyerId; ?>"
                                    selected>

                                    <?= htmlspecialchars(
                                        $purchaseDocument['buyer_name']
                                        ?? 'Comprador atual'
                                    ); ?>

                                </option>

                            <?php endif; ?>


                            <?php foreach ($buyers as $buyer): ?>

                                <option
                                    value="<?= (int) (
                                                $buyer['id']
                                                ?? 0
                                            ); ?>"
                                    <?= (
                                        (int) (
                                            $buyer['id']
                                            ?? 0
                                        )
                                        === $currentBuyerId
                                    )
                                        ? 'selected'
                                        : ''; ?>>

                                    <?= htmlspecialchars(
                                        $buyer['name']
                                        ?? ''
                                    ); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label
                            for="purchase_date"
                            class="form-label">

                            Data da Compra

                        </label>

                        <input
                            type="date"
                            name="purchase_date"
                            id="purchase_date"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                        $currentPurchaseDate
                                    ); ?>">

                    </div>


                    <div class="col-md-5">

                        <label class="form-label text-muted">
                            Condição de Pagamento
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                        $purchaseDocument['payment_method_name']
                                        ?? (
                                            $isPaymentSchedulePending
                                                ? 'FB - Falta boleto'
                                                : '-'
                                        )
                                    ); ?>"
                            readonly>

                    </div>


                    <div class="col-12">

                        <label
                            for="observation"
                            class="form-label">

                            Observação

                        </label>

                        <textarea
                            name="observation"
                            id="observation"
                            class="form-control"
                            rows="3"><?= htmlspecialchars(
                                        $currentObservation
                                    ); ?></textarea>

                    </div>

                </div>

            </div>

        </div>


        <!-- ================================================== -->
        <!-- RATEIO                                             -->
        <!-- ================================================== -->

        <div class="card mb-4 border-light shadow">

            <div class="card-header hstack gap-2">

                <span>

                    <i class="fa-solid fa-code-branch me-2"></i>

                    <strong>
                        Rateio entre Obras
                    </strong>

                </span>


                <span class="ms-auto">

                    <span class="badge bg-warning text-dark">

                        <i class="fa-solid fa-lock me-1"></i>

                        Não editável

                    </span>

                </span>

            </div>


            <div class="card-body">

                <?php if (!empty($allocations)): ?>

                    <div class="table-responsive">

                        <table
                            class="table table-sm align-middle mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        Obra
                                    </th>

                                    <th class="text-end">
                                        Valor
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach (
                                    $allocations
                                    as $allocation
                                ): ?>

                                    <tr>

                                        <td>

                                            <?= htmlspecialchars(
                                                $allocation['project_name']
                                                ?? '-'
                                            ); ?>

                                        </td>

                                        <td class="text-end">

                                            R$
                                            <?= number_format(
                                                (float) (
                                                    $allocation['allocated_amount']
                                                    ?? 0
                                                ),
                                                2,
                                                ',',
                                                '.'
                                            ); ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="text-muted">
                        Nenhum rateio encontrado para este lançamento.
                    </div>

                <?php endif; ?>


                <div class="alert alert-warning mt-3 mb-0">

                    <div class="d-flex gap-2">

                        <i class="fa-solid fa-lock mt-1"></i>

                        <div>

                            <strong>
                                O rateio não pode ser editado.
                            </strong>

                            <div class="small mt-1">

                                Caso a obra ou o rateio estejam incorretos,
                                exclua o lançamento e realize um novo cadastro
                                antes de qualquer movimentação financeira.

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- ================================================== -->
        <!-- PARCELAS                                           -->
        <!-- ================================================== -->

        <div class="card mb-4 border-light shadow">

            <div class="card-header hstack gap-2">

                <span>

                    <i class="fa-solid fa-calendar-days me-2"></i>

                    <strong>
                        Parcelas
                    </strong>

                </span>


                <span
                    class="badge bg-secondary ms-auto"
                    id="editInstallmentCount">

                    <?= count($displayInstallments); ?>

                    parcela<?= count($displayInstallments) !== 1
                                ? 's'
                                : ''; ?>

                </span>


                <?php if (!$isPaymentSchedulePending): ?>

                    <button
                        type="button"
                        id="btnAddEditInstallment"
                        class="btn btn-success btn-sm">

                        <i class="fa-solid fa-plus me-1"></i>

                        Adicionar Parcela

                    </button>

                <?php endif; ?>

            </div>


            <div class="card-body">


                <?php if ($isPaymentSchedulePending): ?>

                    <div class="alert alert-warning mb-0">

                        <i class="fa-solid fa-clock me-1"></i>

                        Este lançamento ainda está como FB.
                        As parcelas devem ser definidas pelo fluxo
                        <strong>Confirmar Parcelas</strong>.

                    </div>


                <?php else: ?>

                    <div
                        class="row g-3"
                        id="editInstallmentsContainer"
                        data-next-new-index="<?= $nextNewIndex; ?>">

                        <?php foreach (
                            $displayInstallments
                            as $item
                        ): ?>

                            <?php
                            include './app/admsDaman/Views/accountsPayable/partials/edit_installment_card.php';
                            ?>

                        <?php endforeach; ?>

                    </div>


                    <div
                        class="alert alert-warning mt-3 d-none"
                        id="editNoInstallmentsWarning">

                        O lançamento precisa permanecer
                        com ao menos uma parcela.

                    </div>


                    <hr class="my-4">


                    <div class="row">

                        <div class="col-lg-5 col-xl-4 ms-auto">


                            <div
                                class="d-flex justify-content-between mb-2">

                                <span class="text-muted">
                                    Valor do Documento:
                                </span>

                                <strong>

                                    R$
                                    <?= number_format(
                                        $totalValue,
                                        2,
                                        ',',
                                        '.'
                                    ); ?>

                                </strong>

                            </div>


                            <div
                                class="d-flex justify-content-between mb-2">

                                <span class="text-muted">
                                    Soma das Parcelas:
                                </span>

                                <strong
                                    id="editInstallmentsTotal">

                                    R$ 0,00

                                </strong>

                            </div>


                            <div
                                class="d-flex justify-content-between">

                                <span class="text-muted">
                                    Diferença:
                                </span>

                                <strong
                                    id="editInstallmentsDifference">

                                    R$ 0,00

                                </strong>

                            </div>


                            <div class="form-text mt-2">

                                Parcelas movimentadas ficam congeladas.
                                As demais podem ser redistribuídas, excluídas
                                ou substituídas por novas parcelas.

                            </div>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <div
            class="d-flex flex-wrap justify-content-end gap-2 mb-4">

            <a
                href="<?= $_ENV['URL_ADM']; ?>view-purchase-document/<?= (int) (
                            $purchaseDocument['id']
                            ?? 0
                        ); ?>"
                class="btn btn-outline-secondary">

                <i class="fa-solid fa-arrow-left me-1"></i>

                Cancelar

            </a>


            <button
                type="submit"
                class="btn btn-success"
                id="btnSaveEditPurchaseDocument">

                <i class="fa-solid fa-floppy-disk me-1"></i>

                Salvar Alterações

            </button>

        </div>

    </form>

</div>
