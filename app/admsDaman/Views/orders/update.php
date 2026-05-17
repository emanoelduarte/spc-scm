<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_update_order');

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
                <?php if (in_array("ListOrders", $this->data['buttonPermissions'])): ?>
                <a href="<?= $_ENV['URL_ADM'] . 'list-orders'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i
                        class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>
                <?php if (in_array("ViewOrder", $this->data['buttonPermissions'])): ?>
                <a href="<?= $_ENV['URL_ADM'] . 'view-order/' . ($this->data['form']['id'] ?? ''); ?>"
                    class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>
                <?php endif; ?>
            </span>
        </div>
        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            $expected_receipt_date = ($this->data['form']['expected_receipt_date'] ? date('d/m/Y H:i:s', strtotime($this->data['form']['expected_receipt_date'])) : "");

            ?>

            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token"
                    value="<?php echo CSRFHelper::generateCSRFToken('form_update_order'); ?>" id="">

                <input type="hidden" class="form-control" id="id" name="id"
                    value="<?= ($this->data['form']['id'] ?? ''); ?>">


                <div class="col-lg-2 col-md-6 col-sm-12">
                    <label for="expected_user_view" class="form-label">Prev. Recebimento </label>
                    <input type="text" disabled class="form-control" id="expected_user_view" name="expected_user_view"
                        value="<?= $expected_receipt_date; ?>" placeholder="">
                    <input type="hidden" class="form-control" id="expected_receipt_date" name="expected_receipt_date"
                        value="<?= $expected_receipt_date; ?>" placeholder="">
                </div>

                <div class="col-lg-8 col-md-6 col-sm-12">
                    <label for="solicitante" class="form-label">Solicitante:</label>
                    <input type="text" disabled class="form-control desabled" id="solicitante" name="solicitante"
                        value="<?= $_SESSION['user_name'] ?? '';  ?>" placeholder="Nome do Usuário">
                    <input type="hidden" class="form-control" id="adms_daman_user_id" name="adms_daman_user_id"
                        value="<?= $_SESSION['user_id'] ?? ''; ?>" placeholder="Id do usuário">
                </div>

                <div class="col-lg-2 col-md-6 col-sm-12">
                    <label for="adms_daman_acquisition_status_id" class="form-label">Status</label>
                    <select name="adms_daman_acquisition_status_id" class="form-select"
                        id="adms_daman_acquisition_status_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe pacotes
                        if ($this->data['getAllStatusSelect'] ?? false) {

                            // Percorrer array de pacotes
                            foreach ($this->data['getAllStatusSelect'] as $getAllStatusSelect) {
                                extract($getAllStatusSelect);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_acquisition_status_id']) && $this->data['form']['adms_daman_acquisition_status_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$name</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <?php if (in_array("PurchaseContent", $this->data['buttonPermissions'])): // Verifica se é solicitante de compra ou tem permissão de acessar
                ?>

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
                    <label for="adms_daman_acquisition_types_id" class="form-label">Tipo do pedido</label>

                    <input type="hidden" name="adms_daman_acquisition_types_id"
                        value="<?= $this->data['form']['adms_daman_acquisition_types_id'] ?? '' ?>">

                    <select class="form-select adms_daman_acquisition_types_id" id="adms_daman_acquisition_types_id"
                        name="adms_daman_acquisition_types_id" disabled>
                        <option selected>Selecione o tipo</option>
                        <option value="1"
                            <?= isset($this->data['form']['adms_daman_acquisition_types_id']) && $this->data['form']['adms_daman_acquisition_types_id'] == 1 ? 'selected' : ''; ?>>
                            COMPRA</option>
                        <option value="2"
                            <?= isset($this->data['form']['adms_daman_acquisition_types_id']) && $this->data['form']['adms_daman_acquisition_types_id'] == 2 ? 'selected' : ''; ?>>
                            LOCAÇÃO</option>
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

                <?php else : ?>

                <?php // Envia os valores pelo POST sem permitir edição 
                    ?>
                <input type="hidden" name="adms_daman_project_id"
                    value="<?= $this->data['form']['adms_daman_project_id'] ?? '' ?>">
                <input type="hidden" name="adms_daman_category_id"
                    value="<?= $this->data['form']['adms_daman_category_id'] ?? '' ?>">
                <input type="hidden" name="adms_daman_acquisition_types_id"
                    value="<?= $this->data['form']['adms_daman_acquisition_types_id'] ?? '' ?>">
                <input type="hidden" name="service" value="<?= $this->data['form']['service'] ?? '' ?>">
                <input type="hidden" name="observation" value="<?= $this->data['form']['observation'] ?? '' ?>">

                <?php // Exibição visual para o comprador 
                    ?>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label class="form-label">Obras</label>
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($this->data['form']['project_name'] ?? '') ?>" disabled>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label class="form-label">Categoria do pedido</label>
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($this->data['form']['category_name'] ?? '') ?>" disabled>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label class="form-label">Tipo do pedido</label>
                    <input type="text" class="form-control"
                        value="<?= $this->data['form']['adms_daman_acquisition_types_id'] == 1 ? 'COMPRA' : 'LOCAÇÃO' ?>"
                        disabled>
                </div>

                <div class="col-lg-6 col-md-12 col-sm-12">
                    <label class="form-label">Descrição do serviço</label>
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($this->data['form']['service'] ?? '') ?>" disabled>
                </div>

                <div class="col-12">
                    <label class="form-label">Observação</label>
                    <textarea class="form-control" disabled
                        style="height: 100px"><?= htmlspecialchars($this->data['form']['observation'] ?? '') ?></textarea>
                </div>

                <?php endif; ?>

                <hr>

                <?php
                // Se encontrar o array de itens exibir para edição:
                $items = $this->data['form']['items'] ?? $this->data['items'] ?? [];

                if ($items): ?>

                <div id="items-container" class="row g-3 ">
                    <?php
                        // Percorre o array form até encontrar o elemento 'description', existindo ele continua a executar para mostrar ao menos um campo inicial, para o usuário.
                        foreach ($items as $index => $item):

                            $formItems = $this->data['form']['items'] ?? [];
                            $dbItems   = $this->data['items'] ?? [];

                            $item = $formItems[$index] ?? $dbItems[$index] ?? [];

                            $isNew = $item['is_new'] ?? (!empty($item['item_id']) ? '0' : '1');
                        ?>

                    <?php if ($isNew == '0'): ?>
                    <!-- ITEM DO BANCO (layout completo) -->
                    <div class="row g-1 item-group mb-2 mt-n1">

                        <input type="hidden" name="items[<?= $index ?>][is_new]" value="0">
                        <input type="hidden" name="items[<?= $index ?>][item_id]" value="<?= $item['item_id'] ?? '' ?>">

                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <?php if ($index === 0): ?>
                            <label class="fw-bold">Descrição</label>
                            <?php endif; ?>
                            <input type="text" class="form-control" name="items[<?= $index ?>][description]"
                                value="<?= $item['description'] ?? '' ?>">
                        </div>

                        <?php if (in_array("PurchaseContent", $this->data['buttonPermissions'])): // Verifica se é solicitante de compra ou tem permissão de acessar
                                    ?>

                        <div class="col-lg-1">
                            <?php if ($index === 0): ?><label class="fw-bold">Qtd</label><?php endif; ?>
                            <input type="text" class="form-control" name="items[<?= $index ?>][quantity]"
                                value="<?= $item['quantity'] ?? '' ?>">
                        </div>
                        <?php else : ?>

                        <?php // Envia os valores pelo POST sem permitir edição 
                                        ?>
                        <input type="hidden" name="items[<?= $index ?>][quantity]"
                            value="<?= $item['quantity'] ?? '' ?>">

                        <?php // Exibição visual para o comprador 
                                        ?>
                        <div class="col-lg-1">
                            <?php if ($index === 0): ?><label class="fw-bold">Qtd</label><?php endif; ?>
                            <input type="text" class="form-control" value="<?= $item['quantity'] ?? '' ?>" disabled>
                        </div>
                        <?php endif; ?>

                        <div class="col-lg-1">
                            <?php if ($index === 0): ?><label class="fw-bold">Un</label><?php endif; ?>
                            <select name="items[<?= $index ?>][adms_daman_measurement_units_id]" class="form-select">
                                <option value="">Selecione</option>
                                <?php foreach ($this->data['getAllMeasurementUnitsSelect'] as $unit):
                                                $selected = ($item['adms_daman_measurement_units_id'] ?? '') == $unit['id'] ? 'selected' : '';
                                            ?>
                                <option value="<?= $unit['id'] ?>" <?= $selected ?>><?= $unit['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (in_array("BuyerContent", $this->data['buttonPermissions'])): // Verifica se é comprador ou tem permissão de acessar
                                    ?>
                        <div class="col-lg-1">
                            <?php if ($index === 0): ?><label class="fw-bold">Comprado</label><?php endif; ?>
                            <input type="text" class="form-control" name="items[<?= $index ?>][purchased_quantity]"
                                value="<?= $item['purchased_quantity'] ?? '' ?>">
                        </div>

                        <div class="col-lg-1">
                            <?php if ($index === 0): ?><label class="fw-bold">Preço</label><?php endif; ?>
                            <input type="text" class="form-control" name="items[<?= $index ?>][unit_price]"
                                value="<?= $item['unit_price'] ?? '' ?>">
                        </div>
                        <?php else : ?>

                        <?php // Envia os dados do tipo hidden
                                        ?>
                        <input type="hidden" name="items[<?= $index ?>][purchased_quantity]"
                            value="<?= $item['purchased_quantity'] ?? '' ?>">
                        <input type="hidden" name="items[<?= $index ?>][unit_price]"
                            value="<?= $item['unit_price'] ?? '' ?>">


                        <?php // Exibição visual para o solicitante de compra 
                                        ?>
                        <div class="col-lg-1">
                            <?php if ($index === 0): ?><label class="fw-bold">Comprado</label><?php endif; ?>
                            <input type="text" class="form-control" value="<?= $item['purchased_quantity'] ?? '' ?>"
                                disabled>
                        </div>

                        <div class="col-lg-1">
                            <?php if ($index === 0): ?><label class="fw-bold">Preço</label><?php endif; ?>
                            <input type="text" class="form-control" value="<?= $item['unit_price'] ?? '' ?>" disabled>
                        </div>

                        <?php endif; ?>

                        <div class="col-lg-2">
                            <?php if ($index === 0): ?><label class="fw-bold">Status</label><?php endif; ?>
                            <select name="items[<?= $index ?>][adms_daman_acquisition_status_id]" class="form-select">
                                <option value="">Selecione</option>
                                <?php foreach ($this->data['getAllStatusSelect'] as $status):
                                                $selected = ($item['adms_daman_acquisition_status_id'] ?? '') == $status['id'] ? 'selected' : '';
                                            ?>
                                <option value="<?= $status['id'] ?>" <?= $selected ?>><?= $status['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php else: ?>
                    <!-- 🟢 ITEM NOVO (layout simples) -->
                    <div class="row g-1 item-group mb-2">

                        <input type="hidden" name="items[<?= $index ?>][is_new]" value="1">
                        <input type="hidden" name="items[<?= $index ?>][item_id]" value="">
                        <input type="hidden" name="items[<?= $index ?>][temp_id]" value="<?= uniqid('tmp_', true) ?>">

                        <div class="col-lg-6">
                            <input type="text" class="form-control" name="items[<?= $index ?>][description]"
                                value="<?= $item['description'] ?? '' ?>" placeholder="Descrição completa...">
                        </div>

                        <div class="col-lg-3">
                            <input type="text" class="form-control" name="items[<?= $index ?>][quantity]"
                                value="<?= $item['quantity'] ?? '' ?>" placeholder="Qtd">
                        </div>

                        <div class="col-lg-3">
                            <div class="d-flex">
                                <select name="items[<?= $index ?>][adms_daman_measurement_units_id]"
                                    class="form-select me-2">
                                    <option value="">Selecione</option>
                                    <?php foreach ($this->data['getAllMeasurementUnitsSelect'] as $unit):
                                                    $selected = ($item['adms_daman_measurement_units_id'] ?? '') == $unit['id'] ? 'selected' : '';
                                                ?>
                                    <option value="<?= $unit['id'] ?>" <?= $selected ?>><?= $unit['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="button" class="btn btn-danger btn-remove">-</button>
                            </div>
                        </div>

                    </div>
                    <?php endif; ?>

                    <?php endforeach; // finalização do foreach dos itens  
                        ?>

                    <?php endif; // finalização do if do array de itens  
                    ?>
                </div>

                <?php // Inlcuir resultados das unidade de medida do banco de dados para o js 
                    ?>
                <div id="units-data" data-units='<?= json_encode($this->data['getAllMeasurementUnitsSelect']) ?>'>
                </div>
                <div id="items-data" data-items='<?= json_encode($this->data['items'] ?? []) ?>'
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