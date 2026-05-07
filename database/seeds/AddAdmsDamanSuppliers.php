<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanSuppliers extends AbstractSeed
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

    // Variável para receber os dados a serem inseridos
        $data = [];

        // Variável para receber os dados que devem ser validados antes de cadastrar
        $suppliers = [
            ['legal_name' => 'CASA COMÉRCIO DE MATERIAIS DE CONSTRUÇÃO LTDA', 
            'trade_name' => 'DICASA', 
            'cnpj' => '13253346000143', 
            'contact_name' => 'JUNIOR', 
            'phone' => '91985044131', 
            'email' => 'exemple@exemple.com.br',
            'adms_daman_suppliers_types_id' => 1, 
            'supplier_status' => 1, 
            'created_at' => date('Y-m-d H:i:s'),],

            ['legal_name' => 'IMPORTADOR OPLIMA LTDA', 
            'trade_name' => 'OPLIMA', 
            'cnpj' => '04945481000169', 
            'contact_name' => 'MESSIAS/EDILSON', 
            'phone' => '91980963374',
            'email' => 'exemple@exemple.com.br', 
            'adms_daman_suppliers_types_id' => 1, 
            'supplier_status' => 1, 
            'created_at' => date('Y-m-d H:i:s'),],

            ['legal_name' => 'ACO BELEM COMERCIAL LTDA', 
            'trade_name' => 'ACO BELÉM', 
            'cnpj' => '04082321000133', 
            'contact_name' => 'GLEYCE', 
            'phone' => '91988506863', 
            'email' => 'exemple@exemple.com.br',
            'adms_daman_suppliers_types_id' => 1, 
            'supplier_status' => 1, 
            'created_at' => date('Y-m-d H:i:s'),],

            ['legal_name' => 'TECHFIX COM. DE PRODUTOS DE FIXAÇÃO LTDA', 
            'trade_name' => 'TECHFIX', 
            'cnpj' => '07084548000106', 
            'contact_name' => 'IZAIAS', 
            'phone' => '91981775236', 
            'email' => 'exemple@exemple.com.br',
            'adms_daman_suppliers_types_id' => 1, 
            'supplier_status' => 1, 
            'created_at' => date('Y-m-d H:i:s'),],
        ];

        // Percorrer o array com dados que devem ser validados antes de cadastrar
        foreach ($suppliers as $supplier) {

            // Verifica se a página com o cnpj especificado já existe
            $existingRecord = $this->query('SELECT id FROM adms_daman_suppliers WHERE cnpj=:cnpj', ['cnpj' => $supplier['cnpj']])->fetch();

            // Se a página não existir, adiciona seus dados ao array $data
            if (!$existingRecord) {
                $data[] = [
                    'legal_name' => $supplier['legal_name'],
                    'trade_name' => $supplier['trade_name'],
                    'cnpj' => $supplier['cnpj'],
                    'contact_name' => $supplier['contact_name'],
                    'phone' => $supplier['phone'],
                    'email' => $supplier['email'],
                    'adms_daman_suppliers_types_id' => $supplier['adms_daman_suppliers_types_id'],
                    'supplier_status' => $supplier['supplier_status'],
                    'created_at' => date("Y-m-d H:i:s"),
                ];
            }
        }

        // Obtém a tabela 'adms_daman_suppliers' para inserir os registros
        $adms_daman_suppliers = $this->table('adms_daman_suppliers');

        // Insere os registros na tabela
        $adms_daman_suppliers->insert($data)->save();

    }
}
