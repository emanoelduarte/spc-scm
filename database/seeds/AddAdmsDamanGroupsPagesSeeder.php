<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanGroupsPagesSeeder extends AbstractSeed
{
    /**
     * Cadastra pacote na tabela `adms_daman_groups_pages` se ainda não existirem.
     *
     * Este método é executado para popular a tabela `adms_daman_groups_pages` com registros iniciais dos pacotes.
     * Primeiro, verifica se já existe pacote na tabela com base no name. 
     * Se o pacote não existir, os dados são inseridos na tabela.
     * 
     * @return void
     */
    public function run(): void
    {

        // Variável para receber os dados a serem inseridos
        $data = [];

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Dashboard'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Dashboard',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Usuários'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Usuários',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Nível de Acesso'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Nível de Acesso',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Pacote de Páginas'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Pacote de Páginas',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Grupo de Páginas'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Grupo de Páginas',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Páginas'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Páginas',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Login'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Login',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Erros'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Erros',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Pedidos de Compra'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Pedidos de Compra',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Categoria'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Categoria',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Obras'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Obras',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Compras'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Compras',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Verifica se o grupo de página com o name especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_groups_pages WHERE name=:name', ['name' => 'Permissões'])->fetch();

        // Se o usuário não existir, adiciona seus dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Permissões',
                'obs' => '',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Obtém a tabela 'adms_daman_groups_pages' para inserir os registros
        $adms_daman_groups_pages = $this->table('adms_daman_groups_pages');

        // Insere os registros na tabela
        $adms_daman_groups_pages->insert($data)->save();
    }
}
