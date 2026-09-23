<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-five" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <?php
                $menuPermission = $this->data['menuPermission'] ?? [];
                ?>

                <?php if (in_array('Dashboard', $menuPermission)) : ?>

                    <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'dashboard')) ? 'active' : '' ?>"
                        href="<?= $_ENV['URL_ADM'] ?>dashboard">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Dashboard
                    </a>
                <?php endif; ?>

                <?php if (in_array('ListUsers', $menuPermission)) : ?>
                    <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-users')) ? 'active' : '' ?>"
                        href="<?= $_ENV['URL_ADM'] ?>list-users">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                        Usuários
                    </a>
                <?php endif; ?>

                <?php if (in_array('ListOrders', $menuPermission)) : ?>
                    <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-orders')) ? 'active' : '' ?>"
                        href="<?php echo $_ENV['URL_ADM'] ?>list-orders">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                        Pedidos
                    </a>
                <?php endif; ?>

                <?php if (in_array('ListPurchasings', $menuPermission)) : ?>
                    <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-purchasings')) ? 'active' : '' ?>"
                        href="<?php echo $_ENV['URL_ADM'] ?>list-purchasings">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-basket-shopping"></i></div>
                        Compras
                    </a>
                <?php endif; ?>

                <?php

                /*
                * Verificar se alguma página financeira
                * está ativa para manter o menu aberto.
                */
                $financeMenuActive = in_array(
                    $this->data['menu'] ?? '',
                    [
                        'list-purchase-documents',
                        'list-direct-expenses',
                        'create-direct-expense',
                        'list-financial-disbursements',
                    ]
                );


                /*
                * Exibir o grupo financeiro somente se o usuário
                * possuir acesso a pelo menos uma página do módulo.
                */
                $showFinanceGroup = array_intersect(
                    [
                        'ListPurchaseDocuments',
                        'ListDirectExpenses',
                        'CreateDirectExpense',
                        'ListFinancialDisbursements',
                    ],
                    $menuPermission
                );

                ?>


                <?php if (!empty($showFinanceGroup)) : ?>

                    <!-- MENU PAI -->
                    <a
                        class="nav-link collapsed <?= $financeMenuActive ? 'active' : ''; ?>"
                        href="#collapseFinanceiro"
                        data-bs-toggle="collapse"
                        aria-expanded="<?= $financeMenuActive ? 'true' : 'false'; ?>"
                        aria-controls="collapseFinanceiro">

                        <div class="sb-nav-link-icon">

                            <i class="fa-solid fa-hand-holding-dollar"></i>

                        </div>

                        Financeiro

                        <div class="sb-sidenav-collapse-arrow">

                            <i class="fas fa-angle-down"></i>

                        </div>

                    </a>


                    <!-- SUBITENS -->
                    <div
                        class="collapse <?= $financeMenuActive ? 'show' : ''; ?>"
                        id="collapseFinanceiro">

                        <nav class="sb-sidenav-menu-nested nav">


                            <?php if (
                                in_array(
                                    'ListPurchaseDocuments',
                                    $menuPermission
                                )
                            ) : ?>

                                <a
                                    class="nav-link <?= (
                                                        ($this->data['menu'] ?? '')
                                                        === 'list-purchase-documents'
                                                    )
                                                        ? 'active'
                                                        : ''; ?>"
                                    href="<?= $_ENV['URL_ADM']; ?>list-purchase-documents">

                                    <div class="sb-nav-link-icon">

                                        <i class="fa-solid fa-file-invoice-dollar"></i>

                                    </div>

                                    Compras / Contas a Pagar

                                </a>

                            <?php endif; ?>


                            <?php if (
                                in_array(
                                    'ListDirectExpenses',
                                    $menuPermission
                                )
                            ) : ?>

                                <a
                                    class="nav-link <?= (
                                                        ($this->data['menu'] ?? '')
                                                        === 'list-direct-expenses'
                                                    )
                                                        ? 'active'
                                                        : ''; ?>"
                                    href="<?= $_ENV['URL_ADM']; ?>list-direct-expenses">

                                    <div class="sb-nav-link-icon">

                                        <i class="fa-solid fa-money-bill-transfer"></i>

                                    </div>

                                    Despesas Diretas

                                </a>

                            <?php endif; ?>


                            <?php if (in_array('ListFinancialDisbursements', $menuPermission)) : ?>

                                <a
                                    class="nav-link <?= (
                                                        ($this->data['menu'] ?? '')
                                                        === 'list-financial-disbursements'
                                                    )
                                                        ? 'active'
                                                        : ''; ?>"
                                    href="<?= $_ENV['URL_ADM']; ?>list-financial-disbursements">

                                    <div class="sb-nav-link-icon">
                                        <i class="fa-solid fa-chart-line"></i>
                                    </div>

                                    Desembolso de Obras

                                </a>

                            <?php endif; ?>


                        </nav>

                    </div>

                <?php endif; ?>

                <?php if (in_array('ListNfes', $menuPermission)) : ?>
                    <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-nfes')) ? 'active' : '' ?>"
                        href="<?php echo $_ENV['URL_ADM'] ?>list-nfes">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-receipt"></i></div>
                        NF-e
                    </a>
                <?php endif; ?>

                <?php if (in_array('ListProjects', $menuPermission)) : ?>
                    <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-projects')) ? 'active' : '' ?>"
                        href="<?php echo $_ENV['URL_ADM'] ?>list-projects">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-helmet-safety"></i></div>
                        Obras
                    </a>
                <?php endif; ?>

                <?php if (in_array('ListMaterialStock', $menuPermission)) : ?>
                    <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-material-stock')) ? 'active' : '' ?>"
                        href="<?php echo $_ENV['URL_ADM'] ?>list-material-stock">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-warehouse"></i></div>
                        Inventário
                    </a>
                <?php endif; ?>

                <?php if (in_array('ListSuppliers', $menuPermission)) : ?>
                    <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-suppliers')) ? 'active' : '' ?>"
                        href="<?php echo $_ENV['URL_ADM'] ?>list-suppliers">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-industry"></i></div>
                        Fornecedores
                    </a>
                <?php endif; ?>

                <?php if (in_array('ListBudgets', $menuPermission)) : ?>
                    <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-budgets')) ? 'active' : '' ?>"
                        href="<?php echo $_ENV['URL_ADM'] ?>list-budgets">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-sack-dollar text-muted"></i></div>
                        Orçamentos
                    </a>
                <?php endif; ?>

                <?php
                // Verifica se algum item do grupo está ativo para manter o menu aberto
                $configMenuActive = in_array($this->data['menu'] ?? '', [
                    'list-access-levels',
                    'list-categories',
                    'list-packages',
                    'list-groups-pages',
                    'list-pages'
                ]);

                // Verifica se o usuário tem permissão em pelo menos um item do grupo
                $showConfigGroup = array_intersect(
                    ['ListAccessLevels', 'ListCategories', 'ListPackages', 'ListGroupsPages', 'ListPages'],
                    $menuPermission
                );
                ?>

                <?php if (!empty($showConfigGroup)) : ?>
                    <?php // Botão do grupo pai 
                    ?>
                    <a class="nav-link collapsed <?= $configMenuActive ? 'active' : '' ?>"
                        href="#collapseConfiguracoes"
                        data-bs-toggle="collapse"
                        aria-expanded="<?= $configMenuActive ? 'true' : 'false' ?>"
                        aria-controls="collapseConfiguracoes">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-gear"></i></div>
                        Configurações
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>

                    <?php // Sub-itens 
                    ?>
                    <div class="collapse <?= $configMenuActive ? 'show' : '' ?>" id="collapseConfiguracoes">
                        <nav class="sb-sidenav-menu-nested nav">

                            <?php if (in_array('ListAccessLevels', $menuPermission)) : ?>
                                <a class="nav-link <?= (($this->data['menu'] ?? false) && $this->data['menu'] == 'list-access-levels') ? 'active' : '' ?>"
                                    href="<?= $_ENV['URL_ADM'] ?>list-access-levels">
                                    <div class="sb-nav-link-icon"><i class="fa-solid fa-network-wired"></i></div>
                                    Níveis de Acesso
                                </a>
                            <?php endif; ?>

                            <?php if (in_array('ListCategories', $menuPermission)) : ?>
                                <a class="nav-link <?= (($this->data['menu'] ?? false) && $this->data['menu'] == 'list-categories') ? 'active' : '' ?>"
                                    href="<?= $_ENV['URL_ADM'] ?>list-categories">
                                    <div class="sb-nav-link-icon"><i class="fa-solid fa-tags"></i></div>
                                    Categorias
                                </a>
                            <?php endif; ?>

                            <?php if (in_array('ListPackages', $menuPermission)) : ?>
                                <a class="nav-link <?= (($this->data['menu'] ?? false) && $this->data['menu'] == 'list-packages') ? 'active' : '' ?>"
                                    href="<?= $_ENV['URL_ADM'] ?>list-packages">
                                    <div class="sb-nav-link-icon"><i class="fa-solid fa-cubes"></i></div>
                                    Pacotes
                                </a>
                            <?php endif; ?>

                            <?php if (in_array('ListGroupsPages', $menuPermission)) : ?>
                                <a class="nav-link <?= (($this->data['menu'] ?? false) && $this->data['menu'] == 'list-groups-pages') ? 'active' : '' ?>"
                                    href="<?= $_ENV['URL_ADM'] ?>list-groups-pages">
                                    <div class="sb-nav-link-icon"><i class="fa-solid fa-layer-group"></i></div>
                                    Grupos
                                </a>
                            <?php endif; ?>

                            <?php if (in_array('ListPages', $menuPermission)) : ?>
                                <a class="nav-link <?= (($this->data['menu'] ?? false) && $this->data['menu'] == 'list-pages') ? 'active' : '' ?>"
                                    href="<?= $_ENV['URL_ADM'] ?>list-pages">
                                    <div class="sb-nav-link-icon"><i class="fa-regular fa-file"></i></div>
                                    Páginas
                                </a>
                            <?php endif; ?>

                        </nav>
                    </div>
                <?php endif; ?>

                <a class="nav-link" href="<?= $_ENV['URL_ADM'] ?>logout">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                    Sair
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logado:</div>
            <strong><?= ($_SESSION['user_name']) ?? ''; ?></strong>
        </div>
    </nav>
</div>