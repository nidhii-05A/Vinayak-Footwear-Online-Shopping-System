<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Prevent duplicate function declarations
if (!function_exists('sendOTP')) {
    function sendOTP($email, $otp) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'anonymous1790543@gmail.com';
            $mail->Password = 'mkhmedsqjlstjkau';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom('anonymous1790543@gmail.com', 'Vinayak Footwear');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Vinayak Footwear - Your OTP';
            
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px;'>
                <div style='background: linear-gradient(135deg, #4169e1, #27408b); padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='color: white; margin: 0;'>👟 Vinayak Footwear</h1>
                </div>
                <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
                    <h2 style='color: #4169e1; margin-bottom: 20px;'>Hello! Welcome to Vinayak Footwear 👋</h2>
                    
                    <p style='color: #333; font-size: 16px; line-height: 1.6;'>
                        Thank you for registering with us!
                    </p>
                    
                    <div style='background: #fff; border: 2px dashed #4169e1; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px;'>
                        <p style='color: #4169e1; font-size: 14px; margin: 0 0 10px 0;'>Your OTP is:</p>
                        <p style='color: #4169e1; font-size: 32px; font-weight: bold; margin: 0; letter-spacing: 5px;'>$otp</p>
                    </div>
                    
                    <p style='color: #dc3545; font-size: 14px; text-align: center;'>
                        ⚠️ Valid only for 5 minutes
                    </p>
                    
                    <p style='color: #666; font-size: 14px; margin-top: 20px;'>
                        If you didn't request this OTP, please ignore this email.
                    </p>
                </div>
                <div style='text-align: center; padding: 15px; color: #999; font-size: 12px;'>
                    &copy; 2026 Vinayak Footwear. All rights reserved.
                </div>
            </div>
            ";
            
            $mail->AltBody = "Hello Welcome to Vinayak Footwear!\n\nThis is your OTP: $otp\n\nValid only for 5 minutes.";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('generateOTP')) {
    function generateOTP() {
        return rand(100000, 999999);
    }
}

if (!function_exists('sendPasswordResetLink')) {
    function sendPasswordResetLink($email, $link) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'anonymous1790543@gmail.com';
            $mail->Password = 'mkhmedsqjlstjkau';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom('anonymous1790543@gmail.com', 'Vinayak Footwear');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset - Vinayak Footwear';
            
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding:0px;'>
                <div style='background: linear-gradient(135deg, #4169e1, #27408b); padding: 0px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='color: white; margin: 0;'>👟 Vinayak Footwear</h1>
                </div>
                <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
                    <h2 style='color: #4169e1; margin-bottom: 20px;'>Password Reset Request 🔐</h2>
                    
                    <p style='color: #333; font-size: 16px; line-height: 1.6;'>
                        We received a request to reset your password.
                    </p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$link' style='background: linear-gradient(135deg, #4169e1, #27408b); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block;'>
                            Reset Password
                        </a>
                    </div>
                    
                    <p style='color: #666; font-size: 14px;'>
                        Or copy this link:<br>
                        <span style='color: #4169e1; word-break: break-all;'>$link</span>
                    </p>
                    
                    <div style='background: #fff3cd; padding: 15px; border-radius: 10px; margin-top: 20px;'>
                        <p style='color: #856404; font-size: 14px; margin: 0;'>
                            ⚠️ This link will expire in 1 hour. If you didn't request this, please ignore this email.
                        </p>
                    </div>
                </div>
                <div style='text-align: center; padding: 15px; color: #999; font-size: 12px;'>
                    &copy; 2026 Vinayak Footwear. All rights reserved.
                </div>
            </div>
            ";
            
            $mail->AltBody = "Password Reset - Vinayak Footwear\n\nClick the link to reset your password:\n$link\n\nThis link will expire in 1 hour.";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>