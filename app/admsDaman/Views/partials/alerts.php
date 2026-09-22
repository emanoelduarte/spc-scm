<?php
// Coletar todas as mensagens
$toasts = [];

if (isset($_SESSION['success'])) {
    $toasts[] = [
        'type' => 'success',
        'message' => $_SESSION['success']
    ];
}

if (isset($_SESSION['error'])) {
    $toasts[] = [
        'type' => 'danger',
        'message' => $_SESSION['error']
    ];
}

if (isset($_SESSION['warning'])) {
    $toasts[] = [
        'type' => 'warning',
        'message' => $_SESSION['warning']
    ];
}

if (isset($_SESSION['info'])) {
    $toasts[] = [
        'type' => 'info',
        'message' => $_SESSION['info']
    ];
}

if (isset($this->data['errors'])) {
    foreach ($this->data['errors'] as $error) {
        $toasts[] = [
            'type' => 'danger',
            'message' => $error
        ];
    }
}

unset(
    $_SESSION['success'],
    $_SESSION['error'],
    $_SESSION['warning'],
    $_SESSION['info'],
    $_SESSION['errors']
);

if (!empty($toasts)) : ?>

    <div id="toast-container" style="
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 360px;
    ">

        <?php foreach ($toasts as $index => $toast) :

            $config = match ($toast['type']) {

                'success' => [
                    'color' => '#3B6D11',
                    'bg' => '#EAF3DE',
                    'border' => '#C0DD97',
                    'icon' => 'fa-circle-check',
                ],

                'danger' => [
                    'color' => '#A32D2D',
                    'bg' => '#FCEBEB',
                    'border' => '#F7C1C1',
                    'icon' => 'fa-circle-xmark',
                ],

                'warning' => [
                    'color' => '#8A6116',
                    'bg' => '#FFF4CE',
                    'border' => '#F1D58A',
                    'icon' => 'fa-triangle-exclamation',
                ],

                'info' => [
                    'color' => '#1F5F99',
                    'bg' => '#E8F3FC',
                    'border' => '#A9D0F0',
                    'icon' => 'fa-circle-info',
                ],

                default => [
                    'color' => '#555555',
                    'bg' => '#F2F2F2',
                    'border' => '#D5D5D5',
                    'icon' => 'fa-circle-info',
                ],
            };

            $color  = $config['color'];
            $bg     = $config['bg'];
            $border = $config['border'];
            $icon   = $config['icon'];

        ?>

            <div
                class="toast-item"
                id="toast-<?= $index ?>"
                style="
                    background: <?= $bg ?>;
                    border: 1px solid <?= $border ?>;
                    border-left: 4px solid <?= $color ?>;
                    border-radius: 8px;
                    padding: 14px 16px;
                    display: flex;
                    align-items: flex-start;
                    gap: 10px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                    animation: slideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
                    transition: opacity 0.6s ease, transform 0.6s ease;
                ">

                <i
                    class="fa-solid <?= $icon ?>"
                    style="
                        color: <?= $color ?>;
                        font-size: 16px;
                        margin-top: 2px;
                        flex-shrink: 0;
                    ">
                </i>

                <span style="
                    font-size: 14px;
                    color: <?= $color ?>;
                    flex-grow: 1;
                    line-height: 1.5;
                ">
                    <?= htmlspecialchars($toast['message']); ?>
                </span>

                <button
                    onclick="closeToast('toast-<?= $index ?>')"
                    style="
                        background: none;
                        border: none;
                        cursor: pointer;
                        color: <?= $color ?>;
                        font-size: 14px;
                        padding: 0;
                        flex-shrink: 0;
                        opacity: 0.6;
                    ">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

        <?php endforeach; ?>

    </div>

    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(60px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>

    <script>
        function closeToast(id) {
            const el = document.getElementById(id);

            if (!el) return;

            el.style.opacity = '0';
            el.style.transform = 'translateX(40px)';

            setTimeout(() => el.remove(), 600);
        }

        document.querySelectorAll('.toast-item').forEach(
            function(el, index) {

                el.style.animationDelay = (index * 150) + 'ms';
                el.style.opacity = '0';

                setTimeout(function() {
                    el.style.opacity = '1';
                }, index * 150);

                setTimeout(function() {
                    closeToast(el.id);
                }, 4000 + (index * 150));
            }
        );
    </script>

<?php endif; ?>