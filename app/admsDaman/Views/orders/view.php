<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token_item = CSRFHelper::generateCSRFToken('form_delete_item');
$csrf_token = CSRFHelper::generateCSRFToken('form_delete_order');
$csrf_token_add_comment = CSRFHelper::generateCSRFToken('csrf_token_add_comment');

?>
<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Pedidos</h2>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-orders">Pedidos</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
            </li>
        </ol>
    </div>

    <?php
    $order_status_id = $this->data['order']['order_status_id'];
    // Verificar o Status e aplicar a cor na borda do pedido
    switch ($order_status_id) {
        case 1:
            $highlightClass = "highlight-analysis";
            break;
        case 2:
            $highlightClass = "highlight-budget";
            break;
        case 3:
            $highlightClass = "highlight-purchased";
            break;
        case 4:
            $highlightClass = "highlight-partial-purchase";
            break;
        case 5:
            $highlightClass = "highlight-delivered";
            break;
        case 6:
            $highlightClass = "highlight-partial-delivery";
            break;
        case 7:
            $highlightClass = "highlight-rented";
            break;
        case 8:
            $highlightClass = "highlight-returned";
            break;
        case 9:
            $highlightClass = "highlight-partial-return";
            break;
        case 10:
            $highlightClass = "highlight-canceled";
            break;
        default:
            $highlightClass = "highlight-analysis";
    }
    ?>
    <div class="<?= $highlightClass ?> card mb-4 shadow">
        <div class="card-header d-flex flex-column flex-sm-row gap-2">
            <span>Visualizar</span>
            <span class="ms-sm-auto d-sm-flex flex-row">

                <?php if (in_array("ListOrders", $this->data['buttonPermissions'])): ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'list-orders'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i
                            class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>

                <?php if (in_array("ViewOrder", $this->data['buttonPermissions'])): ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'view-order/' . ($this->data['order']['id'] ?? ''); ?>"
                        class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>
                <?php endif; ?>


                <?php if (isset($this->data['order']) and ($this->data['order']['adms_daman_acquisition_types_id'] == 1)): ?>

                    <?php if (in_array("UpdateOrder", $this->data['buttonPermissions'])): ?>
                        <a href="<?= $_ENV['URL_ADM'] . 'update-order/' . ($this->data['order']['id'] ?? ''); ?>"
                            class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
                    <?php endif; ?>

                <?php else: ?>

                    <?php if (in_array("UpdateRentalOrder", $this->data['buttonPermissions'])): ?>
                        <a href="<?= $_ENV['URL_ADM'] . 'update-rental-order/' . ($this->data['order']['id'] ?? ''); ?>"
                            class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if ($this->data['order']['oder_type_id'] != 2) : ?>

                    <?php if (in_array("GeneratePurchasing", $this->data['buttonPermissions'])): ?>
                        <a href="<?= $_ENV['URL_ADM'] . 'generate-purchasing/' . ($this->data['order']['id'] ?? ''); ?>"
                            class="btn btn-success btn-sm me-1 mb-1"><i class="fa-solid fa-bag-shopping"></i> Gerar Compra</a>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if (in_array("DeleteOrder", $this->data['buttonPermissions'])) : ?>
                    <?php  // Formulário para envio dos dados para deletar Pedido 
                    ?>
                    <form id="formDelete<?= ($this->data['order']['id'] ?? ''); ?>"
                        action="<?= $_ENV['URL_ADM']; ?>delete-order" method="POST">

                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                        <input type="hidden" name="id" id="id" value="<?= ($this->data['order']['id'] ?? ''); ?>">

                        <button type="submit" class="btn btn-danger btn-sm me-1 mb-1"
                            onclick="confirmDeletion(event, <?= ($this->data['order']['id'] ?? '') ?>)"> <i
                                class="fa-solid fa-trash"></i> Apagar</button>

                    </form>
                <?php endif; ?>
                </td>
            </span>
        </div>

        <div class="card-body">

            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            if (isset($this->data['order'])):
                extract($this->data['order']);

                // O operador ternário verifica se $created_at não é null antes de chamar a strtotime(). Se $created_at for null, ele retorna uma string vazia.
                $created = ($created_at ? date('d/m/Y H:i:s', strtotime($created_at)) : "");
                $edited = ($updated_at ? date('d/m/Y H:i:s', strtotime($updated_at)) : "");
                $expected_receipt_date = ($expected_receipt_date ? date('d/m/Y H:i:s', strtotime($expected_receipt_date)) : "");
            ?>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Pedido:</strong> <?= $id ?></p>
                        <p><strong>Data:</strong> <?= $created ?></p>
                        <p><strong>Data da Modificação:</strong> <?= $edited ?></p>
                        <p><strong>Status:</strong> <?= $order_status ?></p>
                        <p><strong>Categoria:</strong> <?= $category_name ?></p>
                        <p><strong>Obra:</strong> <?= $adms_daman_project_id . " | " . $project_name ?></p>
                        <p><strong>Endereço da Obra:</strong> <?= $project_adrress ?></p>
                    </div>

                    <div class="col-md-6">
                        <p><strong>Tipo:</strong> <?= $order_name_type ?></p>
                        <p><strong>Prev. Recebimento:</strong> <?= $expected_receipt_date ?></p>
                        <p><strong>Solicitante:</strong> <?= $usr_name ?></p>

                        <?php if ($order_name_type == 'LOCAÇÃO'): ?>

                            <p><strong>Locador:</strong> <?= $supplier_name ?></p>
                            <p><strong>Contrato:</strong> <?= $rental_contract ?></p>
                            <p><strong>Periodo:</strong> <?= $rental_period ?> Dias</p>

                        <?php endif; ?>

                    </div>

                    <hr>

                    <p><strong>Serviço:</strong> <?= $service ?></p>
                    <p><strong>Observação:</strong> <?= $observation ?></p>
                </div>
            <?php else: ?>
                <?php // Caso a Obra não seja encontrada
                ?>
                <div class='alert alert-danger' role='alert'>Pedido não encontrada</div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    if ($this->data['items'] ?? false) :
    ?>
        <div class="card mb-4">
            <div class="card-header">
                Itens do Pedido
            </div>

            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="d-none d-md-table-cell">Item</th>
                            <th>Descrição</th>
                            <th>Unidade</th>
                            <?php if ($order_name_type == 'LOCAÇÃO'): ?>
                                <th>Locado</th>
                                <th>Devolvido</th>
                            <?php else: ?>
                                <th class="d-none d-md-table-cell">Qtd</th>
                                <th>Comprado</th>
                            <?php endif ?>
                            <th>Preço Unit.</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Iniciar a variável contadora
                        $qtd_items = 0;
                        foreach ($this->data['items'] as $item):
                        ?>
                            <tr>
                                <td class="d-none d-md-table-cell"><?= $qtd_items += 1 ?></td>
                                <td><?= $item['description'] ?></td>
                                <td><?= $item['measurement_units'] ?></td>

                                <?php if ($order_name_type == 'LOCAÇÃO'): ?>
                                    <td>
                                        <?php

                                        $rented_quantity = $item['rented_quantity'] ?? '0';
                                        echo number_format($rented_quantity, 2, '.', ',');

                                        ?>
                                    </td>
                                    <td>
                                        <?php

                                        $returned_quantity = $item['returned_quantity'] ?? '0';
                                        echo number_format($returned_quantity, 2, '.', ',');

                                        ?>
                                    </td>
                                <?php else: ?>
                                    <td class="d-none d-md-table-cell"><?= $item['quantity'] ?></td>
                                    <td>
                                        <?php

                                        $purchased_quantity = $item['purchased_quantity'] ?? '0';
                                        echo number_format($purchased_quantity, 2, '.', ',');

                                        ?>
                                    </td>
                                <?php endif; ?>
                                <td><?php
                                    $unit_price = $item['unit_price'] ?? '0.00';
                                    echo "R$ " . number_format($unit_price, 2, ',', '.');
                                    ?>
                                </td>

                                <?php if ($order_name_type == 'LOCAÇÃO'): ?>
                                    <td>
                                        R$ <?= number_format($rented_quantity * $item['unit_price'], 2, ',', '.') ?>
                                    </td>
                                <?php else: ?>
                                    <td>
                                        R$ <?= number_format($item['purchased_quantity'] * $item['unit_price'], 2, ',', '.') ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?= $item['item_status_name'] ?? ''; ?>
                                </td>
                                <td>

                                    <?php  // Formulário para envio dos dados para deletar Item do pedido 
                                    ?>
                                    <form id="formDelete<?= $item['item_id']; ?>" action="<?= $_ENV['URL_ADM']; ?>delete-item"
                                        method="POST">

                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token_item; ?>">

                                        <input type="hidden" name="order_id" id="order_id" value="<?= $id ?? ''; ?>">

                                        <input type="hidden" name="item_id" id="item_id" value="<?= $item['item_id'] ?? ''; ?>">

                                        <button type="submit" class="btn btn-danger d-block btn-sm me-1 mb-1"
                                            onclick="confirmDeletion(event, <?= $item['item_id']; ?>)"> <i
                                                class="fa-solid fa-trash"></i> Excluir</button>

                                    </form>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else : ?>
        <div class='alert alert-danger' role='alert'>Pedido sem itens para exibir</div>
    <?php endif ?>

    <!-- Card de atividades-->
    <div class="card">
        <div class="card-header d-flex flex-column flex-sm-row gap-2">
            <span>Atividades do pedido</span>
            <span class="ms-sm-auto d-sm-flex flex-row">
            </span>
        </div>

        <div class="card-body">

            <?php  // Formulário para envio dos dados para deletar Pedido 
            // 
            ?>
            <form action="" method="POST">

                <input type="hidden" name="csrf_token" value="<?= $csrf_token_add_comment; ?>">

                <input type="hidden" name="id" id="id" value="<?= $this->data['order']['id'] ?? ''; ?>">

                <div class="col-12 mb-2">
                    <label for="new_user_comment" class="form-label fw-bold">Adicionar novo comentário</label>
                    <textarea class="form-control" placeholder="Digite o comentário" name="new_user_comment"
                        id="new_user_comment"
                        style="height: 100px"><?= $this->data['form']['new_user_comment'] ?? ''; ?></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm me-1 mb-1">
                        Comentar <i class="fa-solid fa-share"></i></button>
                </div>


            </form>
            <?php if ($this->data['formatedComments'] ?? false): ?>
                <div class="timeline mt-4">

                    <?php foreach ($this->data['formatedComments'] as $comment): ?>

                        <div class="timeline-item mb-4 d-flex">

                            <!-- Ícone -->
                            <div class="me-3">
                                <span class="badge bg-<?= $comment['color'] ?> p-2 rounded-circle">
                                    <i class="fa-solid <?= $comment['icon'] ?>"></i>
                                </span>
                            </div>

                            <div class="flex-grow-1 card shadow-sm border-0">
                                <div class="card-body p-3">

                                    <div class="d-flex align-items-start gap-3">

                                        <!-- Avatar -->
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 36px; height: 36px; font-size: 1rem; font-weight: 500; color: #0d6efd;">
                                            <?= strtoupper(substr($_SESSION['user_name'] ?? '', 0, 1)) . strtoupper(substr(strrchr($_SESSION['user_name'] ?? '', ' '), 1, 1)) ?>
                                        </div>

                                        <!-- Conteúdo -->
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong><?= $_SESSION['user_name'] ?></strong>
                                                <small
                                                    class="text-muted"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                                            </div>

                                            <p class="mb-1 mt-2"><?= $comment['message'] ?></p>

                                            <!-- <small class="text-muted">por <?= $comment['user'] ?></small> -->
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>
            <?php else : ?>
                <div class='alert alert-primary' role='alert'>Nenhuma atividade para exibir!</div>
            <?php endif; ?>
        </div>
    </div>
</div>