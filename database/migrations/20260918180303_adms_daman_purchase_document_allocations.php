<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/*
 * Migration responsável por criar o rateio dos lançamentos financeiros
 * entre uma ou mais obras.
 *
 * Um lançamento permanece único no Contas a Pagar, preservando o valor
 * integral da NF-e, recibo, reembolso ou outro documento. Esta tabela
 * registra apenas a apropriação gerencial do custo entre as obras.
 *
 * O valor rateado também será utilizado futuramente como base proporcional
 * para distribuir juros, multas, descontos e demais efeitos financeiros
 * dos pagamentos entre as respectivas obras.
 */
final class AdmsDamanPurchaseDocumentAllocations extends AbstractMigration
{
    public function change(): void
    {
        $table =
            $this->table(
                'adms_daman_purchase_document_allocations'
            );

        $table

            /*
             * Lançamento financeiro ao qual este rateio pertence.
             *
             * Um mesmo lançamento pode possuir várias linhas de rateio,
             * uma para cada obra participante do custo.
             */
            ->addColumn(
                'adms_daman_purchase_document_id',
                'integer',
                [
                    'null' => false,
                    'signed' => false,
                    'comment' =>
                        'Lançamento financeiro ao qual o rateio pertence.',
                ]
            )

            /*
             * Obra que receberá esta parcela do custo.
             */
            ->addColumn(
                'adms_daman_project_id',
                'integer',
                [
                    'null' => false,
                    'signed' => false,
                    'comment' =>
                        'Obra à qual parte do valor do lançamento foi apropriada.',
                ]
            )

            /*
             * Valor principal apropriado para a obra.
             *
             * A soma das linhas de um lançamento deverá ser igual
             * ao valor total do documento.
             *
             * Exemplo:
             *
             * Documento = R$ 1.000,00
             *
             * Obra A = R$ 700,00
             * Obra B = R$ 300,00
             */
            ->addColumn(
                'allocated_amount',
                'decimal',
                [
                    'precision' => 15,
                    'scale' => 2,
                    'null' => false,
                    'default' => 0,
                    'comment' =>
                        'Valor do lançamento apropriado para esta obra.',
                ]
            )

            /*
             * Observação opcional sobre o rateio.
             *
             * Pode ser utilizada para explicar critérios especiais
             * de divisão do custo.
             */
            ->addColumn(
                'observation',
                'text',
                [
                    'null' => true,
                    'comment' =>
                        'Observação ou justificativa relacionada ao rateio.',
                ]
            )

            /*
             * Usuário responsável pela criação do rateio.
             */
            ->addColumn(
                'created_by',
                'integer',
                [
                    'null' => false,
                    'signed' => false,
                    'comment' =>
                        'Usuário responsável pelo cadastro do rateio.',
                ]
            )

            ->addColumn(
                'created_at',
                'timestamp',
                [
                    'default' => 'CURRENT_TIMESTAMP',
                    'comment' =>
                        'Data e hora de criação do rateio.',
                ]
            )

            ->addColumn(
                'updated_at',
                'timestamp',
                [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP',
                    'comment' =>
                        'Data e hora da última alteração do rateio.',
                ]
            );


        /*
         * Um lançamento não deve possuir duas linhas separadas
         * para a mesma obra.
         *
         * Se for necessário alterar o valor de uma obra,
         * atualizamos a linha existente.
         */
        $table->addIndex(
            [
                'adms_daman_purchase_document_id',
                'adms_daman_project_id',
            ],
            [
                'unique' => true,
                'name' =>
                    'uk_purchase_document_allocation_project',
            ]
        );


        /*
         * Índices auxiliares para consultas por lançamento e obra.
         */
        $table->addIndex(
            [
                'adms_daman_purchase_document_id',
            ],
            [
                'name' =>
                    'idx_purchase_document_allocations_document',
            ]
        );

        $table->addIndex(
            [
                'adms_daman_project_id',
            ],
            [
                'name' =>
                    'idx_purchase_document_allocations_project',
            ]
        );


        /*
         * Se o lançamento for excluído, seus rateios deixam
         * de possuir sentido e podem ser removidos em cascata.
         */
        $table->addForeignKey(
            'adms_daman_purchase_document_id',
            'adms_daman_purchase_documents',
            'id',
            [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' =>
                    'fk_purchase_allocations_document',
            ]
        );


        /*
         * Uma obra que possui movimentação financeira vinculada
         * não deve ser excluída.
         */
        $table->addForeignKey(
            'adms_daman_project_id',
            'adms_daman_projects',
            'id',
            [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
                'constraint' =>
                    'fk_purchase_allocations_project',
            ]
        );


        /*
         * Preservar a identificação de quem realizou o rateio.
         */
        $table->addForeignKey(
            'created_by',
            'adms_daman_users',
            'id',
            [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
                'constraint' =>
                    'fk_purchase_allocations_created_by',
            ]
        );


        $table->create();
    }
}