<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAdmsDamanNfeSyncTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        // Acessa o IF caso a tabela ainda não exista
        if (!$this->hasTable('adms_daman_nfe_sync')) {

            // Definir as colunas da tabela
            $table = $this->table('adms_daman_nfe_sync');

            $table

                ->addColumn('cnpj', 'string', [
                    'limit' => 14,
                    'comment' => 'CNPJ utilizado na consulta de distribuição de DF-e',
                ])

                ->addColumn('ult_nsu', 'string', [
                    'limit' => 20,
                    'default' => '000000000000000',
                    'comment' => 'Último NSU processado pelo sistema',
                ])

                ->addColumn('max_nsu', 'string', [
                    'limit' => 20,
                    'default' => '000000000000000',
                    'comment' => 'Maior NSU informado pela SEFAZ',
                ])

                ->addColumn('last_cstat', 'string', [
                    'limit' => 10,
                    'null' => true,
                    'comment' => 'Último código de status retornado pela SEFAZ',
                ])

                ->addColumn('last_message', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'comment' => 'Última mensagem retornada pela SEFAZ',
                ])

                ->addColumn('last_sync_at', 'datetime', [
                    'null' => true,
                    'comment' => 'Data e hora da última consulta realizada',
                ])

                ->addColumn('created_at', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'comment' => 'Data de criação do registro',
                ])

                ->addColumn('updated_at', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP',
                    'comment' => 'Data da última atualização do registro',
                ])

                ->addIndex(['cnpj'], [
                    'unique' => true,
                    'name' => 'uk_adms_daman_nfe_sync_cnpj',
                ])

                ->create();
        }
    }
}
