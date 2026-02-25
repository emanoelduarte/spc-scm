<?php

namespace App\admsDaman\Helpers;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Classe de serviço responsável por enviar e-mail
 */
class SendEmailService
{
    public static function sendEmail(string $email, string $name, string $subject, string $body, $altBody)
    {
        // Instaciar a classe PHPMailer
        $mail = new PHPMailer(true);

        // Definir as configurações do servidor
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Habilitar saída de depuração detalhada
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();                                            //Enviar usando SMTP
        $mail->Host       = $_ENV['MAIL_HOST'];                     //Configure o servidor SMTP para enviar através dele
        $mail->SMTPAuth   = true;                                   //Habilitar autenticação SMTP
        $mail->Username   = $_ENV['MAIL_USERNAME'];                     //SMTP username
        $mail->Password   = $_ENV['MAIL_PASSWORD'];                               //SMTP password
        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];             //Habilitar ciptografia implícita VERIFICAR NA HOOSPEDAGEM QUAL smtp DEVE SER UTILIZADO
        $mail->Port       = $_ENV['MAIL_PORT'];

        // Cnfiguração e definição do recipiente 
        //Recipients
        $mail->setFrom($_ENV['EMAIL_ADM'], $_ENV['EMAIL_ADM']);
        $mail->addAddress($email, $name);     // Adicionar um destinatário

        // Conteúdo
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody;

        $mail->send();

        // Chamar método para salvar o erro no log
        GenerateLog::generateLog("info", "E-mail enviado com sucesso!", ['email' => $email, 'subject' => $subject]);

        return true;

        try {
        } catch (Exception $e) {

            // Chamar método para salvar o erro no log
            GenerateLog::generateLog("error", "Não foi possível enviar o e-mail.", ['email' => $email, 'error' => $e->getMessage()]);
            return false;
        }
    }
}