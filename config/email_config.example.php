<?php
/**
 * Email Configuration Example
 * 
 * SETUP INSTRUCTIONS:
 * 1. Copy this file to email_config.php
 * 2. Replace placeholder values with your actual credentials
 * 3. Get Gmail App Password:
 *    - Go to https://myaccount.google.com/security
 *    - Enable "2-Step Verification"
 *    - Go to https://myaccount.google.com/apppasswords
 *    - Select "Mail" and generate password
 *    - Copy the 16-digit password (no spaces)
 */

// Email Configuration - Gmail SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Replace with your Gmail
define('SMTP_PASSWORD', 'your-app-password-here'); // Replace with your Gmail App Password
define('SMTP_FROM_EMAIL', 'your-email@gmail.com'); // Replace with your Gmail
define('SMTP_FROM_NAME', 'Your Institution Feedback System');

/**
 * Send OTP via Email using Gmail SMTP
 */
function sendOTPEmail($email, $otp, $user_name = '') {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/PHPMailer-master/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/PHPMailer-master/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/PHPMailer-master/src/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer;
    
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = 'tls';
    $mail->Port = SMTP_PORT;
    
    // Email settings
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($email);
    $mail->isHTML(true);
    
    $mail->Subject = 'Password Reset OTP - Feedback System';
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { padding: 20px; background: #f4f6f9; }
            .content { background: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: auto; }
            .otp { font-size: 32px; color: #007bff; letter-spacing: 5px; font-weight: bold; text-align: center; padding: 20px; background: #f0f8ff; border-radius: 5px; margin: 20px 0; }
            .footer { color: #666; font-size: 12px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='content'>
                <h2 style='color: #2c3e50;'>Password Reset Request</h2>
                <p>Dear " . ($user_name ?: 'User') . ",</p>
                <p>You have requested to reset your password. Please use the following OTP to proceed:</p>
                <div class='otp'>$otp</div>
                <p><strong style='color: #e74c3c;'>⏰ This OTP is valid for 10 minutes only.</strong></p>
                <p>If you did not request this password reset, please ignore this email.</p>
                <div class='footer'>
                    <p>Best regards,<br>Feedback System Team</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return $mail->send();
}

/**
 * Send OTP based on contact method
 */
function sendOTP($contact_info, $otp, $contact_type = 'email', $user_name = '') {
    if ($contact_type === 'email' || filter_var($contact_info, FILTER_VALIDATE_EMAIL)) {
        return sendOTPEmail($contact_info, $otp, $user_name);
    } else {
        return false;
    }
}
?>
