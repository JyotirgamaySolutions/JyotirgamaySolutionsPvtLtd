<?php
session_start();
include 'db_connect.php'; // Include database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Validate email domain
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, "@Jyotirgamay.online")) {
        echo json_encode(["status" => "error", "message" => "Invalid email address!"]);
        exit();
    }

    // Generate OTP
    $otp = rand(100000, 999999);

    // Store OTP in database
    $stmt = $conn->prepare("INSERT INTO otp_verification (email, otp, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE otp=?, created_at=NOW()");
    $stmt->bind_param("sss", $email, $otp, $otp);
    $stmt->execute();
    $stmt->close();

    // Send OTP via email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = "smtp.yourmailserver.com"; // Change to your SMTP provider
        $mail->SMTPAuth = true;
        $mail->Username = "your-email@example.com"; // SMTP email
        $mail->Password = "your-email-password"; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("your-email@example.com", "Jyotirgamay Support");
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Your OTP for Talent Manual Download";
        $mail->Body = "Your OTP is: <strong>$otp</strong>. It is valid for 5 minutes.";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "OTP sent to your email."]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Could not send OTP. Error: " . $mail->ErrorInfo]);
    }
}
?>
