<?php

use App\admsDaman\Helpers\CSRFHelper;

$form = $this->data['form'] ?? [];
$suppliers = $this->data['suppliers'] ?? [];

$selectedSupplierId =
    (int) ($form['adms_daman_supplier_id'] ?? 0);

$agreementDate =
    $form['agreement_date'] ?? date('Y-m-d');

$sourceInstallmentIds =
    is_array($form['source_installment_ids'] ?? null)
        ? array_values(array_map('intval', $form['source_installment_ids']))
        : [];

$oldInstallments =
    is_array($form['installments'] ?? null)
        ? $form['installments']
        : [];

?>

<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <div>
            <h2 class="mt-3 mb-1">Nova Renegociação Financeira</h2>
            <p class="text-muted mb-3">
                Consolide parcelas existentes do mesmo fornecedor em um novo acordo.
            </p>
        </div>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM']; ?>dashboard">
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents">
                    Compras / Contas a Pagar
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Renegociação
            </li>
        </ol>
    </div>

    <?php include './app/admsDaman/Views/partials/alerts.php'; ?>

    <div class="alert alert-info py-2">
        <i class="fa-solid fa-circle-info me-1"></i>
        As parcelas selecionadas serão mantidas no histórico com status
        <strong>RN - Renegociada</strong>. Elas não serão marcadas como pagas.
        Somente o saldo principal ainda não liquidado será incorporado ao novo acordo.
    </div>

    <form
        method="POST"
        id="financialRenegotiationForm"
        action="<?= $_ENV['URL_ADM']; ?>create-financial-renegotiation"
        data-installments-url="<?= $_ENV['URL_ADM']; ?>get-financial-renegotiation-installments/">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= CSRFHelper::generateCSRFToken('form_create_financial_renegotiation'); ?>">

        <div class="card mb-3 border-light shadow">
            <div class="card-header">
                <i class="fa-solid fa-handshake-angle me-1"></i>
                <strong>Dados do Acordo</strong>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-5">
                        <label for="adms_daman_supplier_id" class="form-label">
                            Fornecedor <span class="text-danger">*</span>
                        </label>
                        <select
                            class="form-select"
                            name="adms_daman_supplier_id"
                            id="adms_daman_supplier_id"
                            required>
                            <option value="">Selecione</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option
                                    value="<?= (int) $supplier['id']; ?>"
                                    <?= $selectedSupplierId === (int) $supplier['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars((string) ($supplier['legal_name'] ?? '')); ?>
                                    <?php if (!empty($supplier['cnpj'])): ?>
                                        - <?= htmlspecialchars((string) $supplier['cnpj']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            São exibidos apenas fornecedores com parcelas disponíveis para renegociação.
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <label for="agreement_number" class="form-label">
                            Número / Referência <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            name="agreement_number"
                            id="agreement_number"
                            maxlength="100"
                            placeholder="Ex: ACORDO 10/2026"
                            value="<?= htmlspecialchars((string) ($form['agreement_number'] ?? '')); ?>"
                            required>
                    </div>

                    <div class="col-lg-3">
                        <label for="agreement_date" class="form-label">
                            Data da Negociação <span class="text-danger">*</span>
                        </label>
                        <input
                            type="date"
                            class="form-control"
                            name="agreement_date"
                            id="agreement_date"
                            value="<?= htmlspecialchars((string) $agreementDate); ?>"
                            required>
                    </div>

                    <div class="col-12">
                        <label for="observation" class="form-label">Observação</label>
                        <textarea
                            class="form-control"
                            name="observation"
                            id="observation"
                            rows="2"
                            placeholder="Ex: Consolidação das parcelas de agosto e setembro, conforme acordo com o fornecedor."><?= htmlspecialchars((string) ($form['observation'] ?? '')); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 border-light shadow">
            <div class="card-header d-flex align-items-center gap-2">
                <span>
                    <i class="fa-solid fa-list-check me-1"></i>
                    <strong>Parcelas de Origem</strong>
                </span>

                <span class="ms-auto badge bg-secondary" id="renegotiationSelectionBadge">
                    0 selecionadas
                </span>
            </div>

            <div class="card-body p-0">
                <div
                    id="renegotiationInstallmentsMessage"
                    class="p-4 text-center text-muted">
                    Selecione um fornecedor para carregar as parcelas disponíveis.
                </div>

                <div
                    id="renegotiationInstallmentsTableWrapper"
                    class="table-responsive d-none">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 42px;" class="text-center">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="selectAllRenegotiationInstallments"
                                        title="Selecionar todas">
                                </th>
                                <th>Documento</th>
                                <th>Parcela</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                                <th>Obra(s)</th>
                                <th class="text-end">Valor Original</th>
                                <th class="text-end">Já Pago</th>
                                <th class="text-end">Saldo a Renegociar</th>
                            </tr>
                        </thead>
                        <tbody id="renegotiationInstallmentsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3 border-light shadow">
            <div class="card-header">
                <i class="fa-solid fa-calculator me-1"></i>
                <strong>Resumo da Renegociação</strong>
            </div>

            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label">Principal Selecionado</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input
                                type="text"
                                class="form-control"
                                id="selectedPrincipalAmount"
                                value="0,00"
                                readonly>
                        </div>
                        <div class="form-text" id="selectedDocumentsSummary">
                            0 documentos / 0 parcelas
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4">
                        <label for="interest_amount" class="form-label">Juros</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input
                                type="text"
                                class="form-control renegotiation-money"
                                name="interest_amount"
                                id="interest_amount"
                                value="<?= htmlspecialchars((string) ($form['interest_amount'] ?? '0,00')); ?>">
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4">
                        <label for="penalty_amount" class="form-label">Multa</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input
                                type="text"
                                class="form-control renegotiation-money"
                                name="penalty_amount"
                                id="penalty_amount"
                                value="<?= htmlspecialchars((string) ($form['penalty_amount'] ?? '0,00')); ?>">
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4">
                        <label for="discount_amount" class="form-label">Desconto</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input
                                type="text"
                                class="form-control renegotiation-money"
                                name="discount_amount"
                                id="discount_amount"
                                value="<?= htmlspecialchars((string) ($form['discount_amount'] ?? '0,00')); ?>">
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label class="form-label">Novo Valor do Acordo</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input
                                type="text"
                                class="form-control fw-bold"
                                id="renegotiatedAmount"
                                value="0,00"
                                readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border-light shadow">
            <div class="card-header">
                <i class="fa-solid fa-calendar-days me-1"></i>
                <strong>Novo Parcelamento</strong>
            </div>

            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-4">
                        <label for="renegotiationInstallmentsCount" class="form-label">
                            Quantidade de Parcelas
                        </label>
                        <input
                            type="number"
                            class="form-control"
                            id="renegotiationInstallmentsCount"
                            min="1"
                            max="120"
                            value="<?= max(1, count($oldInstallments)); ?>">
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <label for="renegotiationFirstDueDate" class="form-label">
                            Primeiro Vencimento
                        </label>
                        <input
                            type="date"
                            class="form-control"
                            id="renegotiationFirstDueDate"
                            value="<?= htmlspecialchars((string) ($oldInstallments[0]['due_date'] ?? '')); ?>">
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <button
                            type="button"
                            class="btn btn-outline-primary w-100"
                            id="btnGenerateRenegotiationInstallments">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                            Gerar Parcelas
                        </button>
                    </div>
                </div>

                <div
                    id="renegotiationInstallmentsPreview"
                    class="table-responsive mt-3 <?= empty($oldInstallments) ? 'd-none' : ''; ?>">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Parcela</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody id="renegotiationNewInstallmentsBody"></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Total</th>
                                <th id="renegotiationInstallmentsTotal">R$ 0,00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="form-text mt-2">
                    As parcelas geradas podem ser ajustadas manualmente antes de confirmar.
                    A soma deve ser exatamente igual ao novo valor do acordo.
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a
                    href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents"
                    class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Voltar
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                    id="btnSaveFinancialRenegotiation"
                    disabled>
                    <i class="fa-solid fa-check me-1"></i>
                    Confirmar Renegociação
                </button>
            </div>
        </div>
    </form>
</div>

<script type="application/json" id="renegotiationOldSelectedIds">
<?= json_encode($sourceInstallmentIds, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<script type="application/json" id="renegotiationOldInstallments">
<?= json_encode($oldInstallments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<script src="<?= $_ENV['URL_ADM']; ?>public/admsDaman/js/financial_renegotiation.js"></script>
