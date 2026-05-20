<?php

use App\admsDaman\Helpers\CSRFHelper;

?>
<div class="container-fluid px-4">
    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Estoque</h2>
        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-material-stock">Estoque</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </li>
        </ol>
    </div>
    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Editar</span>
            <span class="ms-auto d-sm-flex flex-row">

                <a href="<?= $_ENV['URL_ADM'] . 'list-material-stock'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i
                        class="fa-solid fa-list"></i> Listar</a>


                <a href="<?= $_ENV['URL_ADM'] . 'view-material-stock/' . ($this->data['form']['id'] ?? ''); ?>"
                    class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>

            </span>
        </div>
        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            ?>

            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token"
                    value="<?= CSRFHelper::generateCSRFToken('form_update_material'); ?>" id="">

                <input type="hidden" name="id" id="id" value="<?= $this->data['form']['id'] ?? ''; ?>">

                <div class="col-5">
                    <label for="name" class="form-label">Material:</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="<?= $this->data['form']['name'] ?? ''; ?>" placeholder="Material">
                </div>

                <div class="col-lg-1">
                    <label for="name" class="form-label">Unidades:</label>

                    <select name="adms_daman_measurement_units_id" class="form-select"
                        id="adms_daman_measurement_units_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe Unidades
                        if ($this->data['getAllMeasurementUnitsSelect'] ?? false) {

                            // Percorrer array de unidade
                            foreach ($this->data['getAllMeasurementUnitsSelect'] as $units) {
                                extract($units);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_measurement_units_id']) && $this->data['form']['adms_daman_measurement_units_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$name</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-lg-2 col-sm-12">
                    <label for="adms_daman_category_id" class="form-label">Categoria</label>

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

                <input type="hidden" name="adms_daman_project_id"
                    value="<?= $this->data['form']['adms_daman_project_id'] ?? '' ?>">

                <div class="col-lg-2 col-sm-12">
                    <label class="form-label">Quantidade Mínima</label>
                    <input type="number" name="min_quantity" class="form-control" min="0.01" step="0.01"
                        value="<?= $this->data['form']['min_quantity'] ?? ''; ?>" required>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-warning btn-sm" onclick="showLoading()">Editar</button>
                </div>
            </form>
        </div>
    </div>
</div>