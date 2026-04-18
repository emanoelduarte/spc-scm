<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_delete_category');
?>
<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Categorias</h2>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-categories">Categorias</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header d-flex flex-column flex-sm-row gap-2">
            <span>Visualizar</span>
            <span class="ms-sm-auto d-sm-flex flex-row">
                <a href="<?= $_ENV['URL_ADM'] . 'list-categories'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>

                <a href="<?= $_ENV['URL_ADM'] . 'update-category/' . ($this->data['category']['id'] ?? ''); ?>" class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar</a>

                <?php  // Formulário para envio dos dados para deletar Categoria ?>
                    <form id="formDelete<?= $this->data['category']['id']; ?>" action="<?= $_ENV['URL_ADM']; ?>delete-category" method="POST">

                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <input type="hidden" name="id" id="id" value="<?php echo $this->data['category']['id'] ?? ''; ?>">

                        <button type="submit" class="btn btn-danger btn-sm me-1 mb-1" onclick="confirmDeletion(event, <?= $this->data['category']['id'] ?>)"><i class="fa-solid fa-trash"></i> Apagar</button>

                    </form>
            </span>
        </div>

        <div class="card-body">

            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            if (isset($this->data['category'])) :
                extract($this->data['category']);

                // O operador ternário verifica se $created_at não é null antes de chamar a strtotime(). Se $created_at for null, ele retorna uma string vazia.
                $created = ($created_at ? date('d/m/Y H:i:s', strtotime($created_at)) : "");
                $edited = ($updated_at ? date('d/m/Y H:i:s', strtotime($updated_at)) : "");
            ?>
                <dl class="row">
                    <dt class="col-sm-3">ID: </dt>
                    <dd class="col-sm-9"><?= $id ?></dd>
                    <dt class="col-sm-3">Nome: </dt>
                    <dd class="col-sm-9"><?= $name ?></dd>
                    <dt class="col-sm-3">Cadastrado: </dt>
                    <dd class="col-sm-9"><?= $created ?></dd>
                    <dt class="col-sm-3">Editado: </dt>
                    <dd class="col-sm-9"><?= $edited ?></dd>
                </dl>
            <?php else: ?>
                <?php // Caso a categoria não seja encontrada
                ?>
                <div class='alert alert-danger' role='alert'>Categoria não encontrada</div>
            <?php endif; ?>
        </div>
    </div>

</div>
