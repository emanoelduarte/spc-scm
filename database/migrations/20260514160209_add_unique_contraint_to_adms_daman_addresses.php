<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Classe para adicionar indices unicos a coluna 'zip_code', 'number', 'complement'
 */
final class AddUniqueContraintToAdmsDamanAddresses extends AbstractMigration
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
         // Acessar o if quando a tabela existir no banco de dados
        if ($this->hasTable('adms_daman_addresses')) {
            // Alterar a tabela para adicionar incices unicos
            $table = $this->table('adms_daman_addresses');

            // Adicionar indíces únicos às colunas email e username
            // 'name' => 'uq_address' - nomear o índice único     
            $table->addIndex(['zip_code', 'number', 'complement'], ['unique' => true, 'name' => 'uq_address'])
            ->update();
        }
    }

        /**
     * Metodo down() para reverter a migração (caso necessário)
     */
    public function down(): void
    {
        // Acessar o if quando a tabela existir no banco de dados
        if ($this->hasTable('adms_daman_addresses')) {
            // Indicar a tabela para remover os índices únicos das colunas 'zip_code', 'number', 'complement'

            $table = $this->table('adms_daman_addresses');

            // Remover os indíces únicos
            $table->removeIndexByName('uq_address')
                  ->update();
        }

    }
}
