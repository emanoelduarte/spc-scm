<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-five" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <?php if (in_array('Dashboard', $this->data['menuPermission'])) : ?>

                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'dashboard')) ? 'active' : '' ?>"
                    href="<?= $_ENV['URL_ADM'] ?>dashboard">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <?php endif; ?>

                <?php if (in_array('ListUsers', $this->data['menuPermission'])) : ?>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-users')) ? 'active' : '' ?>"
                    href="<?= $_ENV['URL_ADM'] ?>list-users">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                    Usuários
                </a>
                <?php endif; ?>

                <?php if (in_array('ListOrders', $this->data['menuPermission'])) : ?>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-orders')) ? 'active' : '' ?>"
                    href="<?php echo $_ENV['URL_ADM'] ?>list-orders">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                    Pedidos
                </a>
                <?php endif; ?>

                <?php if (in_array('ListPurchasings', $this->data['menuPermission'])) : ?>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-purchasings')) ? 'active' : '' ?>"
                    href="<?php echo $_ENV['URL_ADM'] ?>list-purchasings">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-basket-shopping"></i></div>
                    Compras
                </a>
                <?php endif; ?>

                <?php if (in_array('ListProjects', $this->data['menuPermission'])) : ?>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-projects')) ? 'active' : '' ?>"
                    href="<?php echo $_ENV['URL_ADM'] ?>list-projects">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-diagram-project"></i></div>
                    Obras
                </a>
                <?php endif; ?>

                <!-- Verificar ainda as pemissões para incluir  -->
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-material-stock')) ? 'active' : '' ?>"
                    href="<?php echo $_ENV['URL_ADM'] ?>list-material-stock">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-warehouse"></i></div>
                    Inventário
                </a>
                <!-- finalizar o teste de permissão -->

                <?php if (in_array('ListSuppliers', $this->data['menuPermission'])) : ?>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-suppliers')) ? 'active' : '' ?>"
                    href="<?php echo $_ENV['URL_ADM'] ?>list-suppliers">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-industry"></i></div>
                    Fornecedores
                </a>
                <?php endif; ?>

                <?php if (in_array('ListAccessLevels', $this->data['menuPermission'])) : ?>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-access-levels')) ? 'active' : '' ?>"
                    href="<?php echo $_ENV['URL_ADM'] ?>list-access-levels">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-network-wired"></i></div>
                    Níveis de Acesso
                </a>
                <?php endif; ?>

                <?php if (in_array('ListCategories', $this->data['menuPermission'])) : ?>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-categories')) ? 'active' : '' ?>"
                    href="<?php echo $_ENV['URL_ADM'] ?>list-categories">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-tags"></i></div>
                    Categorias
                </a>
                <?php endif; ?>

                <?php if (in_array('ListPackages', $this->data['menuPermission'])) : ?>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-packages')) ? 'active' : '' ?>"
                    href="<?php echo $_ENV['URL_ADM'] ?>list-packages">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-cubes"></i></div>
                    Pacotes
                </a>
                <?php endif; ?>

                <?php if (in_array('ListGroupsPages', $this->data['menuPermission'])) : ?>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-groups-pages')) ? 'active' : '' ?>"
                    href="<?php echo $_ENV['URL_ADM'] ?>list-groups-pages">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-layer-group"></i></div>
                    Grupos
                </a>
                <?php endif; ?>

                <?php if (in_array('ListPages', $this->data['menuPermission'])) : ?>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-pages')) ? 'active' : '' ?>"
                    href="<?php echo $_ENV['URL_ADM'] ?>list-pages">
                    <div class="sb-nav-link-icon"><i class="fa-regular fa-file"></i> </div>
                    Páginas
                </a>
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