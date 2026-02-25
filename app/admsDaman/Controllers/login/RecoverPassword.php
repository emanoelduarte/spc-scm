<?php

namespace App\admsDaman\Controllers\login;

use App\admsDaman\Controllers\Services\GenerateKeyService;
use App\admsDaman\Helpers\SendEmailService;
use App\admsDaman\Models\Repository\ResetPasswordRepository;

/**
 * Classe RecoverPassword
 *
 * Esta classe é responsável por gerenciar o processo de recuperação de senha dos usuários. 
 * Ela gera uma chave de recuperação, formata a data de validade, atualiza as informações 
 * do usuário no repositório de senhas e envia um e-mail com as instruções para recuperação.
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 * @package App\adms\Controllers\Services
 */
class RecoverPassword
{
    /**
     * Método para recuperar senha
     *
     * Este método inicia o processo de recuperação de senha para um usuário. Ele gera uma 
     * chave de recuperação, configura o e-mail com as instruções de recuperação e envia 
     * o e-mail para o usuário. O método retorna um valor booleano indicando o sucesso 
     * ou falha do envio do e-mail.
     *
     * @param array $data Array contendo os dados do usuário e informações adicionais necessárias para a recuperação de senha.
     * @return bool Retorna true se o e-mail for enviado com sucesso, ou false em caso de falha.
     */
    public function recoverPassword(array $data): bool
    {
        // Instanciar o serviço para gerar a chave
        $valueGenerateKey = GenerateKeyService::generateKey();

        $data['key'] = $valueGenerateKey['key'];
        $data['recover_password'] = $valueGenerateKey['encryptedKey'];
        $data['validate_recover_password'] = date("Y-m-d H:i:s", strtotime('+1hour'));

        //Formatar hora separada da data
        $formattedTime = date("H:i:s", strtotime($data['validate_recover_password']));

        // Formatar data separada da hora
        $formattedDate = date("d/m/Y", strtotime($data['validate_recover_password']));

        // Instaciar o repositório para atualizar o recover_password no banco de dados
        $userUpdate = new ResetPasswordRepository();
        $result = $userUpdate->updateForgotPassword($data);

        // Acessa o IF se o repositório retornou TRUE
        if (!$result) {
            return false;
        }

        $name = explode(" ", $data['user']['name']);
        $firstName = $name[0];

        $subject = "Recuperar Senha";

        $url = "{$_ENV['URL_ADM']}reset-password/{$data['key']}";

        // Corpo do e-mail em HTML
        $body = "<p>Prezado(a) $firstName,</p>";
        $body .= "<p>Você solicitou alteração de senha.</p>";
        $body .= "<p>Para continuar o processo de recuperação de sua senha, clique no link abaixo ou cole o endereço no seu navegador: </p>";
        $body .= "<p><a href='$url'>$url</a></p>";
        $body .= "<p>Por questões de segurança esse código é válido somente até as $formattedTime do dia $formattedDate. Caso esse prazo esteja expirado, será necessário solicitar outro código.</p>";
        $body .= "<p>Se você não solicitou essa alteração, nenhuma ação é necessária. Sua senha permanecerá a mesma até que você ative este código.</p>";

        // Corpo alternativo do e-mail em texto plano
        $altBody = "Prezado(a) $firstName,\n\n";
        $altBody .= "Você solicitou alteração de senha.\n\n";
        $altBody .= "Para continuar o processo de recuperação de sua senha, clique no link abaixo ou cole o endereço no seu navegador: \n\n";
        $altBody .= "$url\n\n";
        $altBody .= "Por questões de segurança esse código é válido somente até as $formattedTime do dia $formattedDate. Caso esse prazo esteja expirado, será necessário solicitar outro código.\n\n";
        $altBody .= "Se você não solicitou essa alteração, nenhuma ação é necessária. Sua senha permanecerá a mesma até que você ative este código.\n\n";

        $sendEmail = new SendEmailService();
        $resultSendEmail = $sendEmail->sendEmail($data['user']['email'], $data['user']['name'], $subject, $body, $altBody);

        return $resultSendEmail;
    }
}
?>