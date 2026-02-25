<nav aria-label="...">
    <ul class="pagination justify-content-center pagination-sm">
        <?php
        // Verificar se existe ultima página e diferente de 1 pois se estou na página um e só existe página um, não tem necessidade de exibir paginação
        if (($this->data['pagination']['last_page'] ?? false) and ($this->data['pagination']['last_page'] != 1)) :

            if ($this->data['pagination']['current_page'] > 1) :
                $beforePage = $this->data['pagination']['current_page'] - 1;
        ?>
                <?php //  Exibir a páginação - Primiera página 
                ?>
                <li class="page-item"><a href="<?= $_ENV['URL_ADM'] . ($this->data['pagination']['url_controller'] ?? '')  ?>/1" class="page-link">Primeira</a></li>

                <?php //  Exibir a páginação - Uma página antes da atual 
                ?>
                <li class="page-item"><a class="page-link" href="<?= $_ENV['URL_ADM'] . ($this->data['pagination']['url_controller'] ?? '') . '/' . $beforePage ?>"><?= $beforePage ?></a></li>

            <?php // Finalização do segundo IF primeira página 
            endif; ?>

            <?php // Exibir a páginação - Página atual 
            ?>
            <li class="page-item active">
                <a class="page-link" href="#" aria-current="page"><?= ($this->data['pagination']['current_page'] ?? false) ?></a>
            </li>

            <?php

            // Abertura do terceiro IF
            // Se ela a página atual for menor que a ultima página apresenta a paginação posterior, caso contrário, não precisa
            if ($this->data['pagination']['current_page'] < $this->data['pagination']['last_page']) :
                $afterPage = $this->data['pagination']['current_page'] + 1;
            ?>

                <?php // Exibir a páginação - Uma página posterior da atual 
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $_ENV['URL_ADM'] . ($this->data['pagination']['url_controller'] ?? '') . "/" . $afterPage ?>"><?= $afterPage ?></a>
                </li>

                <?php // Exibir a páginação - Ultima página 
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $_ENV['URL_ADM'] . ($this->data['pagination']['url_controller'] ?? '') . "/" . ($this->data['pagination']['last_page'] ?? '') ?>">Última</a>
                </li>
    </ul>
</nav>
<?php

// Finalização do terceiro IF
endif;
// Finalização do primeiro IF
endif; ?>