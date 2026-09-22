<?php

use App\admsDaman\Helpers\CSRFHelper;

$nfe = $this->data['nfe'] ?? [];

$projects = $this->data['getAllProjectsSelectActive'] ?? [];
$buyers = $this->data['getPurchaseUsersSelect'] ?? [];
$paymentMethods = $this->data['getAllPaymentSelect'] ?? [];

$issueDate = !empty($nfe['issue_date'])
    ? date('d/m/Y H:i', strtotime($nfe['issue_date']))
    : '-';

$purchaseDate =
    $this->data['form']['purchase_date']
    ?? (
        !empty($nfe['issue_date'])
        ? date('Y-m-d', strtotime($nfe['issue_date']))
        : date('Y-m-d')
    );

?>

<div class="container-fluid px-4">

    <!-- ====================================================== -->
    <!-- TÍTULO                                                 -->
    <!-- ====================================================== -->

    <div class="mb-3 d-flex flex-column flex-sm-row gap-2">

        <div>

            <h2 class="mt-3 mb-1">
                Lançamento de Compra
            </h2>

            <span class="text-muted">
                Cadastro financeiro a partir de NF-e recebida
            </span>

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
                    href="<?= $_ENV['URL_ADM']; ?>list-nfes">

                    NF-e Recebidas

                </a>

            </li>

            <li
                class="breadcrumb-item active"
                aria-current="page">

                Lançar Compra

            </li>

        </ol>

    </div>


    <?php

    // Alertas do sistema
    include './app/admsDaman/Views/partials/alerts.php';

    ?>


    <!-- ====================================================== -->
    <!-- DADOS DA NF-e                                          -->
    <!-- ====================================================== -->

    <div class="card mb-4 border-light shadow">

        <div class="card-header">

            <i class="fa-solid fa-file-invoice me-2"></i>

            <span class="fw-semibold">
                Dados da NF-e
            </span>

        </div>

        <div class="card-body">

            <div class="row g-3">

                <!-- Número -->
                <div class="col-md-2">

                    <label class="form-label text-muted">
                        NF-e
                    </label>

                    <div class="fw-bold fs-5">

                        <?= htmlspecialchars(
                            $nfe['nfe_number'] ?? '-'
                        ); ?>

                    </div>

                </div>


                <!-- Série -->
                <div class="col-md-1">

                    <label class="form-label text-muted">
                        Série
                    </label>

                    <div>

                        <?= htmlspecialchars(
                            $nfe['series'] ?? '-'
                        ); ?>

                    </div>

                </div>


                <!-- Fornecedor -->
                <div class="col-md-4">

                    <label class="form-label text-muted">
                        Fornecedor
                    </label>

                    <div class="fw-semibold">

                        <?= htmlspecialchars(
                            $nfe['issuer_name'] ?? '-'
                        ); ?>

                    </div>

                    <small class="text-muted">

                        CNPJ:
                        <?= htmlspecialchars(
                            $nfe['issuer_cnpj'] ?? '-'
                        ); ?>

                    </small>

                </div>


                <!-- Emissão -->
                <div class="col-md-2">

                    <label class="form-label text-muted">
                        Emissão
                    </label>

                    <div>
                        <?= $issueDate; ?>
                    </div>

                </div>


                <!-- Valor -->
                <div class="col-md-3 text-md-end">

                    <label class="form-label text-muted">
                        Valor Total NF
                    </label>

                    <div class="fw-bold fs-4 text-success">

                        R$
                        <?= number_format(
                            (float) ($nfe['total_value'] ?? 0),
                            2,
                            ',',
                            '.'
                        ); ?>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- DADOS DO LANÇAMENTO                                    -->
    <!-- ====================================================== -->

    <div class="card mb-4 border-light shadow">

        <div class="card-header">

            <i class="fa-solid fa-cart-shopping me-2"></i>

            <span class="fw-semibold">
                Dados da Compra
            </span>

        </div>

        <div class="card-body">

            <!--
                Ainda não vamos processar o POST.
                Primeiro vamos validar visualmente os dados.
            -->
            <form id="purchaseDocumentForm" action="" method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= CSRFHelper::generateCSRFToken(
                                'form_create_purchase_document'
                            ); ?>">

                <input
                    type="hidden"
                    id="total_value"
                    value="<?= htmlspecialchars(
                                (string) (
                                    $nfe['total_value']
                                    ?? '0'
                                )
                            ); ?>">

                <input
                    type="hidden"
                    name="adms_daman_nfe_id"
                    value="<?= (int) ($nfe['id'] ?? 0); ?>">


                <div class="row g-3">

                    <!-- Obra -->
                    <div class="col-md-4">

                        <label
                            for="adms_daman_project_id"
                            class="form-label">

                            Obra
                            <span class="text-danger">*</span>

                        </label>

                        <select
                            name="adms_daman_project_id"
                            id="adms_daman_project_id"
                            class="form-select">

                            <option value="">
                                Selecione
                            </option>

                            <?php foreach ($projects as $project): ?>

                                <option
                                    value="<?= (int) $project['id']; ?>"
                                    <?= (
                                        isset($this->data['form']['adms_daman_project_id'])
                                        &&
                                        (int) $this->data['form']['adms_daman_project_id']
                                        === (int) $project['id']
                                    ) ? 'selected' : ''; ?>>

                                    <?= htmlspecialchars($project['name']); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-check mt-2">

                        <input
                            type="checkbox"
                            class="form-check-input"
                            name="has_proration"
                            id="has_proration"
                            value="1"
                            <?= !empty($this->data['form']['has_proration'])
                                ? 'checked'
                                : ''; ?>>

                        <label
                            class="form-check-label"
                            for="has_proration">

                            Existe rateio entre obras

                        </label>

                    </div>


                    <!-- Comprador -->
                    <div class="col-md-4">

                        <label
                            for="adms_daman_user_id"
                            class="form-label">

                            Comprador
                            <span class="text-danger">*</span>

                        </label>

                        <select
                            name="adms_daman_user_id"
                            id="adms_daman_user_id"
                            class="form-select">

                            <option value="">
                                Selecione
                            </option>

                            <?php foreach ($buyers as $buyer): ?>

                                <option
                                    value="<?= (int) $buyer['id']; ?>"
                                    <?= (
                                        isset($this->data['form']['adms_daman_user_id'])
                                        &&
                                        (int) $this->data['form']['adms_daman_user_id']
                                        === (int) $buyer['id']
                                    ) ? 'selected' : ''; ?>>

                                    <?= htmlspecialchars($buyer['name']); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Data da compra -->
                    <div class="col-md-4">

                        <label
                            for="purchase_date"
                            class="form-label">

                            Data da Compra
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="date"
                            name="purchase_date"
                            id="purchase_date"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                        $purchaseDate
                                    ); ?>">

                    </div>


                    <!-- Condição de pagamento -->
                    <div class="col-md-6">

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

                            <option value="">
                                Selecione
                            </option>

                            <?php foreach ($paymentMethods as $paymentMethod): ?>

                                <option
                                    value="<?= (int) $paymentMethod['id']; ?>"
                                    <?= (
                                        isset($this->data['form']['adms_daman_payment_method_id'])
                                        &&
                                        (int) $this->data['form']['adms_daman_payment_method_id']
                                        === (int) $paymentMethod['id']
                                    ) ? 'selected' : ''; ?>>

                                    <?= htmlspecialchars($paymentMethod['name']); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-12 mt-3">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="payment_schedule_pending"
                                id="payment_schedule_pending"
                                value="1"
                                <?= !empty($this->data['form']['payment_schedule_pending'])
                                    ? 'checked'
                                    : ''; ?>>

                            <label
                                class="form-check-label"
                                for="payment_schedule_pending">

                                Falta boleto / parcelas ainda não confirmadas

                            </label>

                        </div>

                        <div class="form-text">

                            Marque esta opção quando os vencimentos ou valores
                            das parcelas ainda precisarem ser confirmados.

                        </div>

                    </div>


                    <!-- Valor -->
                    <div class="col-md-3">

                        <label class="form-label">
                            Valor da NF-e
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="R$ <?= number_format(
                                            (float) ($nfe['total_value'] ?? 0),
                                            2,
                                            ',',
                                            '.'
                                        ); ?>"
                            readonly>

                    </div>


                    <!-- Status -->
                    <div class="col-md-3">

                        <label class="form-label">
                            Situação
                        </label>

                        <div>

                            <span class="badge bg-success mt-2">

                                <i class="fa-solid fa-circle-check me-1"></i>

                                NF-e Conferida

                            </span>

                        </div>

                    </div>

                    <!-- ====================================================== -->
                    <!-- RATEIO ENTRE OBRAS                                    -->
                    <!-- ====================================================== -->

                    <div
                        id="purchaseAllocationSection"
                        class="col-12 <?= !empty($this->data['form']['has_proration'])
                                            ? ''
                                            : 'd-none'; ?>">

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

                                        Distribua o valor total da NF-e
                                        entre as obras participantes.

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

                                    <div
                                        class="d-flex justify-content-between
                           align-items-center mb-2">

                                        <span class="text-muted">
                                            Valor da NF-e:
                                        </span>

                                        <strong id="purchaseAllocationDocumentTotal">
                                            R$ 0,00
                                        </strong>

                                    </div>


                                    <div
                                        class="d-flex justify-content-between
                           align-items-center mb-2">

                                        <span class="text-muted">
                                            Total Rateado:
                                        </span>

                                        <strong
                                            id="purchaseAllocationTotal"
                                            class="text-success">

                                            R$ 0,00

                                        </strong>

                                    </div>


                                    <div
                                        class="d-flex justify-content-between
                           align-items-center">

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
                            rows="3"
                            class="form-control"
                            placeholder="Observações sobre o lançamento..."><?= htmlspecialchars(
                                                                                $this->data['form']['observation'] ?? ''
                                                                            ); ?></textarea>

                    </div>

                </div>


                <hr class="my-4">


                <!-- Botões -->
                <div
                    class="d-flex
                           justify-content-end
                           gap-2">

                    <a
                        href="<?= $_ENV['URL_ADM']; ?>list-nfes"
                        class="btn btn-outline-secondary">

                        <i class="fa-solid fa-arrow-left me-1"></i>

                        Voltar

                    </a>

                    <button
                        type="button"
                        id="btnGenerateInstallments"
                        class="btn btn-primary"
                        data-endpoint="<?= $_ENV['URL_ADM']; ?>get-payment-method-items"
                        data-total-value="<?= htmlspecialchars(
                                                (string) ($nfe['total_value'] ?? '0')
                                            ); ?>">

                        <i class="fa-solid fa-arrow-right me-1"></i>

                        Gerar Parcelas

                    </button>

                    <button
                        type="submit"
                        form="purchaseDocumentForm"
                        id="btnSavePendingPurchaseDocument"
                        class="btn btn-warning d-none">

                        <i class="fa-solid fa-floppy-disk me-1"></i>

                        Salvar com pendência

                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- ====================================================== -->
    <!-- PRÉVIA DAS PARCELAS                                    -->
    <!-- ====================================================== -->

    <div
        id="installmentsPreview"
        class="card mb-4 border-light shadow d-none">

        <div class="card-header">

            <i class="fa-solid fa-calendar-days me-2"></i>

            <span class="fw-semibold">
                Configuração das Parcelas
            </span>

        </div>

        <div class="card-body">

            <div
                id="installmentsContainer"
                class="row g-3">
            </div>

            <hr class="my-4">

            <div class="row">

                <div class="col-md-4 ms-auto">

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">
                            Valor da NF-e:
                        </span>

                        <strong id="installmentsTotalNfe">
                            R$ 0,00
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">
                            Soma das Parcelas:
                        </span>

                        <strong
                            id="installmentsTotal"
                            class="text-success">
                            R$ 0,00
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">
                            Diferença:
                        </span>

                        <strong
                            id="installmentsDifference"
                            class="text-success">
                            R$ 0,00
                        </strong>
                    </div>

                </div>

            </div>

            <div class="d-flex justify-content-end mt-4">

                <button
                    type="submit"
                    form="purchaseDocumentForm"
                    id="btnSavePurchaseDocument"
                    class="btn btn-success"
                    disabled>

                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Salvar Lançamento

                </button>

            </div>

        </div>

    </div>

    <?php if (!empty($this->data['form']['installments'])): ?>

        <script
            type="application/json"
            id="purchaseInstallmentsOldData">
            <?= json_encode(
                $this->data['form']['installments'],
                JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_HEX_TAG
                    | JSON_HEX_AMP
                    | JSON_HEX_APOS
                    | JSON_HEX_QUOT
            ); ?>
        </script>

    <?php endif; ?>

    <?php

    $oldAllocations =
        $this->data['form']['allocations']
        ?? [];

    ?>

    <script
        type="application/json"
        id="purchaseAllocationsOldData">
        <?= json_encode(
            $oldAllocations,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
        ); ?>
    </script>

</div>