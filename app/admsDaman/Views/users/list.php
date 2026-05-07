<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_delete_user');
?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Usuários</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Usuários</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Listar</span>
            <span class="ms-auto">
                <?php
                if (in_array('CreateUser', $this->data['buttonPermissions'])) :
                ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'create-user'; ?>" class="btn btn-success btn-sm"><i class="fa-solid fa-user-plus"></i> Cadastrar</a>
                <?php endif; ?>
            </span>
        </div>

        <div class="card-body">
            <?php // Incluir arquivo rsponsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            // Acessa o IF quando encontrar o elemento no array users
            if ($this->data['users'] ?? false) {
            ?>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nome</th>
                            <th scope="col" class="d-none d-md-table-cell">E-mail</th>
                            <th scope="col" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        // Percorrer o array de usuários
                        foreach ($this->data['users'] as $user) {
                            extract($user);
                        ?>
                            <tr>
                                <td><?= $id ?></td>
                                <td><?= $name ?></td>
                                <td class="d-none d-md-table-cell"><?= $email ?></td>
                                <td class="text-center">
                                    <?php if (in_array('ViewUser', $this->data['buttonPermissions'])) : ?>
                                        <a href="<?= $_ENV['URL_ADM'] . 'view-user/' . $id; ?>" class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>
                                    <?php endif; ?>

                                    <?php if (in_array('UpdateUser', $this->data['buttonPermissions'])) : ?>
                                        <a href="<?= $_ENV['URL_ADM'] . 'update-user/' . $id; ?>" class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                                    <?php endif; ?>

                                    <?php  // Formulário para envio dos dados para deletar Usuário 
                                    ?>
                                    <?php if (in_array('DeleteUser', $this->data['buttonPermissions'])) : ?>
                                        <form id="formDelete<?= $id; ?>" action="<?= $_ENV['URL_ADM']; ?>delete-user" method="POST" class="d-inline">

                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                                            <input type="hidden" name="id" id="id" value="<?= $id ?? ''; ?>">

                                            <button type="submit" class="btn btn-danger btn-sm me-1 mb-1" onclick="confirmDeletion(event, <?= $id ?>)"> <i class="fa-solid fa-trash"></i> Apagar</button>

                                        </form>
                                    <?php endif; ?>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php
                // Adiconar o arquivo de paginação
                require_once './app/admsDaman/Views/partials/pagination.php';
            } else {
                echo "<div class='alert alert-danger' role='alert'>Nenhum Usuário encontrado</div>";
            }
            ?>
        </div>
    </div>
</div>