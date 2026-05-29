<?php
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/mail_config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendStatusEmail($toEmail, $subject, $messageBody) {
    global $mail_host, $mail_username, $mail_password, $mail_port, $mail_from_email, $mail_from_name;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $mail_host;
        $mail->SMTPAuth = true;
        $mail->Username = $mail_username;
        $mail->Password = $mail_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $mail_port;

        $mail->setFrom($mail_from_email, $mail_from_name);
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $messageBody;

        $mail->send();

        return [
            "success" => true,
            "message" => "Email sent successfully."
        ];

    } catch (Exception $e) {
        return [
            "success" => false,
            "message" => "Email sending failed: " . $mail->ErrorInfo
        ];
    }
}
?>