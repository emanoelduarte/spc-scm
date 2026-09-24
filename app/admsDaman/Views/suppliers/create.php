<?php

use App\admsDaman\Helpers\BrazilStatesHelper;
use App\admsDaman\Helpers\CSRFHelper;

$form = $this->data['form'] ?? [];
$supplierTypes = $this->data['getAllTypesSuppliersSelect'] ?? [];

?>

<div class="container-fluid px-4">
    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Fornecedores</h2>
        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-suppliers">Fornecedores</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Cadastrar</li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Cadastrar</span>
            <span class="ms-auto d-sm-flex flex-row">
                <?php if (in_array('ListSuppliers', $this->data['buttonPermissions'])): ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'list-suppliers'; ?>" class="btn btn-info btn-sm me-1 mb-1">
                        <i class="fa-solid fa-list"></i> Listar
                    </a>
                <?php endif; ?>
            </span>
        </div>

        <div class="card-body">
            <?php include './app/admsDaman/Views/partials/alerts.php'; ?>

            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token"
                    value="<?= CSRFHelper::generateCSRFToken('form_create_supplier'); ?>">

                <div class="col-lg-8 col-sm-12">
                    <label for="legal_name" class="form-label">Razão Social:</label>
                    <input type="text" class="form-control" id="legal_name" name="legal_name"
                        value="<?= htmlspecialchars($form['legal_name'] ?? ''); ?>" placeholder="Nome da Empresa">
                </div>

                <div class="col-lg-4">
                    <label for="trade_name" class="form-label">Nome Fantasia:</label>
                    <input type="text" class="form-control" id="trade_name" name="trade_name"
                        value="<?= htmlspecialchars($form['trade_name'] ?? ''); ?>" placeholder="Nome Fantasia da Empresa">
                </div>

                <div class="col-lg-4">
                    <label for="cnpj" class="form-label">CNPJ:</label>
                    <input type="text" class="form-control" id="cnpj" name="cnpj"
                        value="<?= htmlspecialchars($form['cnpj'] ?? ''); ?>" placeholder="CNPJ da Empresa">
                </div>

                <div class="col-lg-4">
                    <label for="contact_name" class="form-label">Contato:</label>
                    <input type="text" class="form-control" id="contact_name" name="contact_name"
                        value="<?= htmlspecialchars($form['contact_name'] ?? ''); ?>"
                        placeholder="Nome da pessoa de contato">
                </div>

                <div class="col-lg-4">
                    <label for="phone" class="form-label">Telefone:</label>
                    <input type="text" class="form-control" id="phone" name="phone"
                        value="<?= htmlspecialchars($form['phone'] ?? ''); ?>" placeholder="Telefone de contato">
                </div>

                <div class="col-lg-4">
                    <label for="street" class="form-label">Logradouro:</label>
                    <input type="text" class="form-control" id="street" name="street"
                        value="<?= htmlspecialchars($form['street'] ?? ''); ?>" placeholder="Rua Francisca Nogueira">
                </div>

                <div class="col-lg-1">
                    <label for="number" class="form-label">Número:</label>
                    <input type="text" class="form-control" id="number" name="number"
                        value="<?= htmlspecialchars($form['number'] ?? ''); ?>" placeholder="2, S/N, 2A...">
                </div>

                <div class="col-lg-3">
                    <label for="complement" class="form-label">Complemento (Opcional):</label>
                    <input type="text" class="form-control" id="complement" name="complement"
                        value="<?= htmlspecialchars($form['complement'] ?? ''); ?>"
                        placeholder="Em frente, Fachada Azul...">
                </div>

                <div class="col-lg-4">
                    <label for="neighborhood" class="form-label">Bairro:</label>
                    <input type="text" class="form-control" id="neighborhood" name="neighborhood"
                        value="<?= htmlspecialchars($form['neighborhood'] ?? ''); ?>" placeholder="Bairro">
                </div>

                <div class="col-lg-2">
                    <label for="zip_code" class="form-label">CEP:</label>
                    <input type="text" class="form-control" id="zip_code" name="zip_code"
                        value="<?= htmlspecialchars($form['zip_code'] ?? ''); ?>" placeholder="66000-000">
                </div>

                <div class="col-lg-2">
                    <label for="city" class="form-label">Cidade:</label>
                    <input type="text" class="form-control" id="city" name="city"
                        value="<?= htmlspecialchars($form['city'] ?? ''); ?>" placeholder="Belém">
                </div>

                <div class="col-lg-4">
                    <label for="state" class="form-label">Estado</label>
                    <select name="state" id="state" class="form-select">
                        <option value="">Selecione o estado</option>
                        <?php foreach (BrazilStatesHelper::getStates() as $uf => $name): ?>
                            <option value="<?= htmlspecialchars($uf); ?>"
                                <?= (($form['state'] ?? '') === $uf) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-4">
                    <label for="adms_daman_suppliers_types_id" class="form-label">Atividade do fornecedor</label>
                    <select class="form-select" id="adms_daman_suppliers_types_id"
                        name="adms_daman_suppliers_types_id">
                        <option value="">Selecione o tipo</option>

                        <?php foreach ($supplierTypes as $supplierType): ?>
                            <option value="<?= (int) $supplierType['id']; ?>"
                                <?= ((int) ($form['adms_daman_suppliers_types_id'] ?? 0) === (int) $supplierType['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($supplierType['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-8">
                    <label for="email" class="form-label">E-mail:</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?= htmlspecialchars($form['email'] ?? ''); ?>" placeholder="E-mail">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm" onclick="showLoading()">
                        Cadastrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
