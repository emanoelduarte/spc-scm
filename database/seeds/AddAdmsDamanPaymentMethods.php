<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanPaymentMethods extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $data = [];

        ## 1 À VISTA/PIX
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'À VISTA/PIX'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'À VISTA/PIX',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 2 BOL. 5 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 5 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 5 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 3 BOL. 7 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 7 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 7 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 4 BOL. 10 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 10 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 10 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 5 BOL. 15 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 15 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 15 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 6 BOL. 20 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 20 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 20 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 7 BOL. 21 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 21 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 21 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 8 BOL. 28 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 28 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 28 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 9 BOL. 30 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 30 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 30 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 10 BOL. 30/45 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 30/45 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 30/45 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 11 BOL. 30/45/60 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 30/45/60 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 30/45/60 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 12 BOL. 30/60/90 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 30/60/90 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 30/60/90 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 13 BOL. 30/60/90/120 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 30/60/90/120 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 30/60/90/120 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 14 BOL. 30/60/90/120/150 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 30/60/90/120/150 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 30/60/90/120/150 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 15 BOL. 30/60/90/120/150/180 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 30/60/90/120/150/180 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 30/60/90/120/150/180 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 16 BOL. 30/60/90/120/150/180/210 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 30/60/90/120/150/180/210 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 30/60/90/120/150/180/210 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 17 BOL. 30/60/90/120/150/180/210/240 DIAS
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'BOL. 30/60/90/120/150/180/210/240 DIAS'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'BOL. 30/60/90/120/150/180/210/240 DIAS',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 18 CARTÃO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'CARTÃO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'CARTÃO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 19 DEPÓSITO BANCÁRIO
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'DEPÓSITO BANCÁRIO'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'DEPÓSITO BANCÁRIO',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        ## 19 PERMUTA
        // Verificar se a natureza de negócio com o nome especificado já existe
        $existingRecord = $this->query('SELECT id FROM adms_daman_payment_methods WHERE name=:name', ['name' => 'PERMUTA'])->fetch();

        // Se o nível a natureza de negócio não existir, adicione seu dados ao array $data
        if (!$existingRecord) {
            $data[] = [
                'name' => 'PERMUTA',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }


        // Obter a tabela 'adms_daman_payment_methods' para inserir os registros
        $adms_daman_payment_methods = $this->table('adms_daman_payment_methods');

        // Insere os registros na tabela
        $adms_daman_payment_methods->insert($data)->save();

    }
}
