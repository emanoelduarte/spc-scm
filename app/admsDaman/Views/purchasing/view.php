<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_delete_purchasing');
$csrf_cancel_token = CSRFHelper::generateCSRFToken('form_cancel_purchasing');

?>
<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Compras</h2>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-purchasings">Compras</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
            </li>
        </ol>
    </div>


    <?php

    $purchasing_status_id = $this->data['purchasing']['purchasing_status_id'];

    // Verificar o Status e aplicar a cor na borda do pedido
    switch ($purchasing_status_id) {
        case 1:
            $highlightClass = "highlight-purchasing-purchased";
            break;
        case 2:
            $highlightClass = "highlight-purchasing-canceled";
            break;
    }
    ?>

    <div class="card mb-4 shadow <?= $highlightClass ?>">
        <div class="card-header d-flex flex-column border-ligth flex-sm-row gap-2">
            <span>Visualizar</span>
            <span class="ms-sm-auto d-sm-flex flex-row">
                <?php if(in_array("ListPurchasings", $this->data['buttonPermissions'])): ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'list-purchasings'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i
                            class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>

                <?php if(in_array("GeneratePdfPurchasing", $this->data['buttonPermissions'])): ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'generate-pdf-purchasing/' . ($this->data['purchasing']['id'] ?? ''); ?>"
                        class="btn btn-primary btn-sm me-1 mb-1" onclick="showLoading()"><i class="fa-solid fa-file-pdf"></i> Gerar PDF</a>
                <?php endif; ?>

                <?php if(in_array("CancelPurchasing", $this->data['buttonPermissions'])): ?>
                    <?php  // Formulário para envio dos dados para deletar Pedido 
                    ?>
                    <form id="formCancel<?= ($this->data['purchasing']['id'] ?? ''); ?>"
                        action="<?= $_ENV['URL_ADM']; ?>cancel-purchasing" method="POST">

                    <input type="hidden" name="csrf_token" value="<?= $csrf_cancel_token; ?>">

                    <input type="hidden" name="id" id="id" value="<?= ($this->data['purchasing']['id'] ?? ''); ?>">

                    <button type="submit" class="btn btn-danger btn-sm me-1 mb-1"
                        onclick="confirmCancel(event, <?= ($this->data['purchasing']['id'] ?? '') ?>)"> <i class="fa-solid fa-xmark"></i> Cancelar Compra</button>

                </form>
                <?php endif; ?>

                </td>
            </span>
        </div>

        <div class="card-body">

            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            // var_dump($this->data['purchasing']);

            if (isset($this->data['purchasing'])):
                extract($this->data['purchasing']);

                // O operador ternário verifica se $created_at não é null antes de chamar a strtotime(). Se $created_at for null, ele retorna uma string vazia.
                $created = ($created_at ? date('d/m/Y H:i:s', strtotime($created_at)) : "");
                $edited = ($updated_at ? date('d/m/Y H:i:s', strtotime($updated_at)) : "");
                $expected_receipt_date = ($expected_receipt_date ? date('d/m/Y H:i:s', strtotime($expected_receipt_date)) : "");
            ?>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nº Compra:</strong> <?= $id ?></p>
                        <p><strong>N° Pedido:</strong> <?= $order_number ?></p>
                        <p><strong>Data:</strong> <?= $created ?></p>
                        <p><strong>Previsão de Entrega:</strong> <?= $expected_receipt_date ?></p>
                        <p><strong>Data da Modificação:</strong> <?= $edited ?></p>
                        <p><strong>Obra:</strong> <?= $project_name ?></p>
                        <p><strong>End. Entrega:</strong> <?= $delivery_address ?></p>
                        <p><strong>Comprador:</strong> <?= $buyer_name ?></p>
                        <p><strong>Fornecedor:</strong> <?= $legal_name ?></p>
                        <p><strong>Tipo de Aquisição:</strong> <?= $acquisition_type ?></p>
                        <p><strong>Forma de pagamento:</strong> <?= $payment_method ?></p>
                        <p><strong>Serviço:</strong> <?= $service ?></p>
                    </div>
                </div>
            <?php else: ?>
                <?php // Caso a Obra não seja encontrada
                ?>
                <div class='alert alert-danger' role='alert'>Pedido não encontrada</div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    if ($this->data['itemsPurchasing'] ?? false) :
    ?>

        <div class="card">
            <div class="card-header">
                Itens da Compra
            </div>

            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Item</th>
                            <th>Descrição</th>
                            <th>Unidade</th>
                            <th>Quantidade</th>
                            <th>Preço Unit.</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        // Iniciar a variável contadora
                        $qtd_items = 0;
                        $sub_tot = 0;
                        foreach ($this->data['itemsPurchasing'] as $item):
                        ?>

                            <tr>

                                <td><?= $qtd_items += 1 ?></td>
                                <td><?= $item['description'] ?></td>
                                <td><?= $item['measurement_unit'] ?></td>
                                <td>
                                    <?php

                                    $purchased_quantity = $item['purchased_quantity'] ?? '0';
                                    echo number_format($purchased_quantity, 2, '.', ',');

                                    ?>
                                </td>
                                <td><?php
                                    $unit_price = $item['unit_price'] ?? '0.00';
                                    echo "R$ " . number_format($unit_price, 2, ',', '.');
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    $tot_item = $item['purchased_quantity'] * $item['unit_price']
                                    ?>
                                    R$ <?= number_format($tot_item, 2, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php
                            $sub_tot += $tot_item;
                            ?>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="5" class="text-end">Sub Total </td>
                            <td>R$ <?= number_format($sub_tot, 2, ',', '.') ?> </td>
                        </tr>
                        <tr>
                            <?php $delivery_value =  $this->data['purchasing']['delivery_value'] ? $this->data['purchasing']['delivery_value'] : '0'; ?>
                            <td colspan="5" class="text-end">Frete </td>
                            <td>R$ <?= number_format($delivery_value, 2, ',', '.'); ?> </td>
                        </tr>
                        <tr>
                            <?php $discount =  $this->data['purchasing']['discount'] ? $this->data['purchasing']['discount'] : '0'; ?>
                            <td colspan="5" class="text-end">Desconto </td>
                            <td>R$ <?= number_format($discount, 2, ',', '.'); ?> </td>
                        </tr>
                        <tr>
                            <?php
                            $tot = $sub_tot + $delivery_value - $discount;
                            ?>
                            <td colspan="5" class="text-end">Total </td>
                            <td class="fw-bold">R$ <?= number_format($tot, 2, ',', '.') ?> </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

    <?php else : ?>
        <div class='alert alert-danger' role='alert'>Compra sem itens para exibir</div>
    <?php
    endif;
    ?>
</div>