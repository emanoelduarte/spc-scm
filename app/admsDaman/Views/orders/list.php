<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_delete_order');
?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Pedidos</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Pedidos</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Filtrar</span>
        </div>

        <div class="card-body">
            <?php // Campo para pesquisar pedido por numero 
            ?>
            <form action="" method="POST" class="row g-3">
                <div class="col-lg-2 col-md-12 col-sm-12">
                    <input type="text" class="form-control desabled" id="order_number" name="order_number" value="<?= $order_number ?? '' ?>" placeholder="Número do pedido">
                </div>

                <div class="col-lg-2 col-md-12 col-sm-12">
                    <button type="submit" class="btn btn-success">Filtrar</button>
                </div>
            </form>

        </div>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Listar</span>
            <span class="ms-auto">
                <a href="<?= $_ENV['URL_ADM'] . 'create-order'; ?>" class="btn btn-success btn-sm"><i class="fa-solid fa-user-plus"></i> Cadastrar</a>
            </span>
        </div>

        <div class="card-body">
            <?php // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            // Acessa o IF quando encontrar o elemento no array orders
            if ($this->data['orders'] ?? false) {
            ?>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Pedido</th>
                            <th scope="col">Obra</th>
                            <th scope="col" class="d-none d-md-table-cell">Status</th>
                            <th scope="col" class="d-none d-md-table-cell">Data do Pedido</th>
                            <th scope="col" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Percorrer o array de Pedidos
                        foreach ($this->data['orders'] as $order) {
                            extract($order);

                            $created = ($created_at ? date('d/m/Y', strtotime($created_at)) : "");
                        ?>

                            <tr>
                                <td><?= $pedido_id; ?></td>
                                <td><?= $project_name; ?></td>
                                <td><?= $status_name; ?></td>

                                <td class="d-none d-md-table-cell"><?= $created; ?></td>
                                <td class="d-md-flex flex-row justify-content-center">
                                    <a href="<?= $_ENV['URL_ADM'] . 'view-order/' . $pedido_id; ?>" class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>

                                    <?php if (($order) and ($order['adms_daman_order_types_id'] == 1)): ?>

                                        <a href="<?= $_ENV['URL_ADM'] . 'update-order/' . $pedido_id; ?>" class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar</a>

                                    <?php else: ?>

                                        <a href="<?= $_ENV['URL_ADM'] . 'update-rental-order/' . $pedido_id; ?>" class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar</a>

                                    <?php endif; ?>


                                    <?php  // Formulário para envio dos dados para deletar Pedido 
                                    // 
                                    ?>
                                    <form id="formDelete<?= $pedido_id; ?>" action="<?= $_ENV['URL_ADM']; ?>delete-order" method="POST">

                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                                        <input type="hidden" name="id" id="id" value="<?= $pedido_id ?? ''; ?>">

                                        <button type="submit" class="btn btn-danger btn-sm me-1 mb-1" onclick="confirmDeletion(event, <?= $pedido_id ?>)"> <i class="fa-solid fa-trash"></i> Apagar</button>

                                    </form>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php
                // Adiconar o arquivo de paginação
                require_once './app/admsDaman/Views/partials/pagination.php';
            } else {
                echo "<div class='alert alert-danger' role='alert'>Nenhum Pedido encontrado</div>";
            }
            ?>
        </div>
    </div>
</div>