<?php

require __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getVerificationCode(): string
{
   $code  = random_int(1000, 9999);
   return (string)$code;
}

function sendVerificationMail(string $to, string $fromEmail, string $fromName, string $subject, string $messageBody): bool
{
    $mail = new PHPMailer(true); // Enable exceptions

    // TEMPORARILY ENABLE DEBUG OUTPUT FOR DIAGNOSIS
    $mail->SMTPDebug = 0; // Show debug output (0 = off, 1 = client, 2 = client and server)
    $mail->Debugoutput = 'html'; // Or 'echo' if running from CLI

    try {
        // Server settings for your SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // >>> IMPORTANT: Replace with your actual Gmail email and App Password <<<
        $mail->Username   = 'walletdigital90@gmail.com';
        $mail->Password   = 'kiui omlg blur fwgc'; // Use your Gmail App Password here
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $messageBody;
        $mail->AltBody = strip_tags($messageBody);

        $mail->send();
        error_log("Verification email sent successfully to: " . $to);
        return true;

    } catch (Exception $e) {
        error_log("Failed to send verification email to {$to}. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}


$verificationCode = getVerificationCode();
$subject = 'ABC Digital Wallet - Email Verification Code';
$message_body = "
    <html>
    <head>
        <title>Email Verification</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
            h2 { color: #0056b3; }
            .code { font-size: 28px; color: #007bff; text-align: center; letter-spacing: 8px; font-weight: bold; padding: 10px; border: 1px dashed #007bff; display: inline-block; margin: 15px auto; background-color: #e6f2ff; border-radius: 5px; }
            .footer { margin-top: 20px; font-size: 0.9em; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Hello,</h2>
            <p>Thank you for registering with ABC Digital Wallet!</p>
            <p>Your 4-digit verification code is:</p>
            <div style='text-align: center;'>
                <span class='code'>{$verificationCode}</span>
            </div>
            <p>This code is valid for 15 minutes. Please enter it on the verification page to activate your account.</p>
            <p>If you did not request this, please ignore this email.</p>
            <p class='footer'>Best regards,<br>The ABC Digital Wallet Team</p>
        </div>
    </body>
    </html>
";
//$toEmail = 'shshhasan.00@gmail.com';
$toEmail = 'mdfoysal15220@gmail.com';
$fromEmail = 'walletdigital90@gmail.com'; 
$fromName = 'ABC Digital Wallet Support';

if (sendVerificationMail($toEmail, $fromEmail, $fromName, $subject, $message_body)) {
    echo "<h1>Verification email sent successfully to {$toEmail}.</h1>";
} else {
    echo "<h1>Failed to send verification email to {$toEmail}. Please check server logs for details.</h1>";
}

?>