<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddXmlToAdmsDamanNfes extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('adms_daman_nfes');

        /*
         * XML completo/protocolado da NF-e.
         *
         * Usamos LONGTEXT porque um XML completo pode
         * ultrapassar com facilidade o limite de TEXT.
         */
        $table
            ->addColumn('xml_content', 'text', [
                'limit' => MysqlAdapter::TEXT_LONG,
                'null' => true,
                'after' => 'schema_name',
                'comment' => 'XML completo/protocolado da NF-e',
            ])

            /*
             * Schema correspondente ao XML completo recebido.
             *
             * Exemplo:
             * procNFe_v4.00.xsd
             */
            ->addColumn('xml_schema_name', 'string', [
                'limit' => 100,
                'null' => true,
                'after' => 'xml_content',
                'comment' => 'Schema do XML completo da NF-e',
            ])

            /*
             * Momento em que conseguimos obter o XML completo.
             */
            ->addColumn('xml_received_at', 'datetime', [
                'null' => true,
                'after' => 'xml_schema_name',
                'comment' => 'Data/hora em que o XML completo foi recebido',
            ])

            ->update();
    }


    public function down(): void
    {
        $table = $this->table('adms_daman_nfes');

        $table
            ->removeColumn('xml_received_at')
            ->removeColumn('xml_schema_name')
            ->removeColumn('xml_content')
            ->update();
    }
}