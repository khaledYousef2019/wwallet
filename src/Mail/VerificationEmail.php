<?php

namespace App\Mail;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

//class VerificationEmail
//{
//    /**
//     * @throws Exception
//     */
//    public static function send($to, $subject, $message)
//    {
//        $mail = new PHPMailer(true);
//        try{
//        $mail->isSMTP();
//        $mail->Host = $_ENV['MAIL_HOST'];
//        $mail->SMTPAuth = true;
//        $mail->Username = $_ENV['MAIL_USERNAME'];
//        $mail->Password = $_ENV['MAIL_PASSWORD'];
//        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
//        $mail->Port = $_ENV['SMTP_PORT'];
//        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['APP_NAME']);
//        $mail->addAddress($to);
//        $mail->isHTML(true);
//        $mail->Subject = $subject;
//        $mail->Body = $message;
//
//        $mail->send();
//        return true;
//    } catch (Exception $e) {
//    // Handle exceptions
//return false;
//}
//    }
//}
