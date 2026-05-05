<?php
session_start();
require 'db.php';

if(isset($_POST['email'])){

    $email = $_POST['email'];
    $otp = rand(100000,999999);

    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;

    $subject = "Your OTP Code - Vinayak Footwear";
    $message = "Your OTP is: " . $otp;
    $headers = "From: no-reply@vinayak.com";

    if(mail($email, $subject, $message, $headers)){
        echo "OTP_SENT";
    } else {
        echo "ERROR_SENDING";
    }
}
?>