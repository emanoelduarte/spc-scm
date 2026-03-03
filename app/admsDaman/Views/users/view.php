<?php
// Gerar o token CSRF para validar o usuário

use App\admsDaman\Helpers\CSRFHelper;

$csrf_token = CSRFHelper::generateCSRFToken('form_delete_user');
?>
<div class="container-fluid px-4">
    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Usuários</h2>
        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-users">Usuários</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Visualizar Usuário</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header d-flex flex-column flex-sm-row gap-2">
            <span>Visualizar</span>
            <span class="ms-sm-auto d-sm-flex flex-row">
                <a href="<?= $_ENV['URL_ADM'] . 'list-users'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>

                <a href="<?= $_ENV['URL_ADM'] . 'update-user/' . ($this->data['user']['id'] ?? ''); ?>" class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar</a>

                <a href="<?= $_ENV['URL_ADM'] . 'update-password-user/' . ($this->data['user']['id'] ?? ''); ?>" class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar Senha</a>

                <?php  // Formulário para envio dos dados para deletar Usuário 
                ?>
                <form id="formDelete<?= $this->data['user']['id']; ?>" action="<?= $_ENV['URL_ADM']; ?>delete-user" method="POST">

                    <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                    <input type="hidden" name="id" id="id" value="<?= $this->data['user']['id'] ?? ''; ?>">

                    <button type="submit" class="btn btn-danger btn-sm me-1 mb-1" onclick="confirmDeletion(event, <?= $this->data['user']['id'] ?>)"> <i class="fa-solid fa-trash"></i> Apagar</button>

                </form>
            </span>
        </div>

        <div class="card-body">

            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            // Acessa o IF quando encontrar o elemento no array users
            if (isset($this->data['user'])):
                // Extrair o Array pela coluna
                extract($this->data['user']);

                // O operador ternário verifica se $created_at não é null antes de chamar a strtotime(). Se $created_at for null, ele retorna uma strng vazia.
                $created = ($created_at ? date('d/m/Y H:i:s', strtotime($created_at)) : "");
                $edited = ($updated_at ? date('d/m/Y H:i:s', strtotime($updated_at)) : "");
            ?>

                <dl class="row">
                    <dt class="col-sm-3">ID: </dt>
                    <dd class="col-sm-9"><?= $id ?></dd>
                    <dt class="col-sm-3">Nome: </dt>
                    <dd class="col-sm-9"><?= $name ?></dd>
                    <dt class="col-sm-3">E-mail: </dt>
                    <dd class="col-sm-9"><?= $email ?></dd>
                    <dt class="col-sm-3">Usuário: </dt>
                    <dd class="col-sm-9"><?= $username ?></dd>
                    <dt class="col-sm-3">Cadastrado: </dt>
                    <dd class="col-sm-9"><?= $created ?></dd>
                    <dt class="col-sm-3">Editado: </dt>
                    <dd class="col-sm-9"><?= $edited ?></dd>
                </dl>

            <?php else: ?>
                <?php // Caso usuário não seja encontrado
                ?>
                <div class='alert alert-danger' role='alert'>Usuário não encontrado</div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    var_dump($this->data['userAccessLevels']);
    ?>

</div>