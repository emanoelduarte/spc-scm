<?php

use App\admsDaman\Helpers\CSRFHelper;

$form =
    $this->data['form'] ?? [];

$projects =
    $this->data['getAllProjectsSelectActive'] ?? [];

$buyers =
    $this->data['getPurchaseUsersSelect'] ?? [];

$suppliers =
    $this->data['getAllSuppliersSelectActive'] ?? [];

$paymentMethods =
    $this->data['getAllPaymentSelect'] ?? [];

$financialPaymentMethods =
    $this->data['getAllFinancialPaymentMethodsSelect']
    ?? [];


/*
 * Manter as datas preenchidas após erro.
 *
 * Em um novo lançamento, utiliza a data atual.
 */
$documentDate =
    $form['document_date']
    ?? date('Y-m-d');

$purchaseDate =
    $form['purchase_date']
    ?? date('Y-m-d');

?>

<div class="container-fluid px-4">

    <!-- Título -->
    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">

        <div>

            <h2 class="mt-3 mb-1">
                Novo Lançamento
            </h2>

            <p class="text-muted mb-3">
                Cadastro de compra avulsa sem NF-e importada
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

                    Compras

                </a>

            </li>

            <li
                class="breadcrumb-item active"
                aria-current="page">

                Novo Lançamento

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


        <!-- CSRF -->
        <input
            type="hidden"
            name="csrf_token"
            value="<?= CSRFHelper::generateCSRFToken(
                        'form_create_manual_purchase_document'
                    ); ?>">


        <!-- ================================================== -->
        <!-- DOCUMENTO                                         -->
        <!-- ================================================== -->

        <div class="card mb-4 border-light shadow">

            <div class="card-header">

                <i class="fa-solid fa-receipt me-2"></i>

                <strong>
                    Dados do Documento
                </strong>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <!-- Tipo -->
                    <div class="col-xl-3 col-md-6">

                        <label
                            for="document_type"
                            class="form-label">

                            Tipo do Documento

                        </label>

                        <select
                            name="document_type"
                            id="document_type"
                            class="form-select">

                            <option value="">
                                Selecione
                            </option>

                            <?php

                            $documentTypes = [
                                'CUPOM' => 'Cupom Fiscal',
                                'RECIBO' => 'Recibo',
                                'NOTA' => 'Nota Fiscal',
                                'OUTRO' => 'Outro',
                                'SEM_DOCUMENTO' => 'Sem Documento',
                            ];

                            foreach (
                                $documentTypes
                                as $value => $label
                            ):

                            ?>

                                <option
                                    value="<?= $value; ?>"
                                    <?= (
                                        ($form['document_type'] ?? '')
                                        === $value
                                    )
                                        ? 'selected'
                                        : ''; ?>>

                                    <?= $label; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Número -->
                    <div class="col-xl-3 col-md-6">

                        <label
                            for="document_number"
                            class="form-label">

                            Número do Documento

                        </label>

                        <input
                            type="text"
                            name="document_number"
                            id="document_number"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                        $form['document_number']
                                            ?? ''
                                    ); ?>"
                            placeholder="Ex: 001234">

                    </div>


                    <!-- Data documento -->
                    <div class="col-xl-2 col-md-6">

                        <label
                            for="document_date"
                            class="form-label">

                            Data do Documento

                        </label>

                        <input
                            type="date"
                            name="document_date"
                            id="document_date"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                        $documentDate
                                    ); ?>">

                    </div>


                    <!-- Valor -->
                    <div class="col-xl-2 col-md-6">

                        <label
                            for="total_value"
                            class="form-label">

                            Valor Total
                            <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                R$
                            </span>

                            <input
                                type="text"
                                name="total_value"
                                id="total_value"
                                class="form-control"
                                value="<?= htmlspecialchars(
                                            $form['total_value']
                                                ?? ''
                                        ); ?>"
                                placeholder="0,00"
                                inputmode="decimal">

                        </div>

                    </div>


                    <!-- Fornecedor -->
                    <div class="col-xl-4 col-md-6">

                        <label
                            for="adms_daman_supplier_id"
                            class="form-label">

                            Fornecedor
                            <span class="text-danger">*</span>

                        </label>

                        <select
                            name="adms_daman_supplier_id"
                            id="adms_daman_supplier_id"
                            class="form-select">

                            <option value="">
                                Selecione
                            </option>

                            <?php foreach (
                                $suppliers
                                as $supplier
                            ): ?>

                                <option
                                    value="<?= (int) $supplier['id']; ?>"
                                    <?= (
                                        (int) (
                                            $form['adms_daman_supplier_id']
                                            ?? 0
                                        )
                                        === (int) $supplier['id']
                                    )
                                        ? 'selected'
                                        : ''; ?>>

                                    <?= htmlspecialchars(
                                        $supplier['legal_name']
                                    ); ?>

                                    <?php if (
                                        !empty($supplier['cnpj'])
                                    ): ?>

                                        -
                                        <?= htmlspecialchars(
                                            $supplier['cnpj']
                                        ); ?>

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

            </div>

        </div>


        <!-- ================================================== -->
        <!-- DADOS DA COMPRA                                   -->
        <!-- ================================================== -->

        <div class="card mb-4 border-light shadow">

            <div class="card-header">

                <i class="fa-solid fa-cart-shopping me-2"></i>

                <strong>
                    Dados da Compra
                </strong>

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
                            class="form-select">

                            <option value="">
                                Selecione
                            </option>

                            <?php foreach (
                                $projects
                                as $project
                            ): ?>

                                <option
                                    value="<?= (int) $project['id']; ?>"
                                    <?= (
                                        (int) (
                                            $form['adms_daman_project_id']
                                            ?? 0
                                        )
                                        === (int) $project['id']
                                    )
                                        ? 'selected'
                                        : ''; ?>>

                                    <?= htmlspecialchars(
                                        $project['name']
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


                    <!-- Comprador -->
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
                            class="form-select">

                            <option value="">
                                Selecione
                            </option>

                            <?php foreach (
                                $buyers
                                as $buyer
                            ): ?>

                                <option
                                    value="<?= (int) $buyer['id']; ?>"
                                    <?= (
                                        (int) (
                                            $form['adms_daman_user_id']
                                            ?? 0
                                        )
                                        === (int) $buyer['id']
                                    )
                                        ? 'selected'
                                        : ''; ?>>

                                    <?= htmlspecialchars(
                                        $buyer['name']
                                    ); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Data compra -->
                    <div class="col-xl-4 col-md-6">

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

                            <option value="">
                                Selecione
                            </option>

                            <?php foreach (
                                $paymentMethods
                                as $paymentMethod
                            ): ?>

                                <option
                                    value="<?= (int) $paymentMethod['id']; ?>"
                                    <?= (
                                        (int) (
                                            $form['adms_daman_payment_method_id']
                                            ?? 0
                                        )
                                        === (int) $paymentMethod['id']
                                    )
                                        ? 'selected'
                                        : ''; ?>>

                                    <?= htmlspecialchars(
                                        $paymentMethod['name']
                                    ); ?>

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
                                class="d-flex flex-column flex-md-row
                   align-items-md-center gap-2 mb-3">

                                <div>

                                    <div class="fw-semibold">
                                        <i class="fa-solid fa-code-branch me-1"></i>
                                        Rateio entre obras
                                    </div>

                                    <div class="small text-muted">
                                        Distribua o valor total do lançamento
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


                            <!-- Linhas adicionadas pelo JavaScript -->
                            <div
                                id="purchaseAllocationRows"
                                class="d-flex flex-column gap-2">
                            </div>


                            <hr class="my-3">


                            <!-- Resumo -->
                            <div class="row">

                                <div class="col-md-6 col-lg-5 ms-auto">

                                    <div
                                        class="d-flex justify-content-between
                           align-items-center mb-2">

                                        <span class="text-muted">
                                            Valor do Documento:
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
                            class="form-control"
                            rows="3"
                            placeholder="Observações sobre o lançamento..."><?= htmlspecialchars(
                                                                                $form['observation']
                                                                                    ?? ''
                                                                            ); ?></textarea>

                    </div>

                </div>


                <hr class="my-4">


                <!-- Ações -->
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

                    <!--
                        Salvar diretamente quando o parcelamento
                        ainda estiver pendente de confirmação.
                    -->
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

            <!-- ====================================================== -->
            <!-- CONFIGURAÇÃO DAS PARCELAS                              -->
            <!-- ====================================================== -->

            <div
                id="manualInstallmentsPreview"
                class="card mb-4 border-light shadow d-none">

                <div class="card-header">

                    <i class="fa-solid fa-calendar-days me-2"></i>

                    <strong>
                        Configuração das Parcelas
                    </strong>

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
                                    Valor da Compra:
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
                            Salvar Lançamento

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

    <?php

    /*
    * Parcelas retornadas pelo POST.
    *
    * Quando o formulário falhar na validação,
    * estes dados serão utilizados pelo JavaScript
    * para reconstruir visualmente as parcelas.
    */
    $oldInstallments =
        $this->data['form']['installments']
        ?? [];



    /*
    * Rateios retornados pelo POST.
    *
    * Caso alguma validação falhe,
    * o JavaScript reconstruirá as linhas
    * preenchidas anteriormente pelo usuário.
    */
    $oldAllocations =
        $this->data['form']['allocations']
        ?? [];

    ?>

    <script
        type="application/json"
        id="manualInstallmentsOldData">
        <?= json_encode(
            $oldInstallments,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
        ); ?>
    </script>

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

    <script
        type="application/json"
        id="financialPaymentMethodsData">
        <?= json_encode(
            $financialPaymentMethods,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
        ); ?>
    </script>

</div>