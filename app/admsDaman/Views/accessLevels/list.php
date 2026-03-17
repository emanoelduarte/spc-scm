<?php
// Gerar o token CSRF para validar o usuário

use App\admsDaman\Helpers\CSRFHelper;

$csrf_token = CSRFHelper::generateCSRFToken('form_delete_level');
?>
<div class="container-fluid px-4">

    <div class="mb-1 hstack gap-2">
        <h2 class="mt-3">Níveis de acesso</h2>

        <ol class="breadcrumb mb-3 mt-3 ms-auto">
            <li class="breadcrumb-item">
                <a class="text-decoration-none" href="<?php echo $_ENV['URL_ADM'] ?>dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Níveis de acesso</li>
            </li>
        </ol>
    </div>

    <div class="card mb-4 border-light shadow">
        <div class="card-header hstack gap-2">
            <span>Listar</span>
            <span class="ms-auto">
                <a href="<?= $_ENV['URL_ADM'] . 'create-access-level'; ?>" class="btn btn-success btn-sm"><i class="fa-solid fa-user-plus"></i> Cadastrar</a>

                <a href="<?= $_ENV['URL_ADM'] . 'access-level-page-sync'; ?>" class="btn btn-warning btn-sm" onclick="showLoading()"><i class="fa-solid fa-rotate"></i> Sincronizar</a>
            </span>
        </div>

        <div class="card-body">
            <?php // Incluir arquivo rsponsável por alerta
            include './app/admsDaman/Views/partials/alerts.php';

            // Acessa o IF quando encontrar o elemento no array levelsAccess
            if ($this->data['levelsAccess'] ?? false) {
            ?>


                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nome</th>
                            <th scope="col" class="d-none d-md-table-cell">Ordem</th>
                            <th scope="col" class="text-center">Ações</th>
                        </tr>
                    <tbody>

                        <?php
                        // Percorrer o array de níveis de acesso para exibilos em linha
                        foreach ($this->data['levelsAccess'] as $levelAccess) :
                            extract($levelAccess);
                        ?>
                            <tr>
                                <td><?= $id ?></td>
                                <td><?= $name ?></td>
                                <td><?= $order_levels ?></td>

                                <td class="d-md-flex flex-row justify-content-center">
                                    <a href="<?= $_ENV['URL_ADM'] . 'view-access-level/' . $id; ?>" class="btn btn-primary btn-sm me-1 mb-1"><i class="fa-solid fa-eye"></i> Visualizar</a>

                                    <a href="<?= $_ENV['URL_ADM'] . 'update-access-level/' . $id; ?>" class="btn btn-warning btn-sm me-1 mb-1"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                                    <?php
                                    // Formulário para envio dos dados para deletar nível de acesso 
                                    ?>
                                    <form id="formDelete<?= $id; ?>" action="<?= $_ENV['URL_ADM']; ?>delete-access-level" method="POST">

                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                                        <input type="hidden" name="id" id="id" value="<?= $id ?? ''; ?>">

                                        <button type="submit" class="btn btn-danger btn-sm me-1 mb-1" onclick="confirmDeletion(event, <?= $id ?>)"> <i class="fa-solid fa-trash"></i> Apagar</button>

                                    </form>
                                </td>
                            </tr>

                        <?php
                        endforeach;
                        ?>

                    </tbody>
                    </thead>
                </table>
            <?php
            // Adiconar o arquivo de paginação
                require_once './app/admsDaman/Views/partials/pagination.php';
            } else {
                echo "<div class='alert alert-danger' role='alert'>Nenhum Usuário encontrado</div>";
            }
            ?>
        </div>
    </div>

</div>