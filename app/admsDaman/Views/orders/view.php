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

    <div class="card mb-4 border-light shadow">
        <div class="card-header d-flex flex-column flex-sm-row gap-2">
            <span>Visualizar</span>
            <span class="ms-sm-auto d-sm-flex flex-row">
                <a href="<?= $_ENV['URL_ADM'] . 'list-orders'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>
                </td>
            </span>
        </div>

        <div class="card-body">

            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            // var_dump($this->data['order']);
            // var_dump($this->data['items']);
            // exit;

            if (isset($this->data['order'])):
                extract($this->data['order']);

                // O operador ternário verifica se $created_at não é null antes de chamar a strtotime(). Se $created_at for null, ele retorna uma string vazia.
                $created = ($created_at ? date('d/m/Y H:i:s', strtotime($created_at)) : "");
                $edited = ($updated_at ? date('d/m/Y H:i:s', strtotime($updated_at)) : "");
            ?>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Pedido:</strong> <?= $id ?></p>
                        <p><strong>Data:</strong> <?= $created ?></p>
                        <p><strong>Status:</strong> <?= $order_status . " | " . $status_date ?></p>
                        <p><strong>Categoria:</strong> <?= $category_name ?></p>
                        <p><strong>Obra:</strong> <?= $project_name ?></p>
                        <p><strong>Endereço da Obra:</strong> <?= $project_adrress ?></p>
                    </div>

                    <div class="col-md-6">
                        <p><strong>Tipo:</strong> <?= $order_name_type ?></p>
                        <p><strong>Prev. Recebimento:</strong> <?= $expected_receipt_date ?></p>
                        <p><strong>Solicitante:</strong> <?= $usr_name ?></p>

                        <?php if ($order_name_type == 'LOCAÇÃO'): ?>

                            <p><strong>Locador:</strong> <?= $rental_contract ?></p>
                            <p><strong>Contrato:</strong> <?= $rental_contract ?></p>
                            <p><strong>Periodo:</strong> <?= $rental_period ?></p>

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
        <div class="card">
            <div class="card-header">
                Itens do Pedido
            </div>

            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Item</th>
                            <th>Descrição</th>
                            <th>Unidade</th>
                            <?php if ($order_name_type == 'LOCAÇÃO'): ?>
                                <th>Locado</th>
                                <th>Devolvido</th>
                            <?php else: ?>
                                <th>Qtd</th>
                                <th>Comprado</th>
                            <?php endif ?>
                            <th>Preço Unit.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Iniciar a variável contadora
                        $qtd_items = 0;
                        foreach ($this->data['items'] as $item): 
                        ?>
                            <tr>
                                <td><?= $qtd_items += 1?></td>
                                <td><?= $item['description'] ?></td>
                                <td><?= $item['unit'] ?></td>

                                <?php if ($order_name_type == 'LOCAÇÃO'): ?>
                                    <td><?= $item['rented_quantity'] ?></td>
                                    <td><?= $item['returned_quantity'] ?></td>
                                <?php else: ?>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>
                                        <?php

                                        $purchased_quantity = $item['purchased_quantity'] ?? '0';
                                        echo number_format($unit_price, 2, '.', ','); 

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
                                        R$ <?= number_format($item['rented_quantity'] * $item['unit_price'], 2, ',', '.') ?>
                                    </td>
                                <?php else: ?>
                                    <td>
                                        R$ <?= number_format($item['purchased_quantity'] * $item['unit_price'], 2, ',', '.') ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else : ?>
        <div class='alert alert-danger' role='alert'>Pedido sem itens para exibir</div>
    <?php endif ?>
</div>