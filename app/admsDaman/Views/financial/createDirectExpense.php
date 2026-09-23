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

?>

<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">

        <div>
            <h2 class="mt-3 mb-1">
                Nova Despesa Direta
            </h2>

            <p class="text-muted mb-3">
                Registrar um desembolso realizado diretamente pela obra.
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

            <li
                class="breadcrumb-item active"
                aria-current="page">

                Nova Despesa Direta

            </li>

        </ol>

    </div>


    <?php

    include './app/admsDaman/Views/partials/alerts.php';

    ?>


    <?php if (!empty($errors)): ?>

        <div
            class="alert alert-danger"
            role="alert">

            <div class="fw-semibold mb-1">

                <i class="fa-solid fa-circle-exclamation me-1"></i>

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


    <div class="card mb-4 border-light shadow">

        <div class="card-header">

            <i class="fa-solid fa-money-bill-transfer me-1"></i>

            Lançamento do desembolso

        </div>


        <div class="card-body">

            <form
                action=""
                method="POST"
                class="row g-3">


                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        CSRFHelper::generateCSRFToken(
                            'form_create_direct_expense'
                        )
                    ); ?>">


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
                            ?? date('Y-m-d')
                        ); ?>"
                        required>

                </div>


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

                        <?php foreach ($projects as $project): ?>

                            <?php

                            $selected =
                                (
                                    (string) (
                                        $form['adms_daman_project_id']
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

                </div>


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

                        <?php foreach ($categories as $category): ?>

                            <?php

                            $selected =
                                (
                                    (string) (
                                        $form['adms_daman_expense_category_id']
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
                        placeholder="Ex: Mão de obra - 3 funcionários - período 01/09 a 15/09"
                        required>

                </div>


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

                        <?php foreach ($paymentMethods as $paymentMethod): ?>

                            <?php

                            $selected =
                                (
                                    (string) (
                                        $form['adms_daman_financial_payment_method_id']
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


                <div class="col-12 d-flex justify-content-end">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fa-solid fa-floppy-disk me-1"></i>

                        Salvar Despesa

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
