<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanPagesSeeder extends AbstractSeed
{
    /**
     * Cadastra pagina na tabela `adms_daman_pages` se ainda não existirem.
     *
     * Este método é executado para popular a tabela `adms_daman_pages` com registros iniciais dos paginas.
     * Primeiro, verifica se já existe pagina na tabela com base no name. 
     * Se o pagina não existir, os dados são inseridos na tabela.
     * 
     * @return void
     */
    public function run(): void
    {

        // Variável para receber os dados a serem inseridos
        $data = [];

        // Variável para receber os dados que devem ser validados antes de cadastrar
        $pages = [
            ['name' => 'Dashboard', 'controller' => 'Dashboard', 'controller_url' => 'dashboard', 'directory' => 'dashboard', 'obs' => 'Página inicial do administrativo.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 1],

            ['name' => 'Cadastrar Usuário', 'controller' => 'CreateUser', 'controller_url' => 'create-user', 'directory' => 'users', 'obs' => 'Página com o formulário cadastrar usuário.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 2],
            ['name' => 'Listar Usuários', 'controller' => 'ListUsers', 'controller_url' => 'list-users', 'directory' => 'users', 'obs' => 'Página para listar o usuários.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 2],
            ['name' => 'Visualizar Usuário', 'controller' => 'ViewUser', 'controller_url' => 'view-user', 'directory' => 'users', 'obs' => 'Página apresentar os detalhes do usuário.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 2],
            ['name' => 'Editar Usuário', 'controller' => 'UpdateUser', 'controller_url' => 'update-user', 'directory' => 'users', 'obs' => 'Página com o formulário editar usuário.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 2],
            ['name' => 'Editar Senha do Usuário', 'controller' => 'UpdatePasswordUser', 'controller_url' => 'update-password-user', 'directory' => 'users', 'obs' => 'Página com o formulário editar senha do usuário.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 2],
            ['name' => 'Visualizar Perfil', 'controller' => 'ViewProfile', 'controller_url' => 'view-profile', 'directory' => 'users', 'obs' => 'Página para visualizar o perfil do usuário.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 2],
            ['name' => 'Apagar Usuário', 'controller' => 'DeleteUser', 'controller_url' => 'delete-user', 'directory' => 'users', 'obs' => 'Página para apagar o usuário do banco de dados.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 2],

            ['name' => 'Cadastrar Nível de Acesso', 'controller' => 'CreateAccessLevel', 'controller_url' => 'create-access-level', 'directory' => 'accessLevels', 'obs' => 'Página com o formulário cadastrar nível de acesso.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 3],
            ['name' => 'Listar Níveis de Acesso', 'controller' => 'ListAccessLevels', 'controller_url' => 'list-access-levels', 'directory' => 'accessLevels', 'obs' => 'Página para listar o níveis de acesso.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 3],
            ['name' => 'Visualizar Nível de Acesso', 'controller' => 'ViewAccessLevel', 'controller_url' => 'view-access-level', 'directory' => 'accessLevels', 'obs' => 'Página apresentar os detalhes do nível de acesso.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 3],
            ['name' => 'Editar Nível de Acesso', 'controller' => 'UpdateAccessLevel', 'controller_url' => 'update-access-level', 'directory' => 'accessLevels', 'obs' => 'Página com o formulário editar nível de acesso.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 3],
            ['name' => 'Apagar Nível de Acesso', 'controller' => 'DeleteAccessLevel', 'controller_url' => 'delete-access-level', 'directory' => 'accessLevels', 'obs' => 'Página para apagar o nível de acessodo banco de dados.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 3],
            ['name' => 'Editar Nível de Acesso do Usuário', 'controller' => 'UpdateUserAccessLevels', 'controller_url' => 'update-user-access-levels', 'directory' => 'accessLevels', 'obs' => 'Página com o checkbox editar nível de acesso.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 3],
            ['name' => 'Sincronizar páginas', 'controller' => 'AccessLevelPageSync', 'controller_url' => 'access-level-page-sync', 'directory' => 'accessLevels', 'obs' => 'Controller responsável pela sincronização de páginas', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 3],


            ['name' => 'Cadastrar Pacote', 'controller' => 'CreatePackage', 'controller_url' => 'create-package', 'directory' => 'packages', 'obs' => 'Página com o formulário cadastrar pacote.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 4],
            ['name' => 'Listar Pacotes', 'controller' => 'ListPackages', 'controller_url' => 'list-packages', 'directory' => 'packages', 'obs' => 'Página para listar o pacotes.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 4],
            ['name' => 'Visualizar Pacote', 'controller' => 'ViewPackage', 'controller_url' => 'view-package', 'directory' => 'packages', 'obs' => 'Página apresentar os detalhes do pacote.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 4],
            ['name' => 'Editar Pacote', 'controller' => 'UpdatePackage', 'controller_url' => 'update-package', 'directory' => 'packages', 'obs' => 'Página com o formulário editar pacote.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 4],
            ['name' => 'Apagar Pacote', 'controller' => 'DeletePackage', 'controller_url' => 'delete-package', 'directory' => 'packages', 'obs' => 'Página para apagar o pacote do banco de dados.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 4],

            ['name' => 'Cadastrar Grupo de Página', 'controller' => 'CreateGroupPage', 'controller_url' => 'create-group-page', 'directory' => 'groupsPages', 'obs' => 'Página com o formulário cadastrar grupo de página.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 5],
            ['name' => 'Listar Grupos de Páginas', 'controller' => 'ListGroupsPages', 'controller_url' => 'list-groups-pages', 'directory' => 'groupsPages', 'obs' => 'Página para listar o grupos de páginas.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 5],
            ['name' => 'Visualizar Grupo de Página', 'controller' => 'ViewGroupPage', 'controller_url' => 'view-group-page', 'directory' => 'groupsPages', 'obs' => 'Página apresentar os detalhes do grupo de página.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 5],
            ['name' => 'Editar Grupo de Página', 'controller' => 'UpdateGroupPage', 'controller_url' => 'update-group-page', 'directory' => 'groupsPages', 'obs' => 'Página com o formulário editar grupo de página.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 5],
            ['name' => 'Apagar Grupo de Página', 'controller' => 'DeleteGroupPage', 'controller_url' => 'delete-group-page', 'directory' => 'groupsPages', 'obs' => 'Página para apagar o grupo de página do banco de dados.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 5],

            ['name' => 'Cadastrar Página', 'controller' => 'CreatePage', 'controller_url' => 'create-page', 'directory' => 'pages', 'obs' => 'Página com o formulário cadastrar página.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 6],
            ['name' => 'Listar Páginas', 'controller' => 'ListPages', 'controller_url' => 'list-pages', 'directory' => 'pages', 'obs' => 'Página para listar o páginas.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 6],
            ['name' => 'Visualizar Página', 'controller' => 'ViewPage', 'controller_url' => 'view-group-page', 'directory' => 'pages', 'obs' => 'Página apresentar os detalhes do página.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 6],
            ['name' => 'Editar Página', 'controller' => 'UpdatePage', 'controller_url' => 'update-group-page', 'directory' => 'pages', 'obs' => 'Página com o formulário editar página.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 6],
            ['name' => 'Apagar Página', 'controller' => 'DeletePage', 'controller_url' => 'delete-group-page', 'directory' => 'pages', 'obs' => 'Página para apagar o página do banco de dados.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 6],

            ['name' => 'Página de Login', 'controller' => 'Login', 'controller_url' => 'login', 'directory' => 'login', 'obs' => 'Página com o formulário de login.', 'public_page' => 1, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 7],
            ['name' => 'Recuperar Senha', 'controller' => 'ForgotPassword', 'controller_url' => 'forgot-password', 'directory' => 'login', 'obs' => 'Página com o formulário para recuperar a senha.', 'public_page' => 1, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 7],
            ['name' => 'Cadastrar Nova Senha', 'controller' => 'ResetPassword', 'controller_url' => 'reset-password', 'directory' => 'login', 'obs' => 'Página com o formulário cadastrar nova senha no login.', 'public_page' => 1, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 7],
            ['name' => 'Sair do Administrativo', 'controller' => 'Logout', 'controller_url' => 'logout', 'directory' => 'login', 'obs' => 'Deslogar do sistema administrativo.', 'public_page' => 1, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 7],

            ['name' => 'Erro 403', 'controller' => 'Error403', 'controller_url' => 'logout', 'directory' => 'errors', 'obs' => 'Erro que deve apresentado quando não encontrar a página.', 'public_page' => 1, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 8],

            // Páginas Pedidos
            ['name' => 'Criar Pedidos', 'controller' => 'CreateOrder', 'controller_url' => 'create-order', 'directory' => 'orders', 'obs' => 'Página para criar pedido', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 9],
            ['name' => 'Listar Pedidos', 'controller' => 'ListOrders', 'controller_url' => 'list-orders', 'directory' => 'orders', 'obs' => 'Página para listar pedidos', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 9],
            ['name' => 'Visualizar Pedido', 'controller' => 'ViewOrder', 'controller_url' => 'view-order', 'directory' => 'orders', 'obs' => 'Página para visualizar pedido', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 9],
            ['name' => 'Editar Pedido Compra', 'controller' => 'UpdateOrder', 'controller_url' => 'update-order', 'directory' => 'orders', 'obs' => 'Página para editar pedido do tipo compra', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 9],
            ['name' => 'Editar Pedido Locação', 'controller' => 'UpdateRentalOrder', 'controller_url' => 'update-rental-order', 'directory' => 'orders', 'obs' => 'Página para editar pedido do tipo locação', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 9],
            ['name' => 'Apagar Pedido', 'controller' => 'DeleteOrder', 'controller_url' => 'delete-order', 'directory' => 'orders', 'obs' => 'Página para deletar pedido', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 9],
            ['name' => 'Apagar Item do Pedido', 'controller' => 'DeleteItem', 'controller_url' => 'delete-item', 'directory' => 'orders', 'obs' => 'Página para deletar pedido', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 9],

            // Páginas Categorias
            ['name' => 'Criar Categoria', 'controller' => 'CreateCategory', 'controller_url' => 'create-category', 'directory' => 'categories', 'obs' => 'Página para criar categoria', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 10],
            ['name' => 'Listar Categorias', 'controller' => 'ListCategories', 'controller_url' => 'list-categories', 'directory' => 'categories', 'obs' => 'Página para listar categorias', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 10],
            ['name' => 'Visualizar Categoria', 'controller' => 'ViewCategory', 'controller_url' => 'view-category', 'directory' => 'categories', 'obs' => 'Página para visualizar Categoria', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 10],
            ['name' => 'Editar Categoria', 'controller' => 'UpdateCategory', 'controller_url' => 'update-category', 'directory' => 'categories', 'obs' => 'Página para editar categoria', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 10],
            ['name' => 'Apagar Categoria', 'controller' => 'DeleteCategory', 'controller_url' => 'delete-category', 'directory' => 'categories', 'obs' => 'Página para deletar categoria', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 10],

            // Páginas Projetos
            ['name' => 'Criar Obra', 'controller' => 'CreateProject', 'controller_url' => 'create-project', 'directory' => 'projects', 'obs' => 'Página para criar obra', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 11],
            ['name' => 'Listar Obras', 'controller' => 'ListProjects', 'controller_url' => 'list-projects', 'directory' => 'projects', 'obs' => 'Página para listar obras', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 11],
            ['name' => 'Visualizar Obra', 'controller' => 'ViewProject', 'controller_url' => 'view-project', 'directory' => 'projects', 'obs' => 'Página para visualizar Obra', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 11],
            ['name' => 'Editar Obra', 'controller' => 'UpdateProject', 'controller_url' => 'update-project', 'directory' => 'projects', 'obs' => 'Página para editar Obra', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 11],
            ['name' => 'Apagar Obra', 'controller' => 'DeleteProject', 'controller_url' => 'delete-project', 'directory' => 'projects', 'obs' => 'Página para deletar Obra', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 11],
            ['name' => 'Editar Obras Associadas ao Usuário', 'controller' => 'UpdateUserProjectAssociate', 'controller_url' => 'update-user-project-associate', 'directory' => 'projects', 'obs' => 'Página para editar a obra associada ao usuário', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 11],

            // Páginas Fornecedores
            ['name' => 'Cadastrar Fornecedor', 'controller' => 'CreateSupplier', 'controller_url' => 'create-supplier', 'directory' => 'suppliers', 'obs' => 'Página para cadastrar fornecedor', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 12],
            ['name' => 'Listar Fornecedores', 'controller' => 'ListSuppliers', 'controller_url' => 'list-suppliers', 'directory' => 'suppliers', 'obs' => 'Página para listar fornecedores', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 12],
            ['name' => 'Visualizar Fornecedor', 'controller' => 'ViewSupplier', 'controller_url' => 'view-supplier', 'directory' => 'suppliers', 'obs' => 'Página para visualizar fornecedor', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 12],
            ['name' => 'Editar Fornecedor', 'controller' => 'UpdateSupplier', 'controller_url' => 'update-supplier', 'directory' => 'suppliers', 'obs' => 'Página para atualizar fornecedor', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 12],
            ['name' => 'Apagar Fornecedor', 'controller' => 'DeleteSupplier', 'controller_url' => 'delete-supplier', 'directory' => 'suppliers', 'obs' => 'Página para apagar fornecedor', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 12],


            // Páginas Compras
            ['name' => 'Gerar Compra', 'controller' => 'GeneratePurchasing', 'controller_url' => 'generate-purchasing', 'directory' => 'purchasing', 'obs' => 'Página para gerar compra', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 13],
            ['name' => 'Cancelar Compra', 'controller' => 'CancelPurchasing', 'controller_url' => 'cancel-purchasing', 'directory' => 'purchasing', 'obs' => 'Página para cancelar compra', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 13],
            ['name' => 'Listar Compras', 'controller' => 'ListPurchasings', 'controller_url' => 'list-purchasings', 'directory' => 'purchasing', 'obs' => 'Página para listar compras', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 13],
            ['name' => 'Visualizar Compra', 'controller' => 'ViewPurchasing', 'controller_url' => 'view-purchasing', 'directory' => 'purchasing', 'obs' => 'Página para visualizar compra', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 13],
            ['name' => 'Apagar Compra', 'controller' => 'DeletePurchasing', 'controller_url' => 'delete-purchasing', 'directory' => 'purchasing', 'obs' => 'Página para deletar compra', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 13],
            ['name' => 'Criar Compra para aprovação', 'controller' => 'CreatePurchasingQuote', 'controller_url' => 'create-purchasing-quote', 'directory' => 'purchasing', 'obs' => 'Página para cadastrar compra na área de espera.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 13],
            ['name' => 'Listar Compras Pendendes', 'controller' => 'ListPurchasingQuotes', 'controller_url' => 'list-purchasing-quotes', 'directory' => 'purchasing', 'obs' => 'Página listar as compras pendendes de aprovação.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 13],
            ['name' => 'Visualizar Compras Pendendes', 'controller' => 'ViewPurchasingQuote', 'controller_url' => 'view-purchasing-quote', 'directory' => 'purchasing', 'obs' => 'Página visualizar os detalhes de uma compra pendende de aprovação.', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 13],
            ['name' => 'Aprovar Compra', 'controller' => 'ApprovePurchasingQuote', 'controller_url' => 'approve-pruchasing-quote', 'directory' => 'purchasing', 'obs' => 'Controller de aprovação de compras', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 13],
            ['name' => 'Rejeitar Compra', 'controller' => 'RejectPurchasingQuote', 'controller_url' => 'reject-pruchasing-quote', 'directory' => 'purchasing', 'obs' => 'Controller de rejeição de compras', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 13],

            // Permissions
            ['name' => 'Editar Permissão', 'controller' => 'ListAccessLevelsPermissions', 'controller_url' => 'list-access-levels-permissions', 'directory' => 'permission', 'obs' => 'Página para editar permissão', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 14],

            // Pdfs
            ['name' => 'Gerar PDF Compra', 'controller' => 'GeneratePdfPurchasing', 'controller_url' => 'generate-pdf-purchasing', 'directory' => 'pdfs', 'obs' => 'Página para gerar PDF de Compras', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 15],

            // Páginas Estoque
            ['name' => 'Criar Item de Material de Estoque', 'controller' => 'CreateMaterialStock', 'controller_url' => 'create-material-stock', 'directory' => 'materialstock', 'obs' => 'Página para cadastrar novo item no estoque', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 16],
            ['name' => 'Criar Movimentação de Material do Estoque', 'controller' => 'CreateStockMovement', 'controller_url' => 'create-stock-movement', 'directory' => 'materialstock', 'obs' => 'Página para cadastrar nova movimentação no estoque', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 16],
            ['name' => 'Listar Materiais do Estoque', 'controller' => 'ListMaterialStock', 'controller_url' => 'list-material-stock', 'directory' => 'materialstock', 'obs' => 'Página para listar itens do estoque', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 16],
            ['name' => 'Editar Materiais do Estoque', 'controller' => 'UpdateMaterialStock', 'controller_url' => 'update-material-stock', 'directory' => 'materialstock', 'obs' => 'Página para editar itens do estoque', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 16],
            ['name' => 'Visualizar Materiais do Estoque', 'controller' => 'ViewMaterialStock', 'controller_url' => 'view-material-stock', 'directory' => 'materialstock', 'obs' => 'Página para visualizar itens do estoque', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 16],

            // Conteúdos especificos por nível de acesso
            ['name' => 'Conteúdo Solicitante de compra', 'controller' => 'PurchaseContent', 'controller_url' => 'purchase-content', 'directory' => 'content', 'obs' => 'Conteúdo exclusivo para solicitantes de compra e usuários com permissão', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 17],

            ['name' => 'Conteúdo Comprador', 'controller' => 'BuyerContent', 'controller_url' => 'buyer-content', 'directory' => 'content', 'obs' => 'Conteúdo exclusivo do comprador e de usuários com permissão', 'public_page' => 0, 'page_status' => 1, 'adms_daman_packages_page_id' => 1, 'adms_daman_groups_page_id' => 17],
        ];

        // Percorrer o array com dados que devem ser validados antes de cadastrar
        foreach ($pages as $page) {

            // Verifica se a página com o name especificado já existe
            $existingRecord = $this->query('SELECT id FROM adms_daman_pages WHERE name=:name', ['name' => $page['name']])->fetch();

            // Se a página não existir, adiciona seus dados ao array $data
            if (!$existingRecord) {
                $data[] = [
                    'name' => $page['name'],
                    'controller' => $page['controller'],
                    'controller_url' => $page['controller_url'],
                    'directory' => $page['directory'],
                    'obs' => $page['obs'],
                    'public_page' => $page['public_page'],
                    'page_status' => $page['page_status'],
                    'adms_daman_packages_page_id' => $page['adms_daman_packages_page_id'],
                    'adms_daman_groups_page_id' => $page['adms_daman_groups_page_id'],
                    'created_at' => date("Y-m-d H:i:s"),
                ];
            }
        }

        // Obtém a tabela 'adms_daman_pages' para inserir os registros
        $adms_daman_pages = $this->table('adms_daman_pages');

        // Insere os registros na tabela
        $adms_daman_pages->insert($data)->save();
    }
}
