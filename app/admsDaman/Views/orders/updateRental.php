<?php

use App\admsDaman\Helpers\CSRFHelper;

?>
<div class="container-fluid px-4">
    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Pedidos</h2>
        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-orders">Pedidos</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </li>
        </ol>
    </div>
    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Editar</span>
            <span class="ms-auto d-sm-flex flex-row">
                <a href="<?= $_ENV['URL_ADM'] . 'list-orders'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>
                <a href="<?= $_ENV['URL_ADM'] . 'view-order/' . ($this->data['form']['id'] ?? ''); ?>" class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>
            </span>
        </div>
        <div class="card-body">
            <?php
            // var_dump($this->data);
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            $expected_receipt_date = ($this->data['form']['expected_receipt_date'] ? date('d/m/Y H:i:s', strtotime($this->data['form']['expected_receipt_date'])) : "");

            ?>

            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_update_order'); ?>" id="">

                <input type="hidden" class="form-control" id="id" name="id" value="<?= ($this->data['form']['id'] ?? ''); ?>">


                <div class="col-lg-6 col-md-6 col-sm-12">
                    <label for="solicitante" class="form-label">Prev. Recebimento </label>
                    <input type="text" disabled class="form-control" id="expected_user_view" name="expected_user_view" value="<?= $expected_receipt_date; ?>" placeholder="">
                    <input type="hidden" class="form-control" id="expected_receipt_date" name="expected_receipt_date" value="<?= $expected_receipt_date; ?>" placeholder="">
                </div>

                <div class="col-lg-6 col-md-6 col-sm-12">
                    <label for="solicitante" class="form-label">Solicitante:</label>
                    <input type="text" disabled class="form-control desabled" id="solicitante" name="solicitante" value="<?= $_SESSION['user_name'] ?? '';  ?>" placeholder="Nome do Usuário">
                    <input type="hidden" class="form-control" id="adms_daman_user_id" name="adms_daman_user_id" value="<?= $_SESSION['user_id'] ?? ''; ?>" placeholder="Id do usuário">
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label for="adms_daman_project_id" class="form-label">Obras</label>

                    <select name="adms_daman_project_id" class="form-select" id="adms_daman_project_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe pacotes
                        if ($this->data['getAllProjectsSelect'] ?? false) {

                            // Percorrer array de pacotes
                            foreach ($this->data['getAllProjectsSelect'] as $getAllProjectsSelect) {
                                extract($getAllProjectsSelect);

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
                    <label for="adms_daman_order_types_id" class="form-label">Tipo do pedido</label>

                    <input type="hidden" name="adms_daman_order_types_id" value="<?=  $this->data['form']['adms_daman_order_types_id'] ?? '' ?>">

                    <select class="form-select adms_daman_order_types_id" id="adms_daman_order_types_id_visibled" name="adms_daman_order_types_id_visibled" disabled>
                        <option selected>Selecione a tipo</option>
                        <option value="1" <?= isset($this->data['form']['adms_daman_order_types_id']) && $this->data['form']['adms_daman_order_types_id'] == 1 ? 'selected' : ''; ?>>COMPRA</option>
                        <option value="2" <?= isset($this->data['form']['adms_daman_order_types_id']) && $this->data['form']['adms_daman_order_types_id'] == 2 ? 'selected' : ''; ?>>LOCAÇÃO</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12" id="locationPeriod" style="display: <?= ($this->data['form']['order_name_type'] == 'LOCAÇÃO') ? 'block' : 'none' ?>;">
                    <label for="rental_period" class="form-label">Período de Locação</label>

                    <select class="form-select" id="rental_period" name="rental_period">
                        <option selected value="">Selecione o período</option>
                        <option value="1" <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 1 ? 'selected' : ''; ?>>DIÁRIA</option>
                        <option value="7" <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 7 ? 'selected' : ''; ?>>7 DIAS</option>
                        <option value="15" <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 15 ? 'selected' : ''; ?>>15 DIAS</option>
                        <option value="30" <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 30 ? 'selected' : ''; ?>>30 DIAS</option>
                    </select>
                </div>

                <div class="col-lg-6 col-md-12 col-sm-12">
                    <label for="service" class="form-label">Descrição do serviço:</label>
                    <input type="text" class="form-control desabled" id="service" name="service" value="<?= $this->data['form']['service'] ?? '';  ?>" placeholder="Descrição do serviço">
                </div>

                <div class="col-12">
                    <label for="observation" class="form-label">Observação</label>
                    <textarea class="form-control" placeholder="Observação" name="observation" id="observation" style="height: 100px"><?= $this->data['form']['observation'] ?? ''; ?></textarea>
                </div>

                <hr>

                <?php

                // Se encontrar o array de itens exibir para edição:
                if ($this->data['items'] ?? false): ?>

                    <div id="items-container" class="row g-3 ">
                        <?php
                        // Percorre o array form até encontrar o elemento 'description', existindo ele continua a executar para mostrar ao menos um campo inicial, para o usuário.
                        foreach ($this->data['items'] as $index => $item):
                        ?>
                            <div class="row g-1 item-group mb-2 mt-n1">
                                <div class="col-lg-5 col-md-12 col-sm-12">
                                    <?php if ($index === 0): ?>
                                        <label class="fw-bold">Descrição</label>
                                    <?php endif; ?>
                                    <input type="hidden" class="form-control desabled" id="item_id" name="items[<?= $index ?>][item_id]" value="<?= $item['item_id'] ?? '';  ?>">

                                    <input type="text" class="form-control desabled" id="description" name="items[<?= $index ?>][description]" value="<?= $item['description'] ?? '';  ?>" placeholder="Descrição completa: Marca, modelo e referências, evitando compras erradas.">
                                </div>

                                <div class="col-lg-1 col-md-12 col-sm-12">
                                    <?php if ($index === 0): ?>
                                        <label class="fw-bold">Qtd Pedida</label>
                                    <?php endif; ?>
                                    <input type="text" class="form-control desabled" id="quantity" name="items[<?= $index ?>][quantity]" value="<?= $item['quantity'] ?? '';  ?>" placeholder="Qtd" disabled>
                                </div>

                                <div class="col-lg-1 col-md-12 col-sm-12">
                                    <?php if ($index === 0): ?>
                                        <label class="fw-bold">Un</label>
                                    <?php endif; ?>
                                    <select name="items[<?= $index ?>][adms_daman_measurement_units_id]" class="form-select" id="adms_daman_measurement_units_id">
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
                                </div>

                                <div class="col-lg-1 col-md-12 col-sm-12">
                                    <?php if ($index === 0): ?>
                                        <label class="fw-bold">Locado</label>
                                    <?php endif; ?>
                                    <input type="text" class="form-control desabled" id="rented_quantity" name="items[<?= $index ?>][rented_quantity]" value="<?= $item['rented_quantity'] ?? '';  ?>" placeholder="Qtd Locada">
                                </div>

                                <div class="col-lg-1 col-md-12 col-sm-12">
                                    <?php if ($index === 0): ?>
                                        <label class="fw-bold">Devolvido</label>
                                    <?php endif; ?>
                                    <input type="text" class="form-control desabled" id="returned_quantity" name="items[<?= $index ?>][returned_quantity]" value="<?= $item['returned_quantity'] ?? '';  ?>" placeholder="Qtd Locada">
                                </div>

                                <div class="col-lg-1 col-md-12 col-sm-12">
                                    <?php if ($index === 0): ?>
                                        <label class="fw-bold">Preço Unit</label>
                                    <?php endif; ?>
                                    <input type="text" class="form-control desabled" id="unit_price" name="items[<?= $index ?>][unit_price]" value="<?= $item['unit_price'] ?? '';  ?>" placeholder="Preço Unitário">
                                </div>

                                <div class="col-lg-1 col-md-12 col-sm-12">
                                    <?php if ($index === 0): ?>
                                        <label class="fw-bold">Status</label>
                                    <?php endif; ?>
                                    <select name="items[<?= $index ?>][adms_daman_order_status_id]" class="form-select" id="adms_daman_order_status_id">
                                        <option value="" selected>Selecione</option>

                                        <?php
                                        // Verificar se existe Status
                                        if ($this->data['getAllStatusSelect'] ?? false) {

                                            // Percorrer array de status
                                            foreach ($this->data['getAllStatusSelect'] as $status) {

                                                // Verificar se deve manter selecionada a opção
                                                $statusSelecionado =
                                                    $this->data['form']['items'][$index]['adms_daman_order_status_id']
                                                    ?? $item['adms_daman_order_status_id']
                                                    ?? null;

                                                $selected = ($statusSelecionado == $status['id']) ? 'selected' : '';

                                                echo "<option value='" . htmlspecialchars($status['id']) . "' $selected>" . htmlspecialchars($status['name']) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-lg-1 col-md-12 col-sm-12">
                                    <?php if ($index === 0): ?>
                                        <label class="fw-bold d-block text-end">Ação</label>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-danger btn-sm">
                                            <i class="fa-regular fa-trash-can"></i> Apagar
                                        </button>
                                    </div>
                                </div>

                            </div>
                        <?php endforeach; // finalização do foreach dos itens  
                        ?>

                    <?php endif; // finalização do if do array de itens  
                    ?>
                    </div>

                    <?php // Inlcuir resultados das unidade de medida do banco de dados para o js 
                    ?>
                    <div id="units-data"
                        data-units='<?= json_encode($this->data['getAllMeasurementUnitsSelect']) ?>'>
                    </div>
                    <div id="items-data"
                        data-items='<?= json_encode($this->data['items'] ?? []) ?>'
                        data-old-items='<?= json_encode($this->data['form']['items'] ?? []) ?>'>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 text-end">
                        <button type="button" id="add-item" class="btn btn-success btn-sm mb-3">+ Adicionar Item</button>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-warning btn-sm" onclick="showLoading()">Editar</button>
                    </div>
            </form>
        </div>
    </div>
</div>