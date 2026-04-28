<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanOrderComments extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Variável para receber os dados a serem inseridos
        $data = [];

        $comments = [

            [
                'adms_daman_order_id' => 1, //Id do pedido
                'adms_daman_user_id' => 3, // Id do usuário editor
                'type' => 'auto', // tipo de comentário
                'action' => 'update_item', // Ação realizada no sistema
                'field' => 'description', // Campo editado
                'old_value' => 'cabo 123',  // Valor antigo editado
                'new_value' => 'cabo 1234', // Novo valor
                'adms_daman_order_item_id' => 1, // Id do item
                'comment' => null,
                'created_at' => date('Y-m-d H:i:s'),
            ],

        ];

        // Percorrer o array com dados que devem ser validados antes de cadastrar
        foreach ($comments as $comment) {

            $data[] = [
                'adms_daman_order_id' => $comment['adms_daman_order_id'],
                'adms_daman_user_id' => $comment['adms_daman_user_id'],
                'type' => $comment['type'],
                'action' => $comment['action'],
                'field' => $comment['field'],
                'old_value' => $comment['old_value'],
                'new_value' => $comment['new_value'],
                'adms_daman_order_item_id' => $comment['adms_daman_order_item_id'],
                'comment' => $comment['comment'],
                'created_at' => $comment['created_at'],
            ];
        }

        // Obtém a tabela 'adms_daman_order_comments' para inserir os registros
        $adms_daman_order_comments = $this->table('adms_daman_order_comments');

        // Insere os registros na tabela
        $adms_daman_order_comments->insert($data)->save();
    }
}
