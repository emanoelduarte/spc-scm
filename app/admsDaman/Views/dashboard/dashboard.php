<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Dashboard</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item active">
                Dashboard
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Dashboard</span>
        </div>

        <div class="card-body">

            <?php include './app/admsDaman/Views/partials/alerts.php'; ?>

            <div class="row">
                <div class="col-xl-12 col-md-12">
                    <div class="card mb-4">
                        <div
                            class="card-header fw-semibold bg-white justify-content-between d-flex align-items-center py-3">
                            <div>
                                <i class="fas fa-chart-line me-2 text-primary"></i>
                                Status de Pedidos
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">

                                <?php if ($this->data['statusCount'] ?? false) : ?>
                                    <?php foreach ($this->data['statusCount'] as $status) : ?>

                                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                            <div
                                                style="background: <?= $status['color'] ?>22; border-radius: 8px; padding: 14px;">
                                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                                    <i class="fa-solid <?= $status['icon'] ?>"
                                                        style="color: <?= $status['color'] ?>; font-size: 16px;"></i>
                                                    <span
                                                        style="font-size: 12px; font-weight: 500; color: <?= $status['color'] ?>;">
                                                        <?= htmlspecialchars($status['name']) ?>
                                                    </span>
                                                </div>
                                                <strong style="font-size: 28px; color: <?= $status['color'] ?>;">
                                                    <?= $status['total'] ?>
                                                </strong>
                                            </div>
                                        </div>

                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class='alert alert-danger' role='alert'>Ops, parece que você não tem nada para ser
                                        exibido aqui!</div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (in_array("BuyerContent", $this->data['buttonPermissions'])): // Verifica se é comprador ou tem permissão de acessar
            ?>
                <div class="row">

                    <div class="col-xl-6 col-md-12">
                        <div class="card mb-4 shadow-sm border-0">
                            <div
                                class="card-header fw-semibold bg-white justify-content-between d-flex align-items-center py-3">
                                <div>
                                    <i class="fas fa-building me-2 text-primary"></i>
                                    Pedidos em análise por obra
                                </div>
                            </div>
                            <div class="card-body p-0">

                                <?php if (!empty($this->data['ordersAnalysisByProject'])) : ?>

                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($this->data['ordersAnalysisByProject'] as $index => $project) : ?>

                                            <li class="list-group-item d-flex align-items-center justify-content-between px-3 py-2">

                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-muted"
                                                        style="font-size: 12px; min-width: 18px;"><?= $index + 1 ?>°</span>
                                                    <i class="fas fa-hard-hat text-warning"></i>
                                                    <span><?= htmlspecialchars($project['project_name']) ?></span>
                                                </div>

                                                <span class="badge bg-warning text-dark"><?= $project['total'] ?>
                                                    pedido<?= $project['total'] > 1 ? 's' : '' ?></span>

                                            </li>

                                        <?php endforeach; ?>
                                    </ul>

                                <?php else : ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                        <p class="mb-0">Nenhum pedido em análise</p>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-12">
                        <div class="card mb-4 shadow-sm border-0">

                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <div class="fw-semibold">
                                    <i class="fas fa-cart-shopping text-primary me-2"></i>
                                    Últimas compras da semana
                                </div>

                                <span class="badge bg-primary rounded-pill">
                                    <?= count($this->data['getLastPurchasingsWeek']) ?>
                                </span>
                            </div>

                            <div class="card-body p-2">

                                <?php if (!empty($this->data['getLastPurchasingsWeek'])) : ?>

                                    <div class="d-flex flex-column gap-2">

                                        <div class="text-end">

                                            <?php $totPurchasedWeek = $this->data['getTotalPurchasingsWeek']; ?>

                                            <span class="badge text- text-dark rounded-pill px-3 py-3 fs-6"
                                                style="background-color: #1D9E7544;">
                                                <div class="fw-bold text-dark"> Total da Semana:
                                                    <?= htmlspecialchars('R$ ' . number_format($totPurchasedWeek, 2, ',', '.')) ?>
                                                </div>
                                            </span>

                                        </div>

                                        <?php foreach ($this->data['getLastPurchasingsWeek'] as $index => $purchased) : ?>

                                            <div class="border rounded-3 p-3 bg-light-subtle">

                                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

                                                    <!-- ESQUERDA -->
                                                    <div class="d-flex align-items-start gap-3">

                                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width:45px;height:45px;">

                                                            <i class="fas fa-cart-shopping"></i>
                                                        </div>

                                                        <div>

                                                            <div class="fw-semibold text-dark">
                                                                <?= htmlspecialchars($purchased['project_name']) ?>
                                                            </div>

                                                            <div class="text-muted small">
                                                                <?= htmlspecialchars($purchased['service']) ?>
                                                            </div>

                                                            <div class="small text-secondary mt-1">
                                                                <i class="far fa-clock me-1"></i>
                                                                <?= htmlspecialchars($purchased['created_at']) ?>
                                                            </div>

                                                        </div>

                                                    </div>

                                                    <!-- DIREITA -->
                                                    <div class="text-end">

                                                        <span class="badge text- text-dark rounded-pill px-3 py-2"
                                                            style="background-color: #1D9E7522;">
                                                            <div class="fw-semibold text-dark">
                                                                <?= htmlspecialchars('R$ ' . number_format($purchased['total_final'], 2, ',', '.')) ?>
                                                            </div>
                                                        </span>

                                                        <div class="small text-muted mt-2">
                                                            #<?= $index + 1 ?>
                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        <?php endforeach; ?>

                                    </div>

                                <?php else : ?>

                                    <div class="text-center text-muted py-5">

                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                                        <h6 class="fw-semibold">
                                            Nenhuma compra encontrada
                                        </h6>

                                        <p class="mb-0 small">
                                            Não houve compras registradas nesta semana.
                                        </p>

                                    </div>

                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>