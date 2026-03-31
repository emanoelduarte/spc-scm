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
                <a href="<?php echo $_ENV['URL_ADM']; ?>list-orders" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>
            </span>

        </div>
        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            ?>

            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_create_order'); ?>" id="">

                <div class="col-lg-6 col-md-6 col-sm-12">
                    <label for="solicitante" class="form-label">Prev. Recebimento </label>
                    <input type="date" class="form-control" id="expected_receipt_date" name="expected_receipt_date" value="<?= $this->data['form']['expected_receipt_date'] ?? ''; ?>" placeholder="Id do usuário">
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

                    <select class="form-select" id="adms_daman_order_types_id" name="adms_daman_order_types_id">
                        <option selected>Selecione a tipo</option>
                        <option value="1" <?= isset($this->data['form']['adms_daman_order_types_id']) && $this->data['form']['adms_daman_order_types_id'] == 1 ? 'selected' : ''; ?>>COMPRA</option>
                        <option value="2" <?= isset($this->data['form']['adms_daman_order_types_id']) && $this->data['form']['adms_daman_order_types_id'] == 2 ? 'selected' : ''; ?>>LOCAÇÃO</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12 d-none" id="locationPeriod">
                    <label for="rental_period" class="form-label">Período de Locação</label>

                    <select class="form-select" id="rental_period" name="rental_period">
                        <option selected value="">Selecione o período</option>
                        <option value="1" <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 1 ? 'selected' : ''; ?>>DIÁRIA</option>
                        <option value="7" <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 1 ? 'selected' : ''; ?>>7 DIAS</option>
                        <option value="15" <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 1 ? 'selected' : ''; ?>>15 DIAS</option>
                        <option value="30" <?= isset($this->data['form']['rental_period']) && $this->data['form']['rental_period'] == 1 ? 'selected' : ''; ?>>30 DIAS</option>
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

                <div id="items-container" class="row g-3 ">
                    <div class="row g-1 item-group mb-2 mt-n1">
                        <?php
                        // Percorre o array form até encontrar o elemento 'description', existindo ele continua a executar para mostrar ao menos um campo inicial, para o usuário.
                        foreach ($this->data['form']['description'] as $key => $desc):
                        ?>
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <input type="text" class="form-control desabled" id="description" name="description[]" value="<?= $this->data['form']['description'][$key] ?? '';  ?>" placeholder="Descrição completa: Marca, modelo e referências, evitando compras erradas.">
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <input type="text" class="form-control desabled" id="quantity" name="quantity[]" value="<?= $this->data['form']['quantity'][$key] ?? '';  ?>" placeholder="Qtd">
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <div class="d-flex">
                                    <input type="text" class="form-control desabled" id="unit" name="unit[]" value="<?= $this->data['form']['unit'][$key] ?? '';  ?>" placeholder="Un">
                                    <button type="button" class="btn btn-danger ms-2 btn-remove">-</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 text-end">
                    <button type="button" id="add-item" class="btn btn-success btn-sm mb-3">+ Adicionar Item</button>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>