<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCheckedToAdmsDamanNfes extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('adms_daman_nfes');

        $table
            ->addColumn('is_checked', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'status',
                'comment' => 'Indica se a entrada da NF-e já foi conferida',
            ])
            ->update();
    }
}