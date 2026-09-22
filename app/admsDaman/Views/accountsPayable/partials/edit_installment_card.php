<?php

/**
 * Variáveis recebidas do escopo da View edit.php.
 *
 * @var array $item
 * @var array<string, array{label: string, class: string}> $statusMeta
 */

/*
 * Variáveis esperadas:
 *
 * $item
 * $statusMeta
 */

$formKey =
    (string) (
        $item['form_key']
        ?? ''
    );

$isLocked =
    !empty(
        $item['is_locked']
    );

$isNew =
    !empty(
        $item['is_new']
    );

$installmentId =
    (int) (
        $item['id']
        ?? 0
    );

$installmentNumber =
    $item['installment_number']
    ?? null;

$nature =
    strtoupper(
        (string) (
            $item['nature']
            ?? 'NORMAL'
        )
    );

$dueDate =
    (string) (
        $item['due_date']
        ?? ''
    );

$amount =
    (string) (
        $item['original_amount']
        ?? '0,00'
    );

$displayStatus =
    (string) (
        $item['display_status']
        ?? (
            $nature === 'AP'
                ? 'AP'
                : 'AV'
        )
    );

$currentStatusMeta =
    $statusMeta[$displayStatus]
    ?? $statusMeta['AV'];

$historyCount =
    (int) (
        $item['payment_history_count']
        ?? 0
    );

?>

