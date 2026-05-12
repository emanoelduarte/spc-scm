<!DOCTYPE html>
<html lang="<?= $_ENV['APP_LOCALE']; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="<?= $_ENV['URL_ADM']; ?>public/admsDaman/image/icon/favicon.ico"
        type="image/x-icon">

    <link rel="stylesheet" href="<?= $_ENV['URL_ADM']; ?>public/admsDaman/css/sbadmin.css">

    <link rel="stylesheet" href="<?= $_ENV['URL_ADM']; ?>public/admsDaman/css/styles_admin.css">

    <link rel="stylesheet" href="<?= $_ENV['URL_ADM']; ?>public/admsDaman/css/bootstrap.min.css">

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <!-- CDN - CALENDÁRIO-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Tradução para PT -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

    <title>
        <?php
        echo $_ENV['APP_NAME'] . " - " . ($this->data['title_head'] ?? '');
        ?>
    </title>
</head>

<body class="sb-nav-fixed">

    <?php include 'app/admsDaman/Views/partials/loadingOverlay.php' ?>

    <?php include 'app/admsDaman/Views/partials/navbar.php' ?>

    <div id="layoutSidenav">
        <?php include 'app/admsDaman/Views/partials/menu.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <?php
                // Inclui o conteúdo principal da página, que é especificado pela propriedade $this->view. Este arquivo é dinâmico e pode variar conforme a lógica do controlador ou o contexto da página.
                include $this->view;

                ?>
            </main>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; <?= $_ENV['APP_NAME'] . " " . date("y"); ?></div>
                        <div>
                            <a href="#" class="text-decoration-none">Política de Privacidade</a>
                            &middot;
                            <a href="#" class="text-decoration-none">Termos de Uso</a>
                        </div>
                    </div>
                </div>
            </footer>

        </div>
    </div>

    <script src="<?= $_ENV['URL_ADM']; ?>public/admsDaman/js/sbadmin.js"></script>
    <script src="<?= $_ENV['URL_ADM']; ?>public/admsDaman/js/bootstrap.bundle.min.js"></script>

    <?php // Inclui o sweetalert2 para botões de alerda de uma cdn 
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php // Inclui o arquivo script_admin que tratará diretamente a função de confirmar ações 
    ?>
    <script src="<?php echo $_ENV['URL_ADM']; ?>public/admsDaman/js/script_admin.js"></script>

</body>

</html>