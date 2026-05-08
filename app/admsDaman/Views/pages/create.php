<?php

use App\admsDaman\Helpers\CSRFHelper;

?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Página</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a href="<?php echo $_ENV['URL_ADM']; ?>dashboard" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo $_ENV['URL_ADM']; ?>list-pages" class="text-decoration-none">Página</a>
            </li>
            <li class="breadcrumb-item">Cadastrar</li>

        </ol>

    </div>

    <div class="card mb-4 border-light shadow">

        <div class="card-header hstack gap-2">
            <span>Cadastrar</span>

            <span class="ms-auto d-sm-flex flex-row">
                <?php if (in_array("ListPages", $this->data['buttonPermissions'])) : ?>
                    <a href="<?php echo $_ENV['URL_ADM']; ?>list-pages" class="btn btn-info btn-sm me-1 mb-1"><i class="fa-solid fa-list"></i> Listar</a>
                <?php endif; ?>
            </span>

        </div>

        <div class="card-body">

            <?php include './app/admsDaman/Views/partials/alerts.php'; ?>

            <!-- Formulário para cadastrar um novo página -->
            <form action="" method="POST" class="row g-3">

                <input type="hidden" name="csrf_token" value="<?php echo CSRFHelper::generateCSRFToken('form_create_page'); ?>">

                <div class="col-12">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" name="name" class="form-control" id="name" placeholder="Nome da página" value="<?php echo $this->data['form']['name'] ?? ''; ?>">
                </div>

                <div class="col-md-6 col-sm-12">
                    <label for="controller" class="form-label">Controller</label>
                    <input type="text" name="controller" class="form-control" id="controller" placeholder="Nome do método ou controller" value="<?php echo $this->data['form']['controller'] ?? ''; ?>">
                </div>

                <div class="col-md-6 col-sm-12">
                    <label for="controller_url" class="form-label">URL</label>
                    <input type="text" name="controller_url" class="form-control" id="controller_url" placeholder="Nome do método ou controller na URL" value="<?php echo $this->data['form']['controller_url'] ?? ''; ?>">
                </div>

                <div class="col-12">
                    <label for="directory" class="form-label">Diretório</label>
                    <input type="text" name="directory" class="form-control" id="directory" placeholder="Nome do diretório da controller" value="<?php echo $this->data['form']['directory'] ?? ''; ?>">
                </div>

                <div class="col-12">
                    <label for="obs" class="form-label">Observação</label>
                    <textarea name="obs" class="form-control" id="obs" placeholder="Observação da página" rows="3"><?php echo $this->data['form']['obs'] ?? ''; ?></textarea>
                </div>

                <div class="col-md-6 col-sm-12">
                    <label for="page_status" class="form-label">Status</label>

                    <select name="page_status" class="form-select" id="page_status">
                        <option value="" selected>Selecione</option>
                        <option value="1" <?= isset($this->data['form']['page_status']) && $this->data['form']['page_status'] == 1 ? 'selected' : ''; ?>>Ativa</option>
                        <option value="0" <?= isset($this->data['form']['page_status']) && $this->data['form']['page_status'] == 0 ? 'selected' : ''; ?>>Inativa</option>
                    </select>

                </div>

                <div class="col-md-6 col-sm-12">
                    <label for="public_page" class="form-label">Pública</label>

                    <select name="public_page" class="form-select" id="public_page">
                        <option value="" selected>Selecione</option>
                        <option value="1" <?= isset($this->data['form']['public_page']) && $this->data['form']['public_page'] == 1 ? 'selected' : ''; ?>>Sim</option>
                        <option value="0" <?= isset($this->data['form']['public_page']) && $this->data['form']['public_page'] == 0 ? 'selected' : ''; ?>>Não</option>
                    </select>
                </div>

                <div class="col-md-6 col-sm-12">
                    <label for="adms_daman_packages_page_id" class="form-label">Pacote</label>

                    <select name="adms_daman_packages_page_id" class="form-select" id="adms_daman_packages_page_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe pacotes
                        if ($this->data['listPackagesPages'] ?? false) {

                            // Percorrer array de pacotes
                            foreach ($this->data['listPackagesPages'] as $listPackagesPages) {
                                extract($listPackagesPages);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_packages_page_id']) && $this->data['form']['adms_daman_packages_page_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$name</option>";
                            }
                        }
                        ?>
                    </select>

                </div>

                <div class="col-md-6 col-sm-12">
                    <label for="adms_daman_groups_page_id" class="form-label">Grupo</label>

                    <select name="adms_daman_groups_page_id" class="form-select" id="adms_daman_groups_page_id">
                        <option value="" selected>Selecione</option>

                        <?php
                        // Verificar se existe pacotes
                        if ($this->data['getAllGroupsPagesSelect'] ?? false) {

                            // Percorrer array de pacotes
                            foreach ($this->data['getAllGroupsPagesSelect'] as $getAllGroupsPagesSelect) {
                                extract($getAllGroupsPagesSelect);

                                // Verificar se deve manter selecionada a opção
                                $selected = isset($this->data['form']['adms_daman_groups_page_id']) && $this->data['form']['adms_daman_groups_page_id'] == $id ? 'selected' : '';

                                echo "<option value='$id' $selected>$name</option>";
                            }
                        }
                        ?>
                    </select>

                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">Cadastrar</button>
                </div>

            </form>

        </div>

    </div>

</div>