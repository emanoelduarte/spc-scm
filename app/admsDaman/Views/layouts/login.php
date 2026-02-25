<!DOCTYPE html>
<html lang="<?= $_ENV['APP_LOCALE']; ?>">

    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="<?= $_ENV['URL_ADM']; ?>public/admsDaman/image/icon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="<?= $_ENV['URL_ADM']; ?>public/admsDaman/css/sbadmin.css">

    <link rel="stylesheet" href="<?= $_ENV['URL_ADM']; ?>public/admsDaman/css/bootstrap.min.css">

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <title>
        <?= $_ENV['APP_NAME'] . " - " . ($this->data['title_head'] ?? ''); ?>
    </title>
</head>

<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <?php

                            // Inclui o conteúdo principal da página, que é especificado pela propriedade $this->view. Este arquivo é dinâmico e pode variar conforme a lógica do controlador ou o contexto da página.
                            include $this->view;

                            ?>
                    </div>
                </div>
            </main>
        </div>
    </div>


</body>

<script src="<?php echo $_ENV['URL_ADM']; ?>public/admsDaman/js/sbadmin.js"></script>
<script src="<?php echo $_ENV['URL_ADM']; ?>public/admsDaman/js/bootstrap.bundle.min"></script>

</html>