<?php

declare(strict_types=1);

use App\admsDaman\Helpers\GenerateLog;
use Phinx\Seed\AbstractSeed;

class AddAdmsDamanUserAddressesSeeder extends AbstractSeed
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
        // Variável para receber os dados para cadastro
        $data = [];

        // Se o usuário não existir, adiciona seus dados ao array $data
        $contIdUser = 0;
        for ($i = 48; $i <= 51; $i++) {
            $contIdUser++;

            $data[] = [
                'adms_daman_user_id' => $contIdUser,
                'adms_daman_address_id' => $i,
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        // Indicar em qual tabela deve adicionar/salvar o registro
        $adms_daman_user_addresses = $this->table('adms_daman_user_addresses');

        // Inserir registros na tabela
        $adms_daman_user_addresses->insert($data)->save();
    }
}