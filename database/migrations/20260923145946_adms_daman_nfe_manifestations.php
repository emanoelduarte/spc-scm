<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdmsDamanNfeManifestations extends AbstractMigration
{
    public function change(): void
    {
        $table =
            $this->table(
                'adms_daman_nfe_manifestations'
            );


        $table
            ->addColumn(
                'adms_daman_nfe_id',
                'integer',
                [
                    'null' => false,
                    'signed' => false,
                ]
            )

            /*
             * 210210 = Ciência da Emissão
             *
             * Futuramente poderá armazenar outros
             * eventos do destinatário.
             */
            ->addColumn(
                'event_type',
                'string',
                [
                    'limit' => 6,
                    'null' => false,
                ]
            )

            ->addColumn(
                'status',
                'string',
                [
                    'limit' => 30,
                    'null' => false,
                    'default' => 'registered',
                ]
            )

            /*
             * cStat retornado pela SEFAZ.
             *
             * Ex.:
             * 135 = Evento registrado
             * 573 = Evento duplicado
             */
            ->addColumn(
                'cstat',
                'string',
                [
                    'limit' => 10,
                    'null' => true,
                ]
            )

            ->addColumn(
                'message',
                'string',
                [
                    'limit' => 255,
                    'null' => true,
                ]
            )

            /*
             * Protocolo do evento, quando disponível.
             */
            ->addColumn(
                'protocol',
                'string',
                [
                    'limit' => 30,
                    'null' => true,
                ]
            )

            ->addColumn(
                'registered_at',
                'datetime',
                [
                    'null' => true,
                ]
            )

            ->addColumn(
                'created_at',
                'datetime',
                [
                    'null' => false,
                ]
            )

            ->addColumn(
                'updated_at',
                'datetime',
                [
                    'null' => true,
                ]
            )

            /*
             * Uma mesma NF-e não deve possuir
             * duas manifestações do mesmo tipo
             * registradas localmente.
             */
            ->addIndex(
                [
                    'adms_daman_nfe_id',
                    'event_type',
                ],
                [
                    'unique' => true,
                    'name' => 'uniq_nfe_manifestation_event',
                ]
            )

            ->addForeignKey(
                'adms_daman_nfe_id',
                'adms_daman_nfes',
                'id',
                [
                    'delete' => 'CASCADE',
                    'update' => 'NO_ACTION',
                ]
            )

            ->create();
    }
}