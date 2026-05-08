<?php

use App\admsDaman\Helpers\CSRFHelper;

?>
<div class="container-fluid px-4">
    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Fornecedores</h2>
        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-suppliers">Fornecedores</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </li>
        </ol>
    </div>
    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Editar</span>
            <span class="ms-auto d-sm-flex flex-row">
                <?php if (in_array("ViewSupplier", $this->data['buttonPermissions'])): ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'list-suppliers'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>
                <?php if (in_array("ViewSupplier", $this->data['buttonPermissions'])): ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'view-supplier/' . ($this->data['form']['id'] ?? ''); ?>" class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>
                <?php endif; ?>
            </span>
        </div>
        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            ?>

            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= CSRFHelper::generateCSRFToken('form_update_supplier'); ?>" id="">

                <input type="hidden" name="id" id="id" value="<?= $this->data['form']['id'] ?? ''; ?>">

                <div class="col-lg-8 col-sm-12">
                    <label for="legal_name" class="form-label">Razão Social:</label>
                    <input type="text" class="form-control" id="legal_name" name="legal_name" value="<?= $this->data['form']['legal_name'] ?? ''; ?>" placeholder="Nome da Empresa">
                </div>

                <div class="col-lg-4">
                    <label for="trade_name" class="form-label">Nome Fantasia:</label>
                    <input type="text" class="form-control" id="trade_name" name="trade_name" value="<?= $this->data['form']['trade_name'] ?? ''; ?>" placeholder="Nome Fantasia da Empresa">
                </div>

                <div class="col-lg-4">
                    <label for="cnpj" class="form-label">CNPJ:</label>
                    <input type="text" class="form-control" id="cnpj" name="cnpj" value="<?= $this->data['form']['cnpj'] ?? ''; ?>" placeholder="CNPJ da Empresa">
                </div>

                <div class="col-lg-4">
                    <label for="contact_name" class="form-label">Contato:</label>
                    <input type="text" class="form-control" id="contact_name" name="contact_name" value="<?= $this->data['form']['contact_name'] ?? ''; ?>" placeholder="Nome da pessoa de contato">
                </div>

                <div class="col-lg-4">
                    <label for="phone" class="form-label">Telefone:</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= $this->data['form']['phone'] ?? ''; ?>" placeholder="Telefone de contato">
                </div>

                <div class="col-lg-4">
                    <label for="adms_daman_suppliers_types_id" class="form-label">Atividade do fornecedor</label>
                    <select name="adms_daman_suppliers_types_id" class="form-select" id="adms_daman_suppliers_types_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe pacotes
                        if ($this->data['getAllTypesSuppliersSelect'] ?? false) {

                            // Percorrer array de pacotes
                            foreach ($this->data['getAllTypesSuppliersSelect'] as $getAllTypesSuppliersSelect) {
                                extract($getAllTypesSuppliersSelect);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_suppliers_types_id']) && $this->data['form']['adms_daman_suppliers_types_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$name</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label for="supplier_status" class="form-label">Status</label>

                    <select name="supplier_status" class="form-select" id="supplier_status">
                        <option value="" selected>Selecione</option>
                        <option value="1" <?= isset($this->data['form']['supplier_status']) && $this->data['form']['supplier_status'] == 1 ? 'selected' : ''; ?>>Ativo</option>
                        <option value="0" <?= isset($this->data['form']['supplier_status']) && $this->data['form']['supplier_status'] == 0 ? 'selected' : ''; ?>>Inativo</option>
                    </select>

                </div>

                <div class="col-lg-6">
                    <label for="email" class="form-label">E-mail:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= $this->data['form']['email'] ?? ''; ?>" placeholder="Seu melhor e-mail">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-warning btn-sm" onclick="showLoading()">Editar</button>
                </div>
            </form>
        </div>
    </div>
</div>