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