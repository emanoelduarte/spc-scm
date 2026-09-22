<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanPurchaseDocuments extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_purchase_documents')) {

            $table = $this->table('adms_daman_purchase_documents');

            $table
                ->addColumn('adms_daman_nfe_id', 'integer', [
                    'signed' => false,
                    'null' => true,
                    'comment' => 'NF-e que originou o lançamento',
                ])

                ->addColumn('adms_daman_project_id', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'Obra vinculada à compra',
                ])

                ->addColumn('adms_daman_user_id', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'Usuário responsável pela compra',
                ])

                ->addColumn('adms_daman_payment_method_id', 'integer', [
                    'signed' => false,
                    'null' => true,
                    'comment' => 'Condição de pagamento utilizada',
                ])

                ->addColumn('purchase_date', 'date', [
                    'null' => false,
                    'comment' => 'Data da compra',
                ])

                ->addColumn('observation', 'text', [
                    'null' => true,
                    'comment' => 'Observações do lançamento',
                ])

                ->addColumn('status', 'string', [
                    'limit' => 30,
                    'null' => false,
                    'default' => 'open',
                    'comment' => 'Situação do lançamento',
                ])

                ->addColumn('created_by', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'Usuário que criou o lançamento',
                ])

                ->addColumn('created_at', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                ])

                ->addColumn('updated_at', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP',
                ])

                ->addIndex(
                    ['adms_daman_nfe_id'],
                    [
                        'unique' => true,
                        'name' => 'idx_unique_purchase_document_nfe',
                    ]
                )

                ->addForeignKey(
                    'adms_daman_nfe_id',
                    'adms_daman_nfes',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )

                ->addForeignKey(
                    'adms_daman_project_id',
                    'adms_daman_projects',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )

                ->addForeignKey(
                    'adms_daman_user_id',
                    'adms_daman_users',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )

                ->addForeignKey(
                    'adms_daman_payment_method_id',
                    'adms_daman_payment_methods',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )

                ->addForeignKey(
                    'created_by',
                    'adms_daman_users',
                    'id',
                    [
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )

                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('adms_daman_purchase_documents')) {
            $this->table('adms_daman_purchase_documents')
                ->drop()
                ->save();
        }
    }
}