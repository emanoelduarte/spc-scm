<?php

use App\admsDaman\Helpers\CSRFHelper;

?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Compras</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a href="<?php echo $_ENV['URL_ADM']; ?>dashboard" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo $_ENV['URL_ADM']; ?>list-purchasings" class="text-decoration-none">Compras</a>
            </li>
            <li class="breadcrumb-item">Gerar Compra</li>
        </ol>

    </div>

    <div class="card mb-4 border-light shadow">

        <div class="card-header hstack gap-2">
            <span>Gerar</span>

            <span class="ms-auto d-sm-flex flex-row">
                <?php if (in_array("ListPurchasings", $this->data['buttonPermissions'])): ?>
                <a href="<?= $_ENV['URL_ADM'] . 'list-purchasings'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i
                        class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>
            </span>

        </div>
        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            // var_dump($this->data['getOrder'], $this->data['getItems']);
            ?>

            <form action="<?= $_ENV['URL_ADM'] ?>create-purchasing-quote" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token"
                    value="<?php echo CSRFHelper::generateCSRFToken('form_generate_purchasing_quote'); ?>" id="">

                <div class="col-lg-4 col-md-6 col-sm-12">
                    <label for="adms_daman_supplier_id" class="form-label">Fornecedores</label>

                    <select name="adms_daman_supplier_id" class="form-select" id="adms_daman_supplier_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe Fornecedores
                        if ($this->data['getAllSuppliersSelectActive'] ?? false) {

                            // Percorrer array de Fornecedores
                            foreach ($this->data['getAllSuppliersSelectActive'] as $getAllSuppliersSelectActive) {
                                extract($getAllSuppliersSelectActive);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_supplier_id']) && $this->data['form']['adms_daman_supplier_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$legal_name</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6 col-sm-12">
                    <label for="expected_receipt_date" class="form-label">Prazo de Entrega </label>
                    <input type="date" class="form-control" id="expected_receipt_date" name="expected_receipt_date"
                        placeholder="dd/mm/yyyy">

                    <script>
                    flatpickr("#expected_receipt_date", {
                        dateFormat: "d/m/Y",
                        locale: "pt", // Para português
                        minDate: new Date() // hoje + 3 dias
                    });
                    </script>
                </div>

                <div class="col-lg-5 col-md-6 col-sm-12">
                    <label for="solicitante" class="form-label">Solicitante:</label>
                    <input type="text" disabled class="form-control desabled" id="solicitante" name="solicitante"
                        value="<?= $_SESSION['user_name'] ?? '';  ?>" placeholder="Nome do Usuário">
                    <input type="hidden" class="form-control" id="adms_daman_user_id" name="adms_daman_user_id"
                        value="<?= $_SESSION['user_id'] ?? ''; ?>" placeholder="Id do usuário">
                </div>

                <div class="col-lg-1 col-md-12 col-sm-12">
                    <label for="adms_daman_order_id" class="form-label">N° Pedido:</label>
                    <input type="text" class="form-control desabled" id="adms_daman_order_id" name="adms_daman_order_id"
                        value="<?= $this->data['getOrder']['id'] ?? '';  ?>" placeholder="N° do pedido" readonly>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label for="adms_daman_project_id" class="form-label">O.S | Obra</label>

                    <?php
                    $projectId = $this->data['getOrder']['adms_daman_project_id'];
                    $project_name = $this->data['getOrder']['project_name'];
                    ?>

                    <input type="hidden" name="adms_daman_project_id" class="form-control desabled"
                        id="adms_daman_project_id" value='<?= $projectId ?>'>
                    <input type="text" name="project_name" class="form-control desabled" id="project_name"
                        value='<?= $project_name  ?>' readonly>

                </div>

                <div class="col-lg-6 col-md-12 col-sm-12">
                    <label for="service" class="form-label">Descrição do serviço:</label>
                    <input type="text" class="form-control desabled" id="service" name="service"
                        value="<?= $this->data['getOrder']['service'] ?? '';  ?>" placeholder="Descrição do serviço">
                </div>

                <div class="col-lg-1 col-md-12 col-sm-12">
                    <input type="hidden" class="form-control" id="adms_daman_acquisition_types_id"
                        name="adms_daman_acquisition_types_id"
                        value="<?= $this->data['getOrder']['adms_daman_acquisition_types_id'] ?? '';  ?>">

                    <label for="purchasing_name_type" class="form-label">Tipo:</label>
                    <input type="text" class="form-control desabled" id="purchasing_name_type"
                        name="purchasing_name_type" value="<?= $this->data['getOrder']['order_name_type'] ?? '';  ?>"
                        readonly>
                </div>

                <div class="col-lg-6 col-md-12 col-sm-12">
                    <label for="delivery_address" class="form-label">End. Entrega:</label>
                    <input type="text" class="form-control desabled" id="delivery_address" name="delivery_address"
                        value="<?= $this->data['getOrder']['project_adrress'] ?? '';  ?>" placeholder="N° do pedido">
                </div>

                <div class="col-lg-4 col-md-6 col-sm-12">
                    <label for="adms_daman_payment_methods_id" class="form-label">Forma de Pagamento</label>

                    <select name="adms_daman_payment_methods_id" class="form-select" id="adms_daman_payment_methods_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe Fornecedores
                        if ($this->data['getPaymentsSelect'] ?? false) {

                            // Percorrer array de Fornecedores
                            foreach ($this->data['getPaymentsSelect'] as $getPaymentsSelect) {
                                extract($getPaymentsSelect);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_payment_methods_id']) && $this->data['form']['adms_daman_payment_methods_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$name</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-lg-2 col-md-12 col-sm-12">
                    <label for="delivery_value" class="form-label">Frete</label>
                    <input type="text" class="form-control desabled" id="delivery_value" name="delivery_value"
                        value="<?= $this->data['form']['delivery_value'] ?? '';  ?>" placeholder="R$ 0.00">
                </div>
                <div class="col-lg-4 col-md-12 col-sm-12">
                    <label for="discount_value" class="form-label">Desconto</label>
                    <input type="text" class="form-control desabled" id="discount_value" name="discount_value"
                        value="<?= $this->data['form']['discount_value'] ?? '';  ?>" placeholder="R$ 0.00 ou %">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discount_type" id="discount_type1" value="fixed">
                    <label class="form-check-label" for="discount_type1">
                        R$ (valor fixo)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discount_type" id="discount_type2"
                        value="percent">
                    <label class="form-check-label" for="discount_type2">
                        % (percentual)
                    </label>
                </div>

                <?php
                if ($this->data['getItems'] ?? false) :
                    // var_dump($this->data['getItems']);
                ?>

                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Item</th>
                            <th>Descrição</th>
                            <th>Unidade</th>
                            <th>Quantidade</th>
                            <th>Preço Unit.</th>
                            <th>Total</th>
                            <th>Selecione</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            // Iniciar a variável contadora e total
                            $qtd_items = 0;
                            $sub_tot = 0;
                            foreach ($this->data['getItems'] as  $key => $item):
                            ?>

                        <tr>
                            <td><?= $qtd_items += 1 ?></td>
                            <td>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <input type="text" class="form-control" name="items[<?= $key ?>][description]"
                                        value="<?= $item['description']; ?>" readonly>
                                </div>
                            </td>
                            <td>
                                <input type="hidden" name="items[<?= $key ?>][adms_daman_measurement_units_id]"
                                    value="<?= $item['adms_daman_measurement_units_id']; ?>">

                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <input type="text" class="form-control" name="items[<?= $key ?>][measurement_units]"
                                        value="<?= $item['measurement_units']; ?>" readonly>
                                </div>
                            </td>
                            <td>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <input type="text" class="form-control"
                                        name="items[<?= $key ?>][purchased_quantity]"
                                        value="<?= $item['purchased_quantity']; ?>" readonly>
                                </div>
                            </td>
                            <td>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <input type="text" class="form-control" name="items[<?= $key ?>][unit_price]"
                                        value="<?= $item['unit_price']; ?>" readonly>
                                </div>
                            </td>
                            <td>
                                <?php
                                        $tot_item = $item['purchased_quantity'] * $item['unit_price']
                                        ?>
                                <div class="col-lg-12 col-md-12 col-sm-12 d-flex align-items-center">
                                    <span><?= number_format($tot_item, 2, ',', '.') ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input"
                                        name="items[<?= $key ?>][selected_item]" value="1" id="checkDefault">
                                </div>
                            </td>
                        </tr>

                        <?php
                            endforeach;
                            ?>
                    </tbody>
                </table>

                <?php else: ?>
                <div class='alert alert-danger' role='alert'>Compra sem itens para exibir</div>
                <?php endif; ?>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm" onclick="showLoading()"> <i
                            class="fa-solid fa-clock"></i> Enviar para
                        Aprovação</button>
                </div>
            </form>
        </div>
    </div>
</div>