<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-five" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'dashboard')) ? 'active' : '' ?>" href="<?= $_ENV['URL_ADM'] ?>dashboard">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                
                    <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-users')) ? 'active' : '' ?>" href="<?= $_ENV['URL_ADM'] ?>list-users">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                    Usuários
                </a>
                
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-orders')) ? 'active' : '' ?>" href="<?php echo $_ENV['URL_ADM'] ?>list-orders">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                    Pedidos
                </a>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-purchasings')) ? 'active' : '' ?>" href="<?php echo $_ENV['URL_ADM'] ?>list-purchasings">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-basket-shopping"></i></div>
                    Compras
                </a>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-projects')) ? 'active' : '' ?>" href="<?php echo $_ENV['URL_ADM'] ?>list-projects">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-diagram-project"></i></div>
                    Projetos
                </a>
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-suppliers')) ? 'active' : '' ?>" href="<?php echo $_ENV['URL_ADM'] ?>list-suppliers">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-industry"></i></div>
                    Fornecedores
                </a>
                
                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-access-levels')) ? 'active' : '' ?>" href="<?php echo $_ENV['URL_ADM'] ?>list-access-levels">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-network-wired"></i></div>
                    Níveis de Acesso
                </a>

                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-categories')) ? 'active' : '' ?>" href="<?php echo $_ENV['URL_ADM'] ?>list-categories">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-tags"></i></div>
                    Categorias
                </a>

                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-packages')) ? 'active' : '' ?>" href="<?php echo $_ENV['URL_ADM'] ?>list-packages">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-cubes"></i></div>
                    Pacotes
                </a>

                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-groups-pages')) ? 'active' : '' ?>" href="<?php echo $_ENV['URL_ADM'] ?>list-groups-pages">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-layer-group"></i></div>
                    Grupos
                </a>

                <a class="nav-link <?= (($this->data['menu'] ?? false) and ($this->data['menu'] == 'list-pages')) ? 'active' : '' ?>" href="<?php echo $_ENV['URL_ADM'] ?>list-pages">
                    <div class="sb-nav-link-icon"><i class="fa-regular fa-file"></i> </div>
                    Páginas
                </a>

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