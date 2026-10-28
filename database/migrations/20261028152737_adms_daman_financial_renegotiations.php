<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Estrutura de renegociações financeiras.
 *
 * Uma renegociação consolida uma ou mais parcelas já existentes em um novo
 * compromisso financeiro. As parcelas de origem permanecem no histórico e
 * passam para o status RN (Renegociada); o novo compromisso é persistido no
 * mesmo motor de Contas a Pagar, em adms_daman_purchase_documents.
 */
final class AdmsDamanFinancialRenegotiations extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_financial_renegotiations')) {
            $table = $this->table('adms_daman_financial_renegotiations');

            $table
                ->addColumn('adms_daman_supplier_id', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'Fornecedor/credor da renegociação.',
                ])
                ->addColumn('adms_daman_purchase_document_id', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'Novo lançamento financeiro gerado pela renegociação.',
                ])
                ->addColumn('agreement_number', 'string', [
                    'limit' => 100,
                    'null' => false,
                    'comment' => 'Número ou referência do acordo.',
                ])
                ->addColumn('agreement_date', 'date', [
                    'null' => false,
                    'comment' => 'Data em que a renegociação foi formalizada.',
                ])
                ->addColumn('original_amount', 'decimal', [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'comment' => 'Principal remanescente consolidado das parcelas de origem.',
                ])
                ->addColumn('interest_amount', 'decimal', [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'default' => 0,
                    'comment' => 'Juros incorporados ao acordo.',
                ])
                ->addColumn('penalty_amount', 'decimal', [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'default' => 0,
                    'comment' => 'Multas incorporadas ao acordo.',
                ])
                ->addColumn('discount_amount', 'decimal', [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'default' => 0,
                    'comment' => 'Desconto concedido na renegociação.',
                ])
                ->addColumn('renegotiated_amount', 'decimal', [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'comment' => 'Valor final do novo compromisso financeiro.',
                ])
                ->addColumn('status', 'string', [
                    'limit' => 20,
                    'null' => false,
                    'default' => 'active',
                    'comment' => 'Situação da renegociação: active ou cancelled.',
                ])
                ->addColumn('observation', 'text', [
                    'null' => true,
                    'comment' => 'Observações do acordo.',
                ])
                ->addColumn('created_by', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'Usuário que cadastrou a renegociação.',
                ])
                ->addColumn('created_at', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                ])
                ->addColumn('updated_at', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP',
                ])
                ->addIndex(['adms_daman_purchase_document_id'], [
                    'unique' => true,
                    'name' => 'uk_fin_renegotiation_purchase_document',
                ])
                ->addIndex(['adms_daman_supplier_id', 'agreement_number'], [
                    'unique' => true,
                    'name' => 'uk_fin_renegotiation_supplier_agreement',
                ])
                ->addIndex(['status'], [
                    'name' => 'idx_fin_renegotiation_status',
                ])
                ->addForeignKey(
                    'adms_daman_supplier_id',
                    'adms_daman_suppliers',
                    'id',
                    ['delete' => 'RESTRICT', 'update' => 'CASCADE']
                )
                ->addForeignKey(
                    'adms_daman_purchase_document_id',
                    'adms_daman_purchase_documents',
                    'id',
                    [
                        // A exclusão direta do lançamento de renegociação é proibida.
                        'delete' => 'RESTRICT',
                        'update' => 'CASCADE',
                    ]
                )
                ->addForeignKey(
                    'created_by',
                    'adms_daman_users',
                    'id',
                    ['delete' => 'RESTRICT', 'update' => 'CASCADE']
                )
                ->create();
        }

        if (!$this->hasTable('adms_daman_financial_renegotiation_origins')) {
            $table = $this->table('adms_daman_financial_renegotiation_origins');

            $table
                ->addColumn('adms_daman_financial_renegotiation_id', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'Renegociação à qual a parcela de origem pertence.',
                ])
                ->addColumn('adms_daman_purchase_installment_id', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'Parcela original incorporada ao acordo.',
                ])
                ->addColumn('source_original_amount', 'decimal', [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'comment' => 'Valor original da parcela no momento da renegociação.',
                ])
                ->addColumn('source_paid_principal', 'decimal', [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'default' => 0,
                    'comment' => 'Principal já pago da parcela antes da renegociação.',
                ])
                ->addColumn('included_principal_amount', 'decimal', [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'comment' => 'Saldo de principal efetivamente incorporado ao acordo.',
                ])
                ->addColumn('previous_status', 'string', [
                    'limit' => 10,
                    'null' => false,
                    'comment' => 'Status armazenado da parcela antes de receber RN.',
                ])
                ->addColumn('created_at', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                ])
                ->addIndex(['adms_daman_purchase_installment_id'], [
                    'unique' => true,
                    'name' => 'uk_fin_renegotiation_origin_installment',
                ])
                ->addIndex(['adms_daman_financial_renegotiation_id'], [
                    'name' => 'idx_fin_renegotiation_origins_renegotiation',
                ])
                ->addForeignKey(
                    'adms_daman_financial_renegotiation_id',
                    'adms_daman_financial_renegotiations',
                    'id',
                    ['delete' => 'CASCADE', 'update' => 'CASCADE']
                )
                ->addForeignKey(
                    'adms_daman_purchase_installment_id',
                    'adms_daman_purchase_installments',
                    'id',
                    ['delete' => 'RESTRICT', 'update' => 'CASCADE']
                )
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('adms_daman_financial_renegotiation_origins')) {
            $this->table('adms_daman_financial_renegotiation_origins')
                ->drop()
                ->save();
        }

        if ($this->hasTable('adms_daman_financial_renegotiations')) {
            $this->table('adms_daman_financial_renegotiations')
                ->drop()
                ->save();
        }
    }
}
