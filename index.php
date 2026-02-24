<?php

session_start(); // Inicia a sessão para se trabalhar com vaviáveis de sessão

use Routes\PageController;

//Carregar Composer
require './vendor/autoload.php';

// Instanciar a dependência de variaves de ambiente
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

// Instanciar a classe page controller
$url = new PageController();

// Chamar o método para carregar a página ou a controller
$url->loadPage();