<?php

session_start(); // Inicia a sessão para se trabalhar com vaviáveis de sessão

// ini_set('display_errors', 1);
// ini_set('output_buffering', 'Off');
// error_reporting(E_ALL);

use Routes\PageController;

//Carregar Composer
require './vendor/autoload.php';

// Instanciar a dependência de variaves de ambiente
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

// Definir a timezone
date_default_timezone_set($_ENV['APP_TIMEZONE']);

// Apresentar ou ocutar os erros
if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    // Ambiente de desenvolvimento - deve mostrar os erros
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL); // Exibe todos os erros, incluindo E_DEPRECATED e E_WARNING
} else {
    // Ambiente de produção - não deve mostrar erros
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0); // Nenhum erro é exibido, mas eles ainda são registrados no log
}

// Instanciar a classe page controller
$url = new PageController();

// Chamar o método para carregar a página ou a controller
$url->loadPage();