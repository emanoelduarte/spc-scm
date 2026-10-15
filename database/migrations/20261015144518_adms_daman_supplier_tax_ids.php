<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanSupplierTaxIds extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('adms_daman_supplier_tax_ids')) {
            $table = $this->table('adms_daman_supplier_tax_ids');

            $table
                ->addColumn('adms_daman_supplier_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                ])
                ->addColumn('cnpj', 'string', [
                    'limit' => 14,
                    'null' => false,
                    'comment' => 'CNPJ normalizado, somente números',
                ])
                ->addColumn('is_primary', 'boolean', [
                    'null' => false,
                    'default' => 0,
                ])
                ->addColumn('status', 'boolean', [
                    'null' => false,
                    'default' => 1,
                ])
                ->addColumn('created_at', 'timestamp', [
                    'null' => false,
                    'default' => 'CURRENT_TIMESTAMP',
                ])
                ->addColumn('updated_at', 'timestamp', [
                    'null' => true,
                    'default' => null,
                ])
                ->addIndex(['cnpj'], [
                    'unique' => true,
                    'name' => 'uq_supplier_tax_ids_cnpj',
                ])
                ->addIndex(['adms_daman_supplier_id'], [
                    'name' => 'idx_supplier_tax_ids_supplier',
                ])
                ->addForeignKey(
                    'adms_daman_supplier_id',
                    'adms_daman_suppliers',
                    'id',
                    [
                        'delete' => 'CASCADE',
                        'update' => 'CASCADE',
                    ]
                )
                ->create();
        }

        $this->execute("
            INSERT INTO adms_daman_supplier_tax_ids
            (
                adms_daman_supplier_id,
                cnpj,
                is_primary,
                status,
                created_at,
                updated_at
            )
            SELECT
                supplier.id,
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(supplier.cnpj, '.', ''),
                            '/',
                            ''
                        ),
                        '-',
                        ''
                    ),
                    ' ',
                    ''
                ),
                1,
                1,
                NOW(),
                NOW()
            FROM
                adms_daman_suppliers AS supplier
            WHERE
                LENGTH(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(supplier.cnpj, '.', ''),
                                '/',
                                ''
                            ),
                            '-',
                            ''
                        ),
                        ' ',
                        ''
                    )
                ) = 14
                AND NOT EXISTS (
                    SELECT 1
                    FROM adms_daman_supplier_tax_ids AS supplier_tax_id
                    WHERE supplier_tax_id.cnpj =
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(supplier.cnpj, '.', ''),
                                    '/',
                                    ''
                                ),
                                '-',
                                ''
                            ),
                            ' ',
                            ''
                        )
                )
        ");

        $this->execute("
            UPDATE
                adms_daman_purchase_documents AS purchase_document
            INNER JOIN
                adms_daman_nfes AS nfe
                ON nfe.id = purchase_document.adms_daman_nfe_id
            INNER JOIN
                adms_daman_supplier_tax_ids AS supplier_tax_id
                ON supplier_tax_id.cnpj =
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(nfe.issuer_cnpj, '.', ''),
                                '/',
                                ''
                            ),
                            '-',
                            ''
                        ),
                        ' ',
                        ''
                    )
            SET
                purchase_document.adms_daman_supplier_id =
                    supplier_tax_id.adms_daman_supplier_id
            WHERE
                purchase_document.adms_daman_supplier_id IS NULL
        ");
    }

    public function down(): void
    {
        if ($this->hasTable('adms_daman_supplier_tax_ids')) {
            $this->table('adms_daman_supplier_tax_ids')->drop()->save();
        }
    }
}
