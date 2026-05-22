<?php

use App\admsDaman\Helpers\CSRFHelper;

?>

<div class="container-fluid px-4">
    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Usuários</h2>
        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?= $_ENV['URL_ADM'] ?>list-users">Usuários</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Cadastrar Usuário</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Cadastrar Usuário</span>
            <span class="ms-auto d-sm-flex flex-row">
                <?php if (in_array("ListUsers", $this->data['buttonPermissions'])): ?>
                <a href="<?= $_ENV['URL_ADM'] . 'list-users'; ?>" class="btn btn-info btn-sm me-1 mb-1"><i
                        class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>
            </span>
        </div>

        <div class="card-body">
            <?php
            // Incluir arquivo responsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';
            ?>
            <form action="" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= CSRFHelper::generateCSRFToken('form_create_user'); ?>"
                    id="">

                <div class="col-12">
                    <label for="name" class="form-label">Nome:</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="<?= $this->data['form']['name'] ?? ''; ?>" placeholder="Seu nome">
                </div>

                <!-- <div class="col-lg-3 col-md-6 col-sm-12">
                    <label for="adms_daman_project_id" class="form-label">Obras</label>

                    <select name="adms_daman_project_id" class="form-select" id="adms_daman_project_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe pacotes
                        if ($this->data['getAllProjectsSelectActive'] ?? false) {

                            // Percorrer array de pacotes
                            foreach ($this->data['getAllProjectsSelectActive'] as $getAllProjectsSelectActive) {
                                extract($getAllProjectsSelectActive);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_project_id']) && $this->data['form']['adms_daman_project_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$name</option>";
                            }
                        }
                        ?>
                    </select>
                </div> -->

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label for="adms_daman_access_level_id" class="form-label">Nível de Acesso</label>

                    <select name="adms_daman_access_level_id" class="form-select" id="adms_daman_access_level_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe pacotes
                        if ($this->data['getAllAccessLevels'] ?? false) {

                            // Percorrer array de pacotes
                            foreach ($this->data['getAllAccessLevels'] as $getAllAccessLevels) {
                                extract($getAllAccessLevels);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_access_level_id']) && $this->data['form']['adms_daman_access_level_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$name</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-12">
                    <label for="email" class="form-label">E-mail:</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?= $this->data['form']['email'] ?? ''; ?>" placeholder="Seu melhor e-mail">
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Senha:</label>
                    <input type="password" class="form-control" id="password" name="password"
                        value="<?= $this->data['form']['password'] ?? ''; ?>"
                        placeholder="Digite uma senha com 6 caracters">
                </div>
                <div class="col-md-6">
                    <label for="confirm_password" class="form-label">Confirmar Senha:</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                        value="<?= $this->data['form']['confirm_password'] ?? ''; ?>" placeholder="Confirme sua senha">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>