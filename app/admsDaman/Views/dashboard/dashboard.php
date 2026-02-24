<?php
echo "Bem vindo <strong>" . ($_SESSION['user_id'] ?? '') . "</strong>!<br>";
echo "Bem vindo <strong>" . ($_SESSION['user_name'] ?? '') . "</strong>!<br>";
echo "Bem vindo <strong>" . ($_SESSION['user_email'] ?? '') . "</strong>!<br>";
echo "Bem vindo <strong>" . ($_SESSION['user_name'] ?? '') . "</strong>!<br>";

echo "<a href='{$_ENV['URL_ADM']}logout'>Sair</a>";
