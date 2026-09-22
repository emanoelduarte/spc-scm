<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddManualFieldsToAdmsDamanPurchaseDocuments
    extends AbstractMigration
{
    public function up(): void
    {
        /*
         * Alterar a tabela de lançamentos financeiros
         * para também permitir compras avulsas.
         */
        $table = $this->table(
            'adms_daman_purchase_documents'
        );


        /*
         * Fornecedor utilizado quando o lançamento
         * não estiver vinculado a uma NF-e importada.
         */
        $table->addColumn(
            'adms_daman_supplier_id',
            'integer',
            [
                'null' => true,
                'signed' => false,
                'after' => 'adms_daman_nfe_id',
                'comment' =>
                    'Fornecedor da compra avulsa; NULL quando originada de NF-e',
            ]
        );


        /*
         * Tipo do documento informado manualmente.
         *
         * Exemplos:
         * CUPOM
         * RECIBO
         * NOTA
         * OUTRO
         */
        $table->addColumn(
            'document_type',
            'string',
            [
                'limit' => 30,
                'null' => true,
                'after' => 'adms_daman_supplier_id',
                'comment' =>
                    'Tipo de documento da compra avulsa',
            ]
        );


        /*
         * Número ou identificação do documento.
         */
        $table->addColumn(
            'document_number',
            'string',
            [
                'limit' => 100,
                'null' => true,
                'after' => 'document_type',
                'comment' =>
                    'Número do documento da compra avulsa',
            ]
        );


        /*
         * Data de emissão ou data do documento.
         */
        $table->addColumn(
            'document_date',
            'date',
            [
                'null' => true,
                'after' => 'document_number',
                'comment' =>
                    'Data do documento da compra avulsa',
            ]
        );


        /*
         * Valor total da compra avulsa.
         *
         * Para lançamentos vinculados a NF-e, o valor
         * continuará vindo de adms_daman_nfes.total_value.
         */
        $table->addColumn(
            'total_value',
            'decimal',
            [
                'precision' => 15,
                'scale' => 2,
                'null' => true,
                'after' => 'document_date',
                'comment' =>
                    'Valor total da compra avulsa',
            ]
        );


        /*
         * Relacionar o fornecedor cadastrado no sistema.
         */
        $table->addForeignKey(
            'adms_daman_supplier_id',
            'adms_daman_suppliers',
            'id',
            [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
                'constraint' =>
                    'fk_purchase_documents_supplier',
            ]
        );


        $table->update();
    }


    public function down(): void
    {
        $table = $this->table(
            'adms_daman_purchase_documents'
        );


        /*
         * Primeiro remover a FK.
         */
        $table->dropForeignKey(
            'adms_daman_supplier_id'
        );


        /*
         * Depois remover as colunas adicionadas.
         */
        $table
            ->removeColumn(
                'adms_daman_supplier_id'
            )
            ->removeColumn(
                'document_type'
            )
            ->removeColumn(
                'document_number'
            )
            ->removeColumn(
                'document_date'
            )
            ->removeColumn(
                'total_value'
            )
            ->update();
    }
}