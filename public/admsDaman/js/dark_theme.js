// ============================================================
// DARK THEME
// ============================================================


// ============================================================
// APLICAR TEMA IMEDIATAMENTE
// ============================================================
//
// Esta parte NÃO deve esperar o DOMContentLoaded.
//
// Ela precisa executar antes que o navegador renderize
// o conteúdo da página.
//
(function () {

    const savedTheme =
        localStorage.getItem('theme') || 'light';


    document.documentElement.classList.toggle(
        'dark-theme',
        savedTheme === 'dark'
    );

})();


// ============================================================
// BOTÃO DE ALTERAÇÃO DO TEMA
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    const themeToggle =
        document.getElementById('theme-toggle');


    if (!themeToggle) {
        return;
    }


    // --------------------------------------------------------
    // Atualizar descrição do botão
    // --------------------------------------------------------

    function updateThemeButton() {

        const isDark =
            document.documentElement.classList.contains(
                'dark-theme'
            );


        themeToggle.title =
            isDark
                ? 'Ativar tema claro'
                : 'Ativar tema escuro';


        themeToggle.setAttribute(
            'aria-label',
            isDark
                ? 'Ativar tema claro'
                : 'Ativar tema escuro'
        );
    }


    // Estado inicial
    updateThemeButton();


    // --------------------------------------------------------
    // ALTERAR TEMA
    // --------------------------------------------------------

    themeToggle.addEventListener(
        'click',
        function () {

            const isDark =
                document.documentElement.classList.contains(
                    'dark-theme'
                );


            const newTheme =
                isDark
                    ? 'light'
                    : 'dark';


            // Aplicar
            document.documentElement.classList.toggle(
                'dark-theme',
                newTheme === 'dark'
            );


            // Salvar preferência
            localStorage.setItem(
                'theme',
                newTheme
            );


            // Atualizar tooltip
            updateThemeButton();
        }
    );

});