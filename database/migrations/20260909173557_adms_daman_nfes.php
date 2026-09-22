<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Essa migrations será responsável por criar a tabela adms_daman_nfes, que armazenará informações sobre as notas fiscais eletrônicas (NFes) emitidas em nome da empresa.
 */
final class AdmsDamanNfes extends AbstractMigration
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
        if (!$this->hasTable('adms_daman_nfes')) {

            // Definir as colunas da tabela
            $table = $this->table('adms_daman_nfes');

            $table
                
                ->addColumn('nsu', 'string', [
                'limit' => 20,
                'null' => true,
                'comment' => 'Número Sequencial Único utilizado na distribuição de DF-e',
            ])

            ->addColumn('access_key', 'string', [
                'limit' => 44,
                'null' => true,
                'comment' => 'Chave de acesso da NF-e com 44 dígitos',
            ])

            ->addColumn('nfe_number', 'string', [
                'limit' => 20,
                'null' => true,
                'comment' => 'Número da NF-e',
            ])

            ->addColumn('series', 'string', [
                'limit' => 10,
                'null' => true,
                'comment' => 'Série da NF-e',
            ])

            ->addColumn('issuer_cnpj', 'string', [
                'limit' => 20,
                'null' => true,
                'comment' => 'CNPJ do fornecedor emitente da NF-e',
            ])

            ->addColumn('issuer_name', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'Razão social ou nome do fornecedor emitente',
            ])

            ->addColumn('issue_date', 'datetime', [
                'null' => true,
                'comment' => 'Data e hora de emissão da NF-e',
            ])

            ->addColumn('total_value', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'null' => true,
                'comment' => 'Valor total da NF-e',
            ])

            ->addColumn('schema_name', 'string', [
                'limit' => 100,
                'null' => true,
                'comment' => 'Schema do documento retornado pela distribuição DF-e',
            ])

            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'Data e hora em que a NF-e foi registrada no sistema',
            ])

            ->addColumn('status', 'string', [
                'limit' => 30,
                'default' => 'authorized',
                'comment' => 'Situação atual da NF-e: authorized ou cancelled',
            ])

            ->addColumn('cancelled_at', 'datetime', [
                'null' => true,
                'comment' => 'Data e hora do cancelamento da NF-e',
            ])

            ->addIndex(['nsu'], [
                'unique' => true,
                'name' => 'uk_adms_daman_nfes_nsu',
            ])

            ->addIndex(['access_key'], [
                'unique' => true,
                'name' => 'uk_adms_daman_nfes_access_key',
            ])
                ->create();

        }
    }
}