<div
    class="col-xl-4 col-md-6 edit-installment-card"
    data-locked="<?= $isLocked ? '1' : '0'; ?>"
    data-new="<?= $isNew ? '1' : '0'; ?>"
    data-installment-number="<?= $installmentNumber !== null
        ? (int) $installmentNumber
        : ''; ?>">

    <div
        class="card h-100 <?= $isLocked
            ? 'border-warning'
            : 'border-success'; ?>">

        <div
            class="card-header d-flex align-items-center gap-2">

            <strong class="edit-installment-title">

                <?php if ($isNew): ?>

                    Nova Parcela

                <?php else: ?>

                    <?= (int) $installmentNumber; ?>ª Parcela

                <?php endif; ?>

            </strong>


            <span
                class="badge <?= $currentStatusMeta['class']; ?> edit-installment-status-badge">

                <?= htmlspecialchars(
                    $displayStatus
                ); ?>

            </span>


            <?php if ($isNew): ?>

                <span class="badge bg-secondary">
                    Número automático
                </span>

            <?php endif; ?>


            <span class="ms-auto">

                <?php if ($isLocked): ?>

                    <span
                        class="badge bg-warning text-dark">

                        <i class="fa-solid fa-lock me-1"></i>
                        Bloqueada

                    </span>

                <?php else: ?>

                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm btn-remove-edit-installment"
                        title="<?= $isNew
                            ? 'Remover nova parcela'
                            : 'Excluir parcela'; ?>">

                        <i class="fa-solid fa-trash"></i>

                    </button>

                <?php endif; ?>

            </span>

        </div>


        <div class="card-body">

            <input
                type="hidden"
                name="installments[<?= htmlspecialchars($formKey); ?>][id]"
                value="<?= $installmentId; ?>">


            <?php if ($isLocked): ?>

                <input
                    type="hidden"
                    name="installments[<?= htmlspecialchars($formKey); ?>][original_amount]"
                    value="<?= htmlspecialchars($amount); ?>">

                <input
                    type="hidden"
                    name="installments[<?= htmlspecialchars($formKey); ?>][due_date]"
                    value="<?= htmlspecialchars($dueDate); ?>">

                <input
                    type="hidden"
                    name="installments[<?= htmlspecialchars($formKey); ?>][nature]"
                    value="<?= htmlspecialchars($nature); ?>">

            <?php endif; ?>


            <!-- Natureza -->
            <div class="mb-3">

                <label class="form-label">
                    Natureza
                </label>


                <?php if ($isLocked): ?>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= $nature === 'AP'
                            ? 'Permuta'
                            : 'Parcela normal'; ?>"
                        readonly>

                <?php else: ?>

                    <select
                        name="installments[<?= htmlspecialchars($formKey); ?>][nature]"
                        class="form-select edit-installment-nature">

                        <option
                            value="NORMAL"
                            <?= $nature === 'NORMAL'
                                ? 'selected'
                                : ''; ?>>

                            Parcela normal

                        </option>

                        <option
                            value="AP"
                            <?= $nature === 'AP'
                                ? 'selected'
                                : ''; ?>>

                            Permuta (AP)

                        </option>

                    </select>

                <?php endif; ?>

            </div>


            <!-- Vencimento -->
            <div class="mb-3">

                <label class="form-label">
                    Vencimento
                </label>

                <input
                    type="date"
                    <?= !$isLocked
                        ? 'name="installments['
                            . htmlspecialchars($formKey)
                            . '][due_date]"'
                        : ''; ?>
                    class="form-control edit-installment-due-date"
                    value="<?= htmlspecialchars($dueDate); ?>"
                    <?= $isLocked
                        ? 'readonly'
                        : ''; ?>
                    <?= (
                        !$isLocked
                        &&
                        $nature === 'AP'
                    )
                        ? 'disabled'
                        : ''; ?>>

                <?php if (
                    !$isLocked
                    &&
                    $nature === 'AP'
                ): ?>

                    <div class="form-text">
                        AP permanece sem vencimento definido.
                    </div>

                <?php endif; ?>

            </div>


            <!-- Valor -->
            <div class="mb-3">

                <label class="form-label">
                    Valor Original
                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        R$
                    </span>

                    <input
                        type="text"
                        <?= !$isLocked
                            ? 'name="installments['
                                . htmlspecialchars($formKey)
                                . '][original_amount]"'
                            : ''; ?>
                        class="form-control fw-bold edit-installment-amount"
                        inputmode="decimal"
                        value="<?= htmlspecialchars($amount); ?>"
                        <?= $isLocked
                            ? 'readonly'
                            : ''; ?>>

                </div>

            </div>


            <!-- Status -->
            <div class="mb-3">

                <label class="form-label">
                    Status atual
                </label>

                <input
                    type="text"
                    class="form-control edit-installment-status-text"
                    value="<?= htmlspecialchars(
                                $displayStatus
                                . ' - '
                                . $currentStatusMeta['label']
                            ); ?>"
                    readonly>

            </div>


            <?php if ($isLocked): ?>

                <div
                    class="alert alert-warning mb-0 py-2">

                    <div class="fw-semibold">

                        <i class="fa-solid fa-lock me-1"></i>

                        Dados financeiros preservados

                    </div>


                    <?php if ($historyCount > 0): ?>

                        <div class="small mt-1">

                            Esta parcela possui
                            <?= $historyCount; ?>
                            registro<?= $historyCount !== 1
                                        ? 's'
                                        : ''; ?>
                            no histórico de pagamento.

                        </div>

                    <?php else: ?>

                        <div class="small mt-1">
                            Esta parcela está marcada como OK.
                        </div>

                    <?php endif; ?>


                    <div class="small mt-1">

                        Valor, vencimento, natureza e existência
                        da parcela não podem ser alterados.

                    </div>

                </div>

            <?php else: ?>

                <div
                    class="alert alert-success mb-0 py-2 edit-installment-editable-message">

                    <div class="fw-semibold">

                        <i class="fa-solid fa-pen me-1"></i>

                        <?= $isNew
                            ? 'Nova parcela'
                            : 'Parcela disponível para edição'; ?>

                    </div>

                    <div class="small mt-1">

                        <?= $isNew
                            ? 'O número será atribuído automaticamente ao salvar.'
                            : 'Sem histórico financeiro: pode editar ou excluir.'; ?>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>
