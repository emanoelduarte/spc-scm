<?php

use App\admsDaman\Helpers\CSRFHelper;

?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Pedidos</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a href="<?php echo $_ENV['URL_ADM']; ?>dashboard" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo $_ENV['URL_ADM']; ?>list-orders" class="text-decoration-none">Pedidos</a>
            </li>
            <li class="breadcrumb-item">Cadastrar</li>

        </ol>

    </div>

    <div class="card mb-4 border-light shadow">

        <div class="card-header hstack gap-2">
            <span>Cadastrar</span>

            <span class="ms-auto d-sm-flex flex-row">
                <a href="<?php echo $_ENV['URL_ADM']; ?>list-orders" class="btn btn-info btn-sm me-1 mb-1"><i
                        class="fa-solid fa-list"></i> Listar</a>
            </span>

        </div>
        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            ?>

            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token"
                    value="<?php echo CSRFHelper::generateCSRFToken('form_create_order'); ?>" id="">

                <div class="col-lg-2 col-md-6 col-sm-12">
                    <label for="expected_receipt_date" class="form-label">Prev. Recebimento </label>
                    <input type="date" class="form-control" id="expected_receipt_date" name="expected_receipt_date"
                        value="<?= $this->data['form']['expected_receipt_date'] ?? ''; ?>" placeholder="dd/mm/yyyy">

                    <script>
                    flatpickr("#expected_receipt_date", {
                        dateFormat: "d/m/Y",
                        locale: "pt", // Para português
                        minDate: new Date().fp_incr(3) // hoje + 3 dias
                    });
                    </script>
                </div>

                <div class="col-lg-10 col-md-6 col-sm-12">
                    <label for="solicitante" class="form-label">Solicitante:</label>
                    <input type="text" disabled class="form-control desabled" id="solicitante" name="solicitante"
                        value="<?= $_SESSION['user_name'] ?? '';  ?>" placeholder="Nome do Usuário">
                    <input type="hidden" class="form-control" id="adms_daman_user_id" name="adms_daman_user_id"
                        value="<?= $_SESSION['user_id'] ?? ''; ?>" placeholder="Id do usuário">
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label for="adms_daman_project_id" class="form-label">Obras</label>

                    <select name="adms_daman_project_id" class="form-select" id="adms_daman_project_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe pacotes
                        if ($this->data['getAllProjectsSelectActive'] ?? false) {

                            // Percorrer array de pacotes
                            foreach ($this->data['getAllProjectsSelectActive'] as $getAllProjectsSelectActive) {
                                extract($getAllProjectsSelectActive);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_project_id']) && $this->data['form']['adms_daman_project_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$name</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label for="adms_daman_category_id" class="form-label">Categoria do pedido</label>

                    <select name="adms_daman_category_id" class="form-select" id="adms_daman_category_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe pacotes
                        if ($this->data['getAllCategoriesSelect'] ?? false) {

                            // Percorrer array de pacotes
                            foreach ($this->data['getAllCategoriesSelect'] as $getAllCategoriesSelect) {
                                extract($getAllCategoriesSelect);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_category_id']) && $this->data['form']['adms_daman_category_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$name</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label for="adms_daman_acquisition_types_id" class="form-label">Tipo do pedido</label>

                    <select class="form-select adms_daman_acquisition_types_id" id="adms_daman_acquisition_types_id"
                        name="adms_daman_acquisition_types_id">
                        <option value="" selected>Selecione o tipo</option>
                        <option value="1"
                            <?= isset($this->data['form']['adms_daman_acquisition_types_id']) && $this->data['form']['adms_daman_acquisition_types_id'] == 1 ? 'selected' : ''; ?>>
                            COMPRA</option>
                        <option value="2"
                            <?= isset($this->data['form']['adms_daman_acquisition_types_id']) && $this->data['form']['adms_daman_acquisition_types_id'] == 2 ? 'selected' : ''; ?>>
                            LOCAÇÃO</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12" id="locationPeriod"
                    style="display: <?= (isset($this->data['form']['order_name_type']) == 'LOCAÇÃO') ? 'block' : 'none' ?>;">
                    <label for="rental_period" class="form-label">Período de Locação</label>

                    <select class="form-select" id="rental_period" name="rental_period">
                        <option selected value="">Selecione o período</option>
                        <option value="1"
                            <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 1 ? 'selected' : ''; ?>>
                            DIÁRIA</option>
                        <option value="7"
                            <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 1 ? 'selected' : ''; ?>>
                            7 DIAS</option>
                        <option value="15"
                            <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 1 ? 'selected' : ''; ?>>
                            15 DIAS</option>
                        <option value="30"
                            <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 1 ? 'selected' : ''; ?>>
                            30 DIAS</option>
                    </select>
                </div>

                <div class="col-lg-6 col-md-12 col-sm-12">
                    <label for="service" class="form-label">Descrição do serviço:</label>
                    <input type="text" class="form-control desabled" id="service" name="service"
                        value="<?= $this->data['form']['service'] ?? '';  ?>" placeholder="Descrição do serviço">
                </div>

                <div class="col-12">
                    <label for="observation" class="form-label">Observação</label>
                    <textarea class="form-control" placeholder="Observação" name="observation" id="observation"
                        style="height: 100px"><?= $this->data['form']['observation'] ?? ''; ?></textarea>
                </div>

                <hr>

                <div id="items-container" class="row g-3">

                    <?php
                    $items = $this->data['form']['items'] ?? [];

                    if (!empty($items)):
                        foreach ($items as $index => $item):
                    ?>

                    <div class="row g-1 item-group mb-2 mt-n1">

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <input type="text" class="form-control" name="items[<?= $index ?>][description]"
                                value="<?= $item['description'] ?? ''; ?>" placeholder="Descrição completa...">
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12">
                            <input type="text" class="form-control" name="items[<?= $index ?>][quantity]"
                                value="<?= $item['quantity'] ?? ''; ?>" placeholder="Qtd">
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12">
                            <div class="d-flex">

                                <select name="items[<?= $index ?>][adms_daman_measurement_units_id]" class="form-select"
                                    id="adms_daman_measurement_units_id">
                                    <option value="" selected>Selecione</option>

                                    <?php
                                            // Verificar se existe Status
                                            if ($this->data['getAllMeasurementUnitsSelect'] ?? false) {

                                                // Percorrer array de status
                                                foreach ($this->data['getAllMeasurementUnitsSelect'] as $status) {

                                                    // Verificar se deve manter selecionada a opção
                                                    $statusSelecionado =
                                                        $this->data['form']['items'][$index]['adms_daman_measurement_units_id']
                                                        ?? $item['adms_daman_measurement_units_id']
                                                        ?? null;

                                                    $selected = ($statusSelecionado == $status['id']) ? 'selected' : '';

                                                    echo "<option value='" . htmlspecialchars($status['id']) . "' $selected>" . htmlspecialchars($status['name']) . "</option>";
                                                }
                                            }
                                            ?>
                                </select>
                                <?php  // Verifica pelo indíce se existe mais de uma linha de item para aplicar o botão de remover
                                        if($index == 0) :?>
                                <button type="button" class="btn btn-danger btn-remove d-none">-</button>
                                <?php else :?>
                                <button type="button" class="btn btn-danger btn-remove">-</button>
                                <?php endif;?>
                            </div>
                        </div>

                    </div>

                    <?php
                        endforeach;
                    else:
                        ?>

                    <!-- MOSTRA UM CAMPO VAZIO INICIAL -->
                    <div class="row g-1 item-group mb-2 mt-n1">

                        <div class="col-lg-6">
                            <input type="text" name="items[0][description]" class="form-control"
                                placeholder="Descrição completa: Marca, modelo e referências, evitando compras erradas.">
                        </div>

                        <div class="col-lg-3">
                            <input type="text" name="items[0][quantity]" class="form-control" placeholder="Qtd">
                        </div>

                        <div class="col-lg-3">
                            <div class="d-flex">

                                <select name="items[0][adms_daman_measurement_units_id]" class="form-select"
                                    id="adms_daman_measurement_units_id_mudado">
                                    <option value="" selected>Selecione</option>

                                    <?php
                                        // Verificar se existe Status
                                        if ($this->data['getAllMeasurementUnitsSelect'] ?? false) {

                                            // Percorrer array de status
                                            foreach ($this->data['getAllMeasurementUnitsSelect'] as $status) {

                                                // Verificar se deve manter selecionada a opção
                                                $statusSelecionado =
                                                    $this->data['form']['items'][0]['adms_daman_measurement_units_id']
                                                    ?? $item['adms_daman_measurement_units_id']
                                                    ?? null;

                                                $selected = ($statusSelecionado == $status['id']) ? 'selected' : '';

                                                echo "<option value='" . htmlspecialchars($status['id']) . "' $selected>" . htmlspecialchars($status['name']) . "</option>";
                                            }
                                        }
                                        ?>
                                </select>
                                <!-- <button type="button" class="btn btn-danger btn-remove">-</button> -->
                            </div>
                        </div>

                    </div>

                    <?php endif; ?>

                </div>

                <?php // Inlcuir resultados das unidade de medida do banco de dados para o js 
                ?>
                <div id="units-data" data-units='<?= json_encode($this->data['getAllMeasurementUnitsSelect']) ?>'
                    data-old-items='<?= json_encode($this->data['form']['items'] ?? []) ?>'>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 text-end">
                    <button type="button" id="add-item" class="btn btn-success btn-sm mb-3">+ Adicionar Item</button>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm" onclick="showLoading()">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>