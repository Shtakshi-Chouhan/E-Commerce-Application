<?php
namespace OS\helpers;

use Swift_Message;

final class Mailer{

    public static function send(Swift_Message $message): bool{
        //remove this code when you have smtp server details
        $mailTempFilePath = APP_BASE_PATH . '/mail_tmp/' . uniqid('email-') . '.html';
        file_put_contents($mailTempFilePath, $message->getBody());
        /*$transport = new \Swift_SmtpTransport();
        $transport->setHost(MAILER_CONFIG["server"])
            ->setPassword(MAILER_CONFIG["password"])
            ->setUsername(MAILER_CONFIG["username"])
            ->setPassword(MAILER_CONFIG["password"]);
        $mailer = new \Swift_Mailer($transport);
        $failedRecipients = null;
        $mailer->send($message, $failedRecipients);
        if(is_array($failedRecipients) and count($failedRecipients) > 0)
            return false;
        */
        return true;
    }

}