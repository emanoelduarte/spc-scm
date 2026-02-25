
<!DOCTYPE html>
<html lang="<?= $_ENV['APP_LOCALE']; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="<?= $_ENV['URL_ADM']; ?>/public/admsDaman/image/icon/favicon.ico" type="image/x-icon">

    <title>
        <?php
        echo $_ENV['APP_NAME'] . " - " . ($this->data['title_head'] ?? '');
        ?>
    </title>
</head>

<body>