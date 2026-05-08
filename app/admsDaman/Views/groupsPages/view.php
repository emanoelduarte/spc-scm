<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_delete_group');

?>
<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Grupos</h2>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-groups-pages">Grupos</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header d-flex flex-column flex-sm-row gap-2">
            <span>Visualizar</span>
            <span class="ms-sm-auto d-sm-flex flex-row">
                <?php if (in_array("ListGroupsPages", $this->data['buttonPermissions'])) : ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'list-groups-pages'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>

                <?php if (in_array("UpdateGroupPage", $this->data['buttonPermissions'])) : ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'update-group-page/' . ($this->data['group']['id'] ?? ''); ?>" class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                <?php endif; ?>

                <?php if (in_array("DeleteGroupPage", $this->data['buttonPermissions'])) : ?>
                    <?php  // Formulário para envio dos dados para deletar Grupo de página ?>
                    <form id="formDelete<?= $this->data['group']['id']; ?>" action="<?= $_ENV['URL_ADM']; ?>delete-group-page" method="POST">

                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <input type="hidden" name="id" id="id" value="<?php echo $this->data['group']['id'] ?? ''; ?>">

                        <button type="submit" class="btn btn-danger btn-sm me-1 mb-1" onclick="confirmDeletion(event, <?= $this->data['group']['id']; ?>)"><i class="fa-solid fa-trash"></i> Apagar</button>

                    </form>
                <?php endif; ?>
                </td>
            </span>
        </div>

        <div class="card-body">

            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            if (isset($this->data['group'])) :
                extract($this->data['group']);

                // O operador ternário verifica se $created_at não é null antes de chamar a strtotime(). Se $created_at for null, ele retorna uma string vazia.
                $created = ($created_at ? date('d/m/Y H:i:s', strtotime($created_at)) : "");
                $edited = ($updated_at ? date('d/m/Y H:i:s', strtotime($updated_at)) : "");
            ?>
                <dl class="row">
                    <dt class="col-sm-3">ID: </dt>
                    <dd class="col-sm-9"><?= $id ?></dd>
                    <dt class="col-sm-3">Nome: </dt>
                    <dd class="col-sm-9"><?= $name ?></dd>
                    <dt class="col-sm-3">Observação: </dt>
                    <dd class="col-sm-9"><?= $obs ?></dd>
                    <dt class="col-sm-3">Cadastrado: </dt>
                    <dd class="col-sm-9"><?= $created ?></dd>
                    <dt class="col-sm-3">Editado: </dt>
                    <dd class="col-sm-9"><?= $edited ?></dd>
                </dl>
            <?php else: ?>
                <?php // Caso usuário não seja encontrado
                ?>
                <div class='alert alert-danger' role='alert'>Grupo não encontrado</div>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php
