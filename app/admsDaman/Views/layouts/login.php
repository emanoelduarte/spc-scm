<?php
// Inclui o cabeçalho da página. Este arquivo contém elementos comuns ao topo de todas as páginas, como links para CSS e scripts JavaScript.
include 'app/admsDaman/Views/partials/head.php';

// Inclui o conteúdo principal da página, que é especificado pela propriedade $this->view. Este arquivo é dinâmico e pode variar conforme a lógica do controlador ou o contexto da página.
include $this->view;


// Inclui o rodapé da página. Este arquivo contém elementos comuns ao final de todas as páginas, como scripts de fechamento e informações de contato.
include 'app/admsDaman/Views/partials/footer.php';