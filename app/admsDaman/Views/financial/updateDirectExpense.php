<?php

use App\admsDaman\Helpers\CSRFHelper;


$form =
    $this->data['form']
    ?? [];


$projects =
    $this->data['projects']
    ?? [];


$categories =
    $this->data['expense_categories']
    ?? [];


$paymentMethods =
    $this->data['financial_payment_methods']
    ?? [];


$errors =
    $this->data['errors']
    ?? [];


$directExpenseId =
    (int) (
        $this->data['direct_expense_id']
        ?? 0
    );

?>


<div class="container-fluid px-4">


    <!-- ====================================================== -->
    <!-- TÍTULO                                                 -->
    <!-- ====================================================== -->

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">

        <div>

            <h2 class="mt-3 mb-1">

                Editar Despesa Direta

            </h2>


            <p class="text-muted mb-3">

                Alterar os dados de um desembolso
                realizado diretamente pela obra.

            </p>

        </div>


        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">

                <a
                    href="<?= $_ENV['URL_ADM']; ?>dashboard"
                    class="text-decoration-none">

                    Dashboard

                </a>

            </li>


            <li class="breadcrumb-item">

                <a
                    href="<?= $_ENV['URL_ADM']; ?>list-direct-expenses"
                    class="text-decoration-none">

                    Despesas Diretas

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

    include
        './app/admsDaman/Views/partials/alerts.php';

    ?>


    <!-- ====================================================== -->
    <!-- ERROS                                                  -->
    <!-- ====================================================== -->

    <?php if (!empty($errors)): ?>

        <div
            class="alert alert-danger"
            role="alert">

            <div class="fw-semibold mb-1">

                <i
                    class="fa-solid
                           fa-circle-exclamation
                           me-1">
                </i>

                Verifique os dados informados:

            </div>


            <ul class="mb-0">

                <?php foreach ($errors as $error): ?>

                    <li>

                        <?= htmlspecialchars(
                            (string) $error
                        ); ?>

                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <!-- ====================================================== -->
    <!-- FORMULÁRIO                                             -->
    <!-- ====================================================== -->

    <div class="card mb-4 border-light shadow">

        <div
            class="card-header
                   d-flex
                   justify-content-between
                   align-items-center
                   gap-2">

            <div>

                <i
                    class="fa-solid
                           fa-pen-to-square
                           me-1">
                </i>

                Alteração do desembolso

            </div>


            <span class="badge text-bg-secondary">

                ID:
                <?= $directExpenseId; ?>

            </span>

        </div>


        <div class="card-body">


            <form
                id="directExpenseForm"
                action="<?= $_ENV['URL_ADM']; ?>edit-direct-expense/<?= $directExpenseId; ?>"
                method="POST"
                class="row g-3">


                <!-- ================================================== -->
                <!-- CSRF                                               -->
                <!-- ================================================== -->

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        CSRFHelper::generateCSRFToken(
                            'form_edit_direct_expense_'
                            . $directExpenseId
                        )
                    ); ?>">


                <!-- ================================================== -->
                <!-- DATA                                               -->
                <!-- ================================================== -->

                <div class="col-lg-3 col-md-6 col-sm-12">

                    <label
                        for="expense_date"
                        class="form-label">

                        Data

                    </label>


                    <input
                        type="date"
                        class="form-control"
                        id="expense_date"
                        name="expense_date"
                        max="<?= date('Y-m-d'); ?>"
                        value="<?= htmlspecialchars(
                            $form['expense_date']
                            ?? ''
                        ); ?>"
                        required>

                </div>


                <!-- ================================================== -->
                <!-- OBRA PRINCIPAL                                     -->
                <!-- ================================================== -->

                <div class="col-lg-5 col-md-6 col-sm-12">

                    <label
                        for="adms_daman_project_id"
                        class="form-label">

                        Obra

                    </label>


                    <select
                        class="form-select"
                        id="adms_daman_project_id"
                        name="adms_daman_project_id"
                        required>

                        <option value="">

                            Selecione

                        </option>


                        <?php foreach (
                            $projects
                            as $project
                        ): ?>


                            <?php

                            $selected =
                                (
                                    (string) (
                                        $form[
                                            'adms_daman_project_id'
                                        ]
                                        ?? ''
                                    )
                                    ===
                                    (string) $project['id']
                                )
                                    ? 'selected'
                                    : '';

                            ?>


                            <option
                                value="<?= (int) $project['id']; ?>"
                                <?= $selected; ?>>

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
                            id="has_proration"
                            name="has_proration"
                            value="1"
                            <?= !empty(
                                $form['has_proration']
                            )
                                ? 'checked'
                                : ''; ?>>


                        <label
                            class="form-check-label"
                            for="has_proration">

                            Existe rateio entre obras

                        </label>

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- CATEGORIA                                          -->
                <!-- ================================================== -->

                <div class="col-lg-4 col-md-6 col-sm-12">

                    <label
                        for="adms_daman_expense_category_id"
                        class="form-label">

                        Categoria

                    </label>


                    <select
                        class="form-select"
                        id="adms_daman_expense_category_id"
                        name="adms_daman_expense_category_id"
                        required>

                        <option value="">

                            Selecione

                        </option>


                        <?php foreach (
                            $categories
                            as $category
                        ): ?>


                            <?php

                            $selected =
                                (
                                    (string) (
                                        $form[
                                            'adms_daman_expense_category_id'
                                        ]
                                        ?? ''
                                    )
                                    ===
                                    (string) $category['id']
                                )
                                    ? 'selected'
                                    : '';

                            ?>


                            <option
                                value="<?= (int) $category['id']; ?>"
                                <?= $selected; ?>>

                                <?= htmlspecialchars(
                                    $category['name']
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- ================================================== -->
                <!-- DESCRIÇÃO                                          -->
                <!-- ================================================== -->

                <div class="col-lg-6 col-md-12 col-sm-12">

                    <label
                        for="description"
                        class="form-label">

                        Descrição

                    </label>


                    <input
                        type="text"
                        class="form-control"
                        id="description"
                        name="description"
                        maxlength="255"
                        value="<?= htmlspecialchars(
                            $form['description']
                            ?? ''
                        ); ?>"
                        placeholder="Descrição da despesa"
                        required>

                </div>


                <!-- ================================================== -->
                <!-- FORMA DE PAGAMENTO                                 -->
                <!-- ================================================== -->

                <div class="col-lg-3 col-md-6 col-sm-12">

                    <label
                        for="adms_daman_financial_payment_method_id"
                        class="form-label">

                        Forma de Pagamento

                    </label>


                    <select
                        class="form-select"
                        id="adms_daman_financial_payment_method_id"
                        name="adms_daman_financial_payment_method_id"
                        required>

                        <option value="">

                            Selecione

                        </option>


                        <?php foreach (
                            $paymentMethods
                            as $paymentMethod
                        ): ?>


                            <?php

                            $selected =
                                (
                                    (string) (
                                        $form[
                                            'adms_daman_financial_payment_method_id'
                                        ]
                                        ?? ''
                                    )
                                    ===
                                    (string) $paymentMethod['id']
                                )
                                    ? 'selected'
                                    : '';

                            ?>


                            <option
                                value="<?= (int) $paymentMethod['id']; ?>"
                                <?= $selected; ?>>

                                <?= htmlspecialchars(
                                    $paymentMethod['name']
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- ================================================== -->
                <!-- VALOR                                              -->
                <!-- ================================================== -->

                <div class="col-lg-3 col-md-6 col-sm-12">

                    <label
                        for="amount"
                        class="form-label">

                        Valor

                    </label>


                    <div class="input-group">

                        <span class="input-group-text">

                            R$

                        </span>


                        <input
                            type="text"
                            inputmode="decimal"
                            class="form-control text-end"
                            id="amount"
                            name="amount"
                            value="<?= htmlspecialchars(
                                $form['amount']
                                ?? ''
                            ); ?>"
                            placeholder="0,00"
                            required>

                    </div>

                </div>


                <!-- ====================================================== -->
                <!-- RATEIO ENTRE OBRAS                                    -->
                <!-- ====================================================== -->

                <div
                    id="directExpenseAllocationSection"
                    class="col-12 <?= !empty(
                        $form['has_proration']
                    )
                        ? ''
                        : 'd-none'; ?>">


                    <div class="border rounded p-3">


                        <div
                            class="d-flex
                                   flex-column
                                   flex-md-row
                                   align-items-md-center
                                   gap-2
                                   mb-3">


                            <div>

                                <div class="fw-semibold">

                                    <i
                                        class="fa-solid
                                               fa-code-branch
                                               me-1">
                                    </i>

                                    Rateio entre obras

                                </div>


                                <div class="small text-muted">

                                    Atualize a distribuição do valor
                                    entre as obras participantes.

                                </div>

                            </div>


                            <button
                                type="button"
                                id="btnAddDirectExpenseAllocation"
                                class="btn
                                       btn-outline-primary
                                       btn-sm
                                       ms-md-auto">

                                <i
                                    class="fa-solid
                                           fa-plus
                                           me-1">
                                </i>

                                Adicionar obra

                            </button>

                        </div>


                        <div
                            id="directExpenseAllocationRows"
                            class="d-flex flex-column gap-2">
                        </div>


                        <hr class="my-3">


                        <div class="row">

                            <div
                                class="col-md-6
                                       col-lg-5
                                       ms-auto">


                                <div
                                    class="d-flex
                                           justify-content-between
                                           align-items-center
                                           mb-2">

                                    <span class="text-muted">

                                        Valor da Despesa:

                                    </span>

                                    <strong
                                        id="directExpenseAllocationDocumentTotal">

                                        R$ 0,00

                                    </strong>

                                </div>


                                <div
                                    class="d-flex
                                           justify-content-between
                                           align-items-center
                                           mb-2">

                                    <span class="text-muted">

                                        Total Rateado:

                                    </span>

                                    <strong
                                        id="directExpenseAllocationTotal"
                                        class="text-success">

                                        R$ 0,00

                                    </strong>

                                </div>


                                <div
                                    class="d-flex
                                           justify-content-between
                                           align-items-center">

                                    <span class="text-muted">

                                        Diferença:

                                    </span>

                                    <strong
                                        id="directExpenseAllocationDifference"
                                        class="text-danger">

                                        R$ 0,00

                                    </strong>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- OBSERVAÇÃO                                         -->
                <!-- ================================================== -->

                <div class="col-12">

                    <label
                        for="observation"
                        class="form-label">

                        Observação

                        <span class="text-muted">

                            (opcional)

                        </span>

                    </label>


                    <textarea
                        class="form-control"
                        id="observation"
                        name="observation"
                        rows="3"
                        placeholder="Informações adicionais sobre este desembolso"><?= htmlspecialchars(
                            $form['observation']
                            ?? ''
                        ); ?></textarea>

                </div>


                <!-- ================================================== -->
                <!-- AÇÕES                                              -->
                <!-- ================================================== -->

                <div
                    class="col-12
                           d-flex
                           justify-content-between
                           align-items-center">


                    <a
                        href="<?= $_ENV['URL_ADM']; ?>list-direct-expenses"
                        class="btn btn-secondary">

                        <i
                            class="fa-solid
                                   fa-arrow-left
                                   me-1">
                        </i>

                        Voltar

                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i
                            class="fa-solid
                                   fa-floppy-disk
                                   me-1">
                        </i>

                        Salvar Alterações

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


<?php

/*
 * ============================================================
 * DADOS UTILIZADOS PELO JS DO RATEIO
 * ============================================================
 */

$oldDirectExpenseAllocations =
    $form['allocations']
    ?? [];


$directExpenseProjectsJson =
    json_encode(
        $projects,
        JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
    );


$directExpenseAllocationsJson =
    json_encode(
        $oldDirectExpenseAllocations,
        JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
    );

?>


<input
    type="hidden"
    id="directExpenseProjectsData"
    value="<?= htmlspecialchars(
        $directExpenseProjectsJson
            ?: '[]',
        ENT_QUOTES,
        'UTF-8'
    ); ?>">


<input
    type="hidden"
    id="directExpenseAllocationsOldData"
    value="<?= htmlspecialchars(
        $directExpenseAllocationsJson
            ?: '[]',
        ENT_QUOTES,
        'UTF-8'
    ); ?>">


<script
    src="<?= $_ENV['URL_ADM']; ?>js/direct_expense_allocations.js">
</script>