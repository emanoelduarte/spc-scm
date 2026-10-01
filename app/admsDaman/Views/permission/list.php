<?php

use App\admsDaman\Helpers\CSRFHelper;

// Gerar o token CSRF para validar o usuário
$csrf_token = CSRFHelper::generateCSRFToken('form_update_access_level_permissions');
?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Permissões</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-access-levels">Níveis de Acesso</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Permissões</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span><?= $this->data['accessLevel']['name'] ?? 'Listar'  ?></span>
            <span class="ms-auto">

            </span>
        </div>

        <div class="card-body">
            <?php // Incluir arquivo rsponsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            ?>

            <?php

            $groups = [];

            foreach (
                $this->data['pages'] ?? []
                as $page
            ) {

                if (
                    !empty($page['group_id'])
                    &&
                    !empty($page['group_name'])
                ) {

                    $groups[(int) $page['group_id']] =
                        $page['group_name'];
                }
            }

            ?>

            <div class="row g-3 mb-4">

                <div class="col-md-7">

                    <label
                        for="pagePermissionSearch"
                        class="form-label">

                        Pesquisar página

                    </label>

                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="fa-solid fa-magnifying-glass"></i>

                        </span>

                        <input
                            type="text"
                            id="pagePermissionSearch"
                            class="form-control"
                            placeholder="Nome, controller, rota ou observação...">

                    </div>

                </div>


                <div class="col-md-5">

                    <label
                        for="pagePermissionGroup"
                        class="form-label">

                        Grupo

                    </label>

                    <select
                        id="pagePermissionGroup"
                        class="form-select">

                        <option value="">

                            Todos os grupos

                        </option>

                        <?php foreach (
                            $groups
                            as $groupId => $groupName
                        ): ?>

                            <option
                                value="<?= (int) $groupId; ?>">

                                <?= htmlspecialchars(
                                    $groupName
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>

            <?php

            // Acessa o IF quando encontrar páginas no array de pages
            if ($this->data['pages'] ?? false) {
            ?>

                <form action="<?= $_ENV['URL_ADM']; ?>list-access-levels-permissions/<?= $this->data['accessLevel']['id'] ?? ''  ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <input type="hidden" name="adms_daman_access_level_id" value="<?= ($this->data['accessLevel']['id'] ?? '') ?>">


                    <table class="table table-striped table-hover permission-table">
                        <thead>

                            <tr>
                                <th scope="col"> Liberado </th>
                                <th scope="col"> ID </th>
                                <th scope="col"> Página </th>
                                <th scope="col"> Grupo </th>
                                <th scope="col" class="d-none d-md-table-cell"> Observação </th>
                                <th scope="col" class="d-none d-md-table-cell"> Pública / Privada </th>
                            </tr>

                        </thead>
                        <tbody>

                            <?php

                            $currentGroupId = null;

                            $accessLevelsPages =
                                $this->data['accessLevelsPages']
                                ? $this->data['accessLevelsPages']
                                : [];


                            foreach ($this->data['pages'] as $page):
                                $id = (int) $page['id'];
                                $name = $page['name'] ?? '';
                                $controller = $page['controller'] ?? '';
                                $controllerUrl = $page['controller_url'] ?? '';
                                $obs = $page['obs'] ?? '';
                                $publicPage = (bool) ($page['public_page'] ?? false);
                                $groupId = (int) ($page['group_id'] ?? 0);
                                $groupName = $page['group_name'] ?? 'Sem Grupo';


                                /*
                                * =====================================================
                                * CABEÇALHO DO GRUPO
                                * =====================================================
                                */
                                if (
                                    $currentGroupId
                                    !==
                                    $groupId
                                ):

                                    $currentGroupId =
                                        $groupId;

                            ?>

                                    <tr class="permission-group-row" data-group-header="<?= $groupId; ?>">

                                        <td colspan="6" class="fw-bold">
                                            <i class="fa-solid fa-folder-open me-2"> </i>
                                            <?= htmlspecialchars($groupName); ?>
                                        </td>
                                    </tr>

                                <?php

                                endif;


                                /*
                                * =====================================================
                                * PERMISSÃO
                                * =====================================================
                                */
                                $checked = in_array($id, $accessLevelsPages) || $publicPage ? 'checked' : '';
                                $disabled = $publicPage ? 'disabled' : '';

                                ?>

                                <tr class="permission-page-row" data-group="<?= $groupId; ?>" data-search="<?= htmlspecialchars(
                                                                                                                strtolower($name . ' ' . $controller . ' ' . $controllerUrl . ' ' . $obs . ' ' . $groupName),
                                                                                                                ENT_QUOTES,
                                                                                                                'UTF-8'
                                                                                                            ); ?>">
                                    <td>

                                        <div class="form-check form-switch">
                                            <input
                                                type="checkbox"
                                                name="accessLevelPage[<?= $id; ?>]"
                                                class="form-check-input"
                                                role="switch"
                                                id="accessLevelPage<?= $id; ?>"
                                                value="<?= $id; ?>"
                                                <?= $checked; ?>
                                                <?= $disabled; ?>>

                                            <label
                                                class="form-check-label"
                                                for="accessLevelPage<?= $id; ?>">
                                            </label>
                                        </div>
                                    </td>


                                    <td>
                                        <?= $id; ?>
                                    </td>


                                    <td>
                                        <div class="fw-semibold">
                                            <?= htmlspecialchars(
                                                $name
                                            ); ?>

                                        </div>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars(
                                                $controllerUrl
                                            ); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge text-bg-secondary">
                                            <?= htmlspecialchars($groupName); ?>
                                        </span>
                                    </td>


                                    <td class="d-none d-md-table-cell">
                                        <?= htmlspecialchars($obs); ?>
                                    </td>

                                    <td class="d-none d-md-table-cell">
                                        <?php if ($publicPage): ?>
                                            <span class="badge text-bg-success">
                                                Pública
                                            </span>

                                        <?php else: ?>
                                            <span class="badge text-bg-danger">
                                                Privada
                                            </span>

                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        </tbody>
                    </table>

                    <div class="col-12">
                        <!-- <button type="submit" class="btn btn-warning btn-sm" onclick="showLoading()">Editar</button> -->
                        <button type="submit" class="btn btn-warning btn-sm">Salvar</button>
                    </div>
                </form>

            <?php
            } else {
                echo "<div class='alert alert-danger' role='alert'>Nenhuma página encontrada!</div>";
            }
            ?>
        </div>
    </div>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function() {

                const searchInput =
                    document.getElementById(
                        'pagePermissionSearch'
                    );

                const groupSelect =
                    document.getElementById(
                        'pagePermissionGroup'
                    );

                const pageRows =
                    document.querySelectorAll(
                        '.permission-page-row'
                    );

                const groupRows =
                    document.querySelectorAll(
                        '.permission-group-row'
                    );


                function normalizeText(text) {

                    return text
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(
                            /[\u0300-\u036f]/g,
                            ''
                        );
                }


                function filterPages() {

                    const search =
                        normalizeText(
                            searchInput.value.trim()
                        );

                    const selectedGroup =
                        groupSelect.value;


                    /*
                     * Guardar quantas páginas ficaram
                     * visíveis em cada grupo.
                     */
                    const visibleGroups = {};


                    pageRows.forEach(
                        function(row) {

                            const rowGroup =
                                row.dataset.group;

                            const rowSearch =
                                normalizeText(
                                    row.dataset.search ??
                                    ''
                                );


                            const matchesSearch =
                                search === '' ||
                                rowSearch.includes(
                                    search
                                );


                            const matchesGroup =
                                selectedGroup === '' ||
                                rowGroup ===
                                selectedGroup;


                            const visible =
                                matchesSearch &&
                                matchesGroup;


                            row.style.display =
                                visible ?
                                '' :
                                'none';


                            if (visible) {

                                visibleGroups[rowGroup] =
                                    true;
                            }
                        }
                    );


                    /*
                     * Mostrar apenas cabeçalhos de grupos
                     * que ainda possuem páginas visíveis.
                     */
                    groupRows.forEach(
                        function(row) {

                            const groupId =
                                row.dataset.groupHeader;


                            row.style.display =
                                visibleGroups[groupId] ?
                                '' :
                                'none';
                        }
                    );
                }


                searchInput.addEventListener(
                    'input',
                    filterPages
                );


                groupSelect.addEventListener(
                    'change',
                    filterPages
                );
            }
        );
    </script>
</div>