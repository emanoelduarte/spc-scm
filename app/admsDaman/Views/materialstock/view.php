<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_stock_movement');
$csrf_token_delete = CSRFHelper::generateCSRFToken('form_delete_item_stock');
?>
<div class="container-fluid px-4">

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">
        <h2 class="mt-3">Visualizar Material</h2>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>

            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-material-stock">Estoque</a>
            </li>

            <li class="breadcrumb-item active">
                <?= htmlspecialchars($this->data['material']['name']) ?>
            </li>
            </li>
        </ol>
    </div>

    <a href="<?= $_ENV['URL_ADM'] ?>list-material-stock" class="btn btn-secondary btn-sm mb-2">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>

    <div class="card mb-4 border-light shadow">
        <div class="card-header d-flex flex-column flex-sm-row gap-2">
            <span>Visualizar</span>
            <span class="ms-sm-auto d-sm-flex flex-row">

                <?php if (in_array("CreateStockMovement", $this->data['buttonPermissions'])): ?>

                    <button class="btn btn-success btn-sm me-1 mb-1" data-bs-toggle="modal"
                        data-project="<?= $this->data['material']['adms_daman_project_id']; ?>"
                        data-bs-target="#modalMovement" data-id="<?= $this->data['material']['id']; ?>"
                        data-name="<?= htmlspecialchars($this->data['material']['name']); ?>" data-type="input">
                        Entrada
                    </button>

                    <button class="btn btn-danger btn-sm me-1 mb-1" data-bs-toggle="modal" data-bs-target="#modalMovement"
                        data-id="<?= $this->data['material']['id']; ?>"
                        data-category="<?= $this->data['material']['adms_daman_category_id']; ?>"
                        data-name="<?= htmlspecialchars($this->data['material']['name']); ?>" data-type="output">
                        Saída
                    </button>

                <?php endif; ?>

                <?php if (in_array("UpdateMaterialStock", $this->data['buttonPermissions'])): ?>
                    <a href="<?= $_ENV['URL_ADM'] . 'update-material-stock/' . $this->data['material']['id']; ?>"
                        class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i>
                        Editar</a>
                <?php endif; ?>
            </span>
        </div>

        <div class="card-body">

            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php'; ?>

            <p class="text-uppercase text-muted small fw-semibold mb-3" style="letter-spacing: .05em;">
                Dados do item
            </p>

            <div class="row g-3">

                <div class="col-lg-4 col-md-6">
                    <small class="text-muted d-block">Nome</small>
                    <strong><?= htmlspecialchars($this->data['material']['name']) ?></strong>
                </div>

                <div class="col-lg-4 col-md-6">
                    <small class="text-muted d-block">Obra</small>
                    <strong><?= htmlspecialchars($this->data['material']['project_name']) ?></strong>
                </div>

                <div class="col-lg-2 col-md-6">
                    <small class="text-muted d-block">Unidade</small>
                    <strong><?= htmlspecialchars($this->data['material']['measurement_unit']) ?></strong>
                </div>

                <div class="col-lg-1 col-md-6">
                    <small class="text-muted d-block">Qtd. mínima</small>
                    <strong><?= number_format($this->data['material']['min_quantity'], 2, ',', '.') ?></strong>
                </div>

                <div class="col-lg-1 col-md-6">
                    <small class="text-muted d-block">Qtd. atual</small>
                    <?php
                    $current = (float) $this->data['material']['current_quantity'];
                    $min     = (float) $this->data['material']['min_quantity'];
                    $badge   = $current <= 0 ? 'danger' : ($current <= $min ? 'warning' : 'success');
                    ?>
                    <span class="badge bg-<?= $badge ?>">
                        <?= number_format($current, 2, ',', '.') ?>
                    </span>
                </div>
                <div class="col-12">
                    <small class="text-muted d-block">Observação</small>
                    <?= htmlspecialchars($this->data['material']['obs'] ?? '') ?>
                </div>
            </div>
        </div>
    </div>

    <?php // Motal de abertura do formulário de entrada e saída
    ?>

    <div class="modal fade" id="modalMovement" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Registrar Movimentação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="<?= $_ENV['URL_ADM'] ?>create-stock-movement" method="POST">

                    <div class="modal-body">

                        <input type="hidden" name="redirect_to"
                            value="<?= $_ENV['URL_ADM'] . 'view-material-stock/' . $this->data['material']['id'] ?>">

                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                        <input type="hidden" name="stock_id" id="stockId">
                        <input type="hidden" name="type" id="movementType">
                        <input type="hidden" name="item_name" id="itemNameHidden">
                        <input type="hidden" name="adms_daman_category_id" id="categoryIdHidden">

                        <p class="text-muted mb-3">Item: <strong id="itemName"></strong></p>

                        <div class="mb-3">
                            <label class="form-label">Quantidade</label>
                            <input type="number" name="quantity" class="form-control" min="0.01" step="0.01" required>
                        </div>

                        <div class="mb-3" id="projectField">
                            <label class="form-label">Obra de destino</label>
                            <select name="adms_daman_project_id" class="form-select" required>
                                <option value="">Selecione</option>
                                <?php foreach ($this->data['getAllProjectsSelectActive'] as $project) : ?>
                                    <option value="<?= $project['id'] ?>">
                                        <?= htmlspecialchars($project['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Obra fixa para entrada -->
                        <input type="hidden" name="adms_daman_project_id" id="projectFixed">

                        <div class="mb-3 d-none" id="reasonField">
                            <label class="form-label">Motivo da saída</label>
                            <select name="reason" class="form-select">
                                <option value="">Selecione</option>
                                <option value="consumption">Consumo na obra</option>
                                <option value="transfer">Transferência para outra obra</option>
                                <option value="return">Devolução ao fornecedor</option>
                                <option value="discard">Descarte</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observação</label>
                            <textarea name="observation" class="form-control" rows="2"
                                placeholder="Opcional"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="modalBtn">Confirmar</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Visualização Histórico de movimentações -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <p class="text-uppercase text-muted small fw-semibold mb-3" style="letter-spacing: .05em;">
                        Histórico de movimentações
                    </p>

                    <?php if (!empty($this->data['movements'])) : ?>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Data</th>
                                        <th>Tipo</th>
                                        <th>Motivo</th>
                                        <th>Quantidade</th>
                                        <th>Obra</th>
                                        <th>Usuário</th>
                                        <th>Observação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($this->data['movements'] as $movement) : ?>
                                        <tr>

                                            <td>
                                                <small><?= date('d/m/Y H:i', strtotime($movement['created_at'])) ?></small>
                                            </td>

                                            <style>
                                                .success_personality {
                                                    background-color: #18571822;
                                                    border-radius: 5px;
                                                    border: 1px solid #018401;
                                                    color: #018401;
                                                }
                                                .danger_personality {
                                                    background-color: #d9534f22;
                                                    border-radius: 5px;
                                                    border: 1px solid #d9534f;
                                                    color: #d9534f;
                                                }
                                            </style>

                                            <td>
                                                <?php
                                                $typeLabel = $movement['type'] === 'input' ? 'Entrada' : 'Saída';
                                                $typeBadge = $movement['type'] === 'input' ? 'success_personality' : 'danger_personality';
                                                ?>
                                                <span class="badge <?= $typeBadge ?>">
                                                    <?= $typeLabel ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php
                                                $reasons = [
                                                    'consumption' => 'Consumo',
                                                    'transfer'    => 'Transferência',
                                                    'return'      => 'Devolução',
                                                    'discard'     => 'Descarte',
                                                ];
                                                echo $reasons[$movement['reason']] ?? '—';
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $typeBadge ?>">
                                                    <?= number_format((float) $movement['quantity'], 2, ',', '.') ?>

                                                    <?= htmlspecialchars($this->data['material']['measurement_unit']) ?>
                                                </span>
                                            </td>

                                            <td><?= htmlspecialchars($movement['project_name']) ?></td>

                                            <td><?= htmlspecialchars($movement['user_name'] ?? '—') ?></td>

                                            <td>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($movement['observation'] ?? '—') ?>
                                                </small>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else : ?>

                        <div class="text-center text-muted py-4">
                            <i class="fa-solid fa-clock-rotate-left fa-2x mb-2"></i>
                            <p class="mb-0">Nenhuma movimentação registrada.</p>
                        </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>