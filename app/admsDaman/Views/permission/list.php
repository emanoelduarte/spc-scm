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
            // var_dump($this->data['accessLevelsPages']);
            // var_dump($this->data['pages']);

            // Acessa o IF quando encontrar páginas no array de pages
            if ($this->data['pages'] ?? false) {
            ?>

                <form action="<?= $_ENV['URL_ADM']; ?>list-access-levels-permissions/<?= $this->data['accessLevel']['id'] ?? ''  ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <input type="hidden" name="adms_daman_access_level_id" value="<?= ($this->data['accessLevel']['id'] ?? '') ?>">


                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Liberado</th>
                                <th scope="col">Página</th>
                                <th scope="col">Nome</th>
                                <th scope="col" class="d-none d-md-table-cell">Observação</th>
                                <th scope="col" class="d-none d-md-table-cell">Pública / Privada</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            // Percorrer o array de páginas
                            foreach ($this->data['pages'] as $page) {
                                extract($page);
                            ?>
                                <tr>
                                    <td>

                                        <?php
                                        // Sempre deve ser um array, caso retorne falso, ainda assim transforma em array vazio
                                        $accessLevelsPages = $this->data['accessLevelsPages'] ? $this->data['accessLevelsPages'] : [];

                                        // Verificar se a página Atual ($id) está no array de páginas
                                        $checked = in_array($id, $accessLevelsPages) || $public_page ? 'checked' : '';

                                        $disabled = $public_page ? 'disabled' : '';

                                        echo "<div class='form-check form-switch'>";
                                        echo "<input type='checkbox' name='accessLevelPage[$id]' class='form-check-input' role='switch' id='accessLevelPage$id' value='$id' {$checked} {$disabled}>";
                                        echo " <label class='form-check-label' for='accessLevelPage$id'></label>";
                                        echo "</div>";

                                        ?>

                                    </td>
                                    <td><?= $id ?></td>
                                    <td><?= $name ?></td>
                                    <td class="d-none d-md-table-cell"><?= $obs ?></td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo $public_page ? "<span class='badge text-bg-success'>Pública</span>" : "<span class='badge text-bg-danger'>Privada</span>";; ?>
                                    </td>
                                </tr>
                            <?php } ?>
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
</div>