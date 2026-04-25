<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanPaymentMethods extends AbstractMigration
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
        // Verificar se a tabela 'adms_daman_payment_methods' não existe no banco de dados
        if (!$this->hasTable('adms_daman_payment_methods')) {
            // Cria a tabela 'adms_daman_adms_daman_orders_type'
            $table = $this->table('adms_daman_payment_methods');

            //Define as colunas da tabela
            $table->addColumn('name', 'string', ['null' => false, 'comment' => 'Forma de Pagamento'])
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp')
                ->addIndex(['name'], ['unique' => true, 'name' => 'idx_unique_name']) // Adiciona o índice único com nome específico
                ->create();
        }
    }

    /**
     * Reverte a criação da tabela AdmsDamanOrdersTypes
     * 
     * Este método é executado durante a reversão da migração para remover a tabela 'adms_daman_payment_methods' do banco de dados.
     * 
     * @return void
     */
    public function down(): void
    {
        // Remover a tabela 'adms_daman_payment_methods' do banco de dados
        $this->table('adms_daman_payment_methods')->drop()->save();
    }
}
