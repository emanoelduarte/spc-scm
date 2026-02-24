<?php
// Verificar se existe ultima página e diferente de 1 pois se estou na página um e só existe página um, não tem necessidade de exibir paginação
if (($this->data['pagination']['last_page'] ?? false) and ($this->data['pagination']['last_page'] != 1)) {


    // Se ela a página atual for maior que um, apresenta a paginação anterior, caso contrário, não precisa
    if ($this->data['pagination']['current_page'] > 1) {
        $beforePage = $this->data['pagination']['current_page'] - 1;

        // Exibir a páginação - Primiera página
        echo "<a href='" . $_ENV['URL_ADM'] . ($this->data['pagination']['url_controller'] ?? '') . "/1'> Primeira</a> ";

        // Exibir a páginação - Uma página antes da atual 
        echo "<a href='" . $_ENV['URL_ADM'] . ($this->data['pagination']['url_controller'] ?? '') . "/" . $beforePage . "'> $beforePage </a> ";
    }

    // Exibir a páginação - Pagina atual
    echo "<a href='#'>" . ($this->data['pagination']['current_page'] ?? false) . "</a> ";

    // Se ela a página atual for menor que a ultima página apresenta a paginação posterior, caso contrário, não precisa
    if ($this->data['pagination']['current_page'] < $this->data['pagination']['last_page']) {
        $afterPage = $this->data['pagination']['current_page'] + 1;

        // Exibir a páginação - Uma página posterior da atual 
        echo "<a href='" . $_ENV['URL_ADM'] . ($this->data['pagination']['url_controller'] ?? '') . "/" . $afterPage . "'> $afterPage </a> ";

        // Exibir a páginação - Ultima página
        echo "<a href='" . $_ENV['URL_ADM'] . ($this->data['pagination']['url_controller'] ?? '') . "/" . ($this->data['pagination']['last_page'] ?? '') . "'> Ultima</a> ";
    }
}