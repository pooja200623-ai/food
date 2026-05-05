<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'lib/PHPMailer/Exception.php';
require 'lib/PHPMailer/PHPMailer.php';
require 'lib/PHPMailer/SMTP.php';

/**
 * Sends an OTP email to the user.
 * 
 * @param string $toEmail The recipient's email address.
 * @param string $toName The recipient's name.
 * @param string $otp The OTP code to send.
 * @return array Array with 'success' (bool) and 'message' (string).
 */
function sendOTPEmail($toEmail, $toName, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code - Crave';
        
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h1 style='color: #ff4757;'>Crave</h1>
                </div>
                <h2 style='color: #333;'>Hello $toName,</h2>
                <p style='font-size: 16px; color: #666;'>Your verification code for logging into Crave is:</p>
                <div style='background: #f4f4f4; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #ff4757; border-radius: 5px; margin: 20px 0;'>
                    $otp
                </div>
                <p style='font-size: 14px; color: #999;'>This code will expire in 10 minutes. If you did not request this, please ignore this email.</p>
                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 12px; color: #aaa; text-align: center;'>&copy; " . date('Y') . " Crave Food Delivery. All rights reserved.</p>
            </div>
        ";
        $mail->AltBody = "Hello $toName,\n\nYour verification code for logging into Crave is: $otp\n\nThis code will expire in 10 minutes.";

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"];
    }
}
?>
