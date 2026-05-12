<?php
// Gerar o token CSRF para validar o fornecedor

use App\admsDaman\Helpers\CSRFHelper;

$csrf_token = CSRFHelper::generateCSRFToken('form_delete_supplier');
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

            <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header d-flex flex-column flex-sm-row gap-2">
            <span>Visualizar</span>
            <span class="ms-sm-auto d-sm-flex flex-row">
                <?php if (in_array("ListSuppliers", $this->data['buttonPermissions'])): ?>
                <a href="<?= $_ENV['URL_ADM'] . 'list-suppliers'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i
                        class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>

                <?php if (in_array("UpdateSupplier", $this->data['buttonPermissions'])): ?>
                <a href="<?= $_ENV['URL_ADM'] . 'update-supplier/' . ($this->data['supplier']['id'] ?? ''); ?>"
                    class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                <?php endif; ?>

                <?php if (in_array("DeleteSupplier", $this->data['buttonPermissions'])): ?>
                <?php  // Formulário para envio dos dados para deletar Usuário 
                    ?>
                <form id="formDelete<?= $this->data['supplier']['id']; ?>"
                    action="<?= $_ENV['URL_ADM']; ?>delete-supplier" method="POST">

                    <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                    <input type="hidden" name="id" id="id" value="<?= $this->data['supplier']['id'] ?? ''; ?>">

                    <button type="submit" class="btn btn-danger btn-sm me-1 mb-1"
                        onclick="confirmDeletion(event, <?= $this->data['supplier']['id'] ?>)"> <i
                            class="fa-solid fa-trash"></i> Apagar</button>
                </form>
                <?php endif; ?>
            </span>
        </div>

        <div class="card-body">

            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            // Acessa o IF quando encontrar o elemento no array fornecedor
            if (isset($this->data['supplier'])):
                // Extrair o Array pela coluna
                extract($this->data['supplier']);
                extract($this->data['supplierAddresses']);

                // O operador ternário verifica se $created_at não é null antes de chamar a strtotime(). Se $created_at for null, ele retorna uma strng vazia.
                $created = ($created_at ? date('d/m/Y H:i:s', strtotime($created_at)) : "");
                $edited = ($updated_at ? date('d/m/Y H:i:s', strtotime($updated_at)) : "");
            ?>

            <dl class="row">
                <dt class="col-sm-3">ID: </dt>
                <dd class="col-sm-9"><?= $id ?></dd>
                <dt class="col-sm-3">Razão Social: </dt>
                <dd class="col-sm-9"><?= $legal_name ?></dd>
                <dt class="col-sm-3">Nome Fantasia </dt>
                <dd class="col-sm-9"><?= $trade_name ?></dd>
                <dt class="col-sm-3">CNPJ: </dt>
                <dd class="col-sm-9"><?= $cnpj ?></dd>
                <dt class="col-sm-3">Contato: </dt>
                <dd class="col-sm-9"><?= $contact_name ?></dd>
                <dt class="col-sm-3">Telefone: </dt>
                <dd class="col-sm-9"><?= $phone ?></dd>
                <dt class="col-sm-3">Endereço: </dt>
                <dd class="col-sm-9">
                    <?= $street . ", " . $number . " " . $complement . " " . $neighborhood . " - ", $zip_code . " - " . $city . "/" . $state ?>
                </dd>
                <dt class="col-sm-3">E-mail: </dt>
                <dd class="col-sm-9">
                    <?php echo $email ? $email : "Email não cadastrado"; ?>
                </dd>
                <dt class="col-sm-3">Formas de Pagamento Aceitas: </dt>
                <dd class="col-sm-9"> <?= $accepted_payments ?></dd>
                <dt class="col-sm-3">Atividade do Fornecedor: </dt>
                <dd class="col-sm-9"><?= $supplier_type ?></dd>
                <dt class="col-sm-3">Status: </dt>
                <dd class="col-sm-9">
                    <?= $supplier_status ? "<span class='badge text-bg-success'>Ativo</span>" : "<span class='badge text-bg-danger'>Inativo</span>"; ?>
                </dd>
                <dt class="col-sm-3">Cadastrado: </dt>
                <dd class="col-sm-9"><?= $created ?></dd>
                <dt class="col-sm-3">Editado: </dt>
                <dd class="col-sm-9"><?= $edited ?></dd>
            </dl>

            <?php else: ?>
            <?php // Caso o fornecedor não seja encontrado
                ?>
            <div class='alert alert-danger' role='alert'>Fornecedor não encontrado</div>
            <?php endif; ?>
        </div>
    </div>
</div>