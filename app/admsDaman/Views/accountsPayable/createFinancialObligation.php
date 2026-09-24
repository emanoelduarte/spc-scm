<?php

use App\admsDaman\Helpers\CSRFHelper;

$form =
    $this->data['form'] ?? [];

$projects =
    $this->data['getAllProjectsSelectActive'] ?? [];

$buyers =
    $this->data['getPurchaseUsersSelect'] ?? [];

$suppliers =
    $this->data['getFinancialObligationSuppliersSelect'] ?? [];

$paymentMethods =
    $this->data['getAllPaymentSelect'] ?? [];

$financialPaymentMethods =
    $this->data['getAllFinancialPaymentMethodsSelect'] ?? [];

$generationDate =
    $form['purchase_date']
    ?? date('Y-m-d');

$oldInstallments =
    $form['installments']
    ?? [];

$oldAllocations =
    $form['allocations']
    ?? [];

?>

<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <div>
            <h2 class="mt-3 mb-1">
                Nova Obrigação Financeira
            </h2>

            <p class="text-muted mb-3">
                Cadastro de impostos, taxas, guias e outras obrigações a pagar
            </p>
        </div>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">
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
                    Compras / Contas a Pagar
                </a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">
                Nova Obrigação Financeira
            </li>
        </ol>
    </div>

    <?php
    include './app/admsDaman/Views/partials/alerts.php';
    ?>

    <form
        id="manualPurchaseDocumentForm"
        method="POST"
        action="">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= CSRFHelper::generateCSRFToken(
                        'form_create_financial_obligation'
                    ); ?>">

        <div class="card mb-4 border-light shadow">
            <div class="card-header">
                <i class="fa-solid fa-file-invoice-dollar me-2"></i>
                <strong>Dados da Obrigação Financeira</strong>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <!-- Obra -->
                    <div class="col-xl-4 col-md-6">
                        <label
                            for="adms_daman_project_id"
                            class="form-label">
                            Obra
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="adms_daman_project_id"
                            id="adms_daman_project_id"
                            class="form-select"
                            required>

                            <option value="">Selecione</option>

                            <?php foreach ($projects as $project): ?>
                                <option
                                    value="<?= (int) $project['id']; ?>"
                                    <?= (
                                        (int) ($form['adms_daman_project_id'] ?? 0)
                                        === (int) $project['id']
                                    ) ? 'selected' : ''; ?>>

                                    <?= htmlspecialchars(
                                        (string) $project['name']
                                    ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="form-check mt-2">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="has_proration"
                                id="has_proration"
                                value="1"
                                <?= !empty($form['has_proration'])
                                    ? 'checked'
                                    : ''; ?>>

                            <label
                                class="form-check-label"
                                for="has_proration">
                                Existe rateio entre obras
                            </label>
                        </div>
                    </div>

                    <!-- Comprador / responsável -->
                    <div class="col-xl-4 col-md-6">
                        <label
                            for="adms_daman_user_id"
                            class="form-label">
                            Comprador
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="adms_daman_user_id"
                            id="adms_daman_user_id"
                            class="form-select"
                            required>

                            <option value="">Selecione</option>

                            <?php foreach ($buyers as $buyer): ?>
                                <option
                                    value="<?= (int) $buyer['id']; ?>"
                                    <?= (
                                        (int) ($form['adms_daman_user_id'] ?? 0)
                                        === (int) $buyer['id']
                                    ) ? 'selected' : ''; ?>>

                                    <?= htmlspecialchars(
                                        (string) $buyer['name']
                                    ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Número / Referência -->
                    <div class="col-xl-4 col-md-6">
                        <label
                            for="document_number"
                            class="form-label">
                            Número / Referência
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="document_number"
                            id="document_number"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                        (string) (
                                            $form['document_number']
                                            ?? ''
                                        )
                                    ); ?>"
                            placeholder="Ex: ISS 09/2026"
                            required>
                    </div>

                    <!-- Fornecedor / credor -->
                    <div class="col-xl-6 col-md-6">
                        <label
                            for="adms_daman_supplier_id"
                            class="form-label">
                            Fornecedor / Credor
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="adms_daman_supplier_id"
                            id="adms_daman_supplier_id"
                            class="form-select"
                            required>

                            <option value="">Selecione</option>

                            <?php foreach ($suppliers as $supplier): ?>
                                <option
                                    value="<?= (int) $supplier['id']; ?>"
                                    <?= (
                                        (int) ($form['adms_daman_supplier_id'] ?? 0)
                                        === (int) $supplier['id']
                                    ) ? 'selected' : ''; ?>>

                                    <?= htmlspecialchars(
                                        (string) $supplier['legal_name']
                                    ); ?>

                                    <?php if (!empty($supplier['cnpj'])): ?>
                                        - <?= htmlspecialchars(
                                            (string) $supplier['cnpj']
                                        ); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="form-text">
                            Somente fornecedores ativos do tipo
                            “Obrigação Financeira” são exibidos.
                        </div>
                    </div>

                    <!-- Data de geração -->
                    <div class="col-xl-3 col-md-6">
                        <label
                            for="purchase_date"
                            class="form-label">
                            Data de Geração
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="purchase_date"
                            id="purchase_date"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                        (string) $generationDate
                                    ); ?>"
                            required>
                    </div>

                    <!-- Valor -->
                    <div class="col-xl-3 col-md-6">
                        <label
                            for="total_value"
                            class="form-label">
                            Valor
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">R$</span>

                            <input
                                type="text"
                                name="total_value"
                                id="total_value"
                                class="form-control"
                                value="<?= htmlspecialchars(
                                            (string) (
                                                $form['total_value']
                                                ?? ''
                                            )
                                        ); ?>"
                                placeholder="0,00"
                                inputmode="decimal"
                                required>
                        </div>
                    </div>

                    <!-- Condição de pagamento -->
                    <div class="col-xl-6 col-md-6">
                        <label
                            for="adms_daman_payment_method_id"
                            class="form-label">
                            Condição de Pagamento
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="adms_daman_payment_method_id"
                            id="adms_daman_payment_method_id"
                            class="form-select">

                            <option value="">Selecione</option>

                            <?php foreach ($paymentMethods as $paymentMethod): ?>
                                <option
                                    value="<?= (int) $paymentMethod['id']; ?>"
                                    <?= (
                                        (int) (
                                            $form['adms_daman_payment_method_id']
                                            ?? 0
                                        )
                                        === (int) $paymentMethod['id']
                                    ) ? 'selected' : ''; ?>>

                                    <?= htmlspecialchars(
                                        (string) $paymentMethod['name']
                                    ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Falta boleto -->
                    <div class="col-md-12">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="payment_schedule_pending"
                                id="payment_schedule_pending"
                                value="1"
                                <?= !empty($form['payment_schedule_pending'])
                                    ? 'checked'
                                    : ''; ?>>

                            <label
                                class="form-check-label"
                                for="payment_schedule_pending">
                                Falta boleto / vencimentos ainda não confirmados
                            </label>
                        </div>

                        <div class="form-text">
                            Use quando a obrigação já existe, mas os vencimentos
                            ou valores das parcelas ainda precisam ser confirmados.
                        </div>
                    </div>

                    <!-- ====================================================== -->
                    <!-- RATEIO ENTRE OBRAS                                    -->
                    <!-- ====================================================== -->

                    <div
                        id="purchaseAllocationSection"
                        class="col-12 <?= !empty($form['has_proration'])
                                            ? ''
                                            : 'd-none'; ?>">

                        <div class="border rounded p-3">

                            <div
                                class="d-flex flex-column flex-md-row align-items-md-center gap-2 mb-3">

                                <div>
                                    <div class="fw-semibold">
                                        <i class="fa-solid fa-code-branch me-1"></i>
                                        Rateio entre obras
                                    </div>

                                    <div class="small text-muted">
                                        Distribua o valor total da obrigação entre as obras participantes.
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    id="btnAddPurchaseAllocation"
                                    class="btn btn-outline-primary btn-sm ms-md-auto">
                                    <i class="fa-solid fa-plus me-1"></i>
                                    Adicionar obra
                                </button>
                            </div>

                            <div
                                id="purchaseAllocationRows"
                                class="d-flex flex-column gap-2">
                            </div>

                            <hr class="my-3">

                            <div class="row">
                                <div class="col-md-6 col-lg-5 ms-auto">

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">
                                            Valor da Obrigação:
                                        </span>
                                        <strong id="purchaseAllocationDocumentTotal">
                                            R$ 0,00
                                        </strong>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">
                                            Total Rateado:
                                        </span>
                                        <strong
                                            id="purchaseAllocationTotal"
                                            class="text-success">
                                            R$ 0,00
                                        </strong>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            Diferença:
                                        </span>
                                        <strong
                                            id="purchaseAllocationDifference"
                                            class="text-danger">
                                            R$ 0,00
                                        </strong>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Observação -->
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
                            rows="3"
                            placeholder="Ex: Guia mensal de ISS, competência 09/2026..."><?= htmlspecialchars(
                                (string) ($form['observation'] ?? '')
                            ); ?></textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a
                        href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents"
                        class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i>
                        Voltar
                    </a>

                    <button
                        type="button"
                        id="btnGenerateManualInstallments"
                        class="btn btn-primary"
                        data-endpoint="<?= $_ENV['URL_ADM']; ?>get-payment-method-items">
                        <i class="fa-solid fa-arrow-right me-1"></i>
                        Gerar Parcelas
                    </button>

                    <button
                        type="submit"
                        form="manualPurchaseDocumentForm"
                        id="btnSavePendingPurchaseDocument"
                        class="btn btn-warning d-none">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        Salvar com pendência
                    </button>
                </div>
            </div>
        </div>

        <!-- Configuração das parcelas -->
        <div
            id="manualInstallmentsPreview"
            class="card mb-4 border-light shadow d-none">

            <div class="card-header">
                <i class="fa-solid fa-calendar-days me-2"></i>
                <strong>Configuração das Parcelas</strong>
            </div>

            <div class="card-body">
                <div
                    id="manualInstallmentsContainer"
                    class="row g-3">
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-5 col-lg-4 ms-auto">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">
                                Valor da Obrigação:
                            </span>
                            <strong id="manualInstallmentsTotalPurchase">
                                R$ 0,00
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">
                                Soma das Parcelas:
                            </span>
                            <strong
                                id="manualInstallmentsTotal"
                                class="text-success">
                                R$ 0,00
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted">
                                Diferença:
                            </span>
                            <strong
                                id="manualInstallmentsDifference"
                                class="text-success">
                                R$ 0,00
                            </strong>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a
                        href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents"
                        class="btn btn-outline-secondary">
                        <i class="fa-solid fa-xmark me-1"></i>
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        form="manualPurchaseDocumentForm"
                        id="btnSaveManualPurchaseDocument"
                        class="btn btn-success"
                        disabled>
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        Salvar Obrigação
                    </button>
                </div>
            </div>
        </div>

    </form>

    <!-- Dados utilizados pelo JS compartilhado de parcelamento -->
    <script
        type="application/json"
        id="manualInstallmentsOldData"><?= json_encode(
            $oldInstallments,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
        ); ?></script>

    <script
        type="application/json"
        id="purchaseAllocationsOldData"><?= json_encode(
            $oldAllocations,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
        ); ?></script>

    <script
        type="application/json"
        id="financialPaymentMethodsData"><?= json_encode(
            $financialPaymentMethods,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
        ); ?></script>

</div>
