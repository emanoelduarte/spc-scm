<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_aprovation_quote');
$csrf_token_reject = CSRFHelper::generateCSRFToken('form_reject_quote');
?>
<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Compras Pendentes de Aprovação</h2>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-purchasing-quotes">Compras
                    Pendentes</a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 shadow">
        <div class="card-header d-flex flex-column border-ligth flex-sm-row gap-2 fw-bold"
            <?php if ($this->data['purchasingQuotes']['status'] == 'rejected') : ?>
            style="background-color: #A32D2D50; color: #A32D2D">
            <?php endif; ?>
            <span>Visualizar</span>
            <span class="ms-sm-auto d-sm-flex flex-row">

                <?php if ($this->data['purchasingQuotes']['status'] == 'rejected') : ?>
                <div class="me-3 mb-1 fs-5" style="color: #A32D2D">
                    <i class="fa-solid fa-xmark"></i>
                </div>
                <?php endif; ?>


                <?php if (in_array("ListPurchasingQuotes", $this->data['buttonPermissions'])): ?>
                <a href="<?= $_ENV['URL_ADM'] . 'list-purchasing-quotes'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i
                        class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>

                <?php if (in_array("ApprovePurchasingQuote", $this->data['buttonPermissions'])): ?>

                <?php if (($this->data['purchasingQuotes']['status'] ?? '') === 'pending') : ?>

                <!-- Botão Aprovar -->
                <form id="formAprovation<?= ($this->data['purchasingQuotes']['id'] ?? ''); ?>"
                    action="<?= $_ENV['URL_ADM'] ?>approve-purchasing-quote/<?= $this->data['purchasingQuotes']['id'] ?>"
                    method="POST" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <!-- <input type="hidden" name="newStatus" value="approved"> -->
                    <input type="hidden" name="approveUser" value="<?= $_SESSION['user_id'] ?>">
                    <button type="submit" class="btn btn-success btn-sm me-1 mb-1"
                        onclick="confirmAprovation(event, <?= ($this->data['purchasingQuotes']['id'] ?? '') ?>)">
                        <i class="fa-solid fa-check"></i> Aprovar
                    </button>
                </form>

                <!-- Botão Rejeitar -->
                <button class="btn btn-danger btn-sm me-1 mb-1" data-bs-toggle="modal" data-bs-target="#modalReject">
                    <i class="fa-solid fa-xmark"></i> Rejeitar
                </button>

                <?php endif; ?>
                <?php endif; ?>

                </td>
            </span>
        </div>

        <div class="card-body">

            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            // var_dump($this->data['purchasing']);

            if (isset($this->data['purchasingQuotes'])):
                extract($this->data['purchasingQuotes']);

                // O operador ternário verifica se $created_at não é null antes de chamar a strtotime(). Se $created_at for null, ele retorna uma string vazia.
                $created = ($created_at ? date('d/m/Y H:i:s', strtotime($created_at)) : "");
                $edited = ($updated_at ? date('d/m/Y H:i:s', strtotime($updated_at)) : "");
                $expected_receipt_date = ($expected_receipt_date ? date('d/m/Y H:i:s', strtotime($expected_receipt_date)) : "");
            ?>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nº Compra:</strong> <?= $id ?></p>
                    <p><strong>N° Pedido:</strong> <?= $adms_daman_order_id ?></p>
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
    if ($this->data['itemsPurchasingQuotes'] ?? false) :
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
                        foreach ($this->data['itemsPurchasingQuotes'] as $item):
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
                        <?php $delivery_value =  $this->data['purchasingQuotes']['delivery_value'] ? $this->data['purchasingQuotes']['delivery_value'] : '0'; ?>
                        <td colspan="5" class="text-end">Frete </td>
                        <td>R$ <?= number_format($delivery_value, 2, ',', '.'); ?> </td>
                    </tr>
                    <tr>
                        <?php $discount =  $this->data['purchasingQuotes']['discount'] ? $this->data['purchasingQuotes']['discount'] : '0'; ?>
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

<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Rejeitar Cotação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= $_ENV['URL_ADM'] ?>reject-purchasing-quote/<?= $this->data['purchasingQuotes']['id'] ?>"
                method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token_reject ?>">
                <input type="hidden" name="approveUser" value="<?= $_SESSION['user_id'] ?>">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Motivo da rejeição <span class="text-danger">*</span></label>
                        <textarea name="new_rejection_reason" class="form-control" rows="4"
                            placeholder="Descreva o motivo da rejeição..." required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Rejeição</button>
                </div>
            </form>

        </div>
    </div>
</div>