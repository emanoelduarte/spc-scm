<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanAccessLevels extends AbstractSeed
{
    /**
     * Cadastra nível de acesso na tabela 'adms_daman_access_levels' se ainda não existirem.
     * 
     * Este método é executado para popular a tabela 'adms_daman_access_levels' com registros iniciais de nível de acesso.
     * 
     * Primeiro, verifica se já existe nível de acesso na tabela com base no name.
     * Se o usuário não existir, os dados são inseridos na tabela.
     * 
     * @return void
     */
    public function run(): void
    {
        // Variável para receber os dados a serem inserido
        $data = [];

        ## SUPER ADMINISTRADOR
        // Verificar se o nível de acesso com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_access_levels WHERE name=:name', ['name' => 'Super Administrador'])->fetch();

        // Se o nível de acesso não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Super Administrador',
                'order_levels' => '1',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## Administrador
        // Verificar se o nível de acesso com o nome especificado já existe 
        $existingRecord = $this->query('SELECT id FROM adms_daman_access_levels WHERE name=:name', ['name' => 'Administrador'])->fetch();

        // Se o nível de acesso não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Administrador',
                'order_levels' => '2',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## Financeiro
        // Verificar se o nível de acesso com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_access_levels WHERE name=:name', ['name' => 'Financeiro'])->fetch();

        // Se o nível de acesso não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Financeiro',
                'order_levels' => '3',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## Encarregado de Obra
        // Verificar se o nível de acesso com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_access_levels WHERE name=:name', ['name' => 'Encarregado de Obra'])->fetch();

        // Se o nível de acesso não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Encarregado de Obra',
                'order_levels' => '4',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## Comprador
        // Verificar se o nível de acesso com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_access_levels WHERE name=:name', ['name' => 'Comprador'])->fetch();

        // Se o nível de acesso não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Comprador',
                'order_levels' => '5',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## Almoxarife
        // Verificar se o nível de acesso com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_access_levels WHERE name=:name', ['name' => 'Almoxarife'])->fetch();

        // Se o nível de acesso não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Almoxarife',
                'order_levels' => '6',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## Solicitante de Compra
        // Verificar se o nível de acesso com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_access_levels WHERE name=:name', ['name' => 'Solicitante de Compra'])->fetch();

        // Se o nível de acesso não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'Solicitante de Compra',
                'order_levels' => '7',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Obter a tabela 'adms_access_levels' para inserir os registros
        $adms_acess_levels = $this->table('adms_daman_access_levels');

        // Insere os registros na tabela
        $adms_acess_levels->insert($data)->save();
    }
}
