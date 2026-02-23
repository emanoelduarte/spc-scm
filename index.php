<?php

use Routes\PageController;

//Carregar Composer
require './vendor/autoload.php';

// Instanciar a classe page controller
$url = new PageController();

// Chamar o método para carregar a página ou a controller
$url->loadPage();