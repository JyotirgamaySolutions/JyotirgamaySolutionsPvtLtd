<?php
session_start();
include 'db_connect.php'; // Include database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $entered_otp = $_POST["otp"];

    // Retrieve OTP from database
    $stmt = $conn->prepare("SELECT otp FROM otp_verification WHERE email = ? AND created_at >= NOW() - INTERVAL 5 MINUTE");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($otp);
    $stmt->fetch();
    $stmt->close();

    if ($otp && $entered_otp == $otp) {
        $download_link = "https://www.jyotirgamay.com/downloads/talent_manual.pdf"; // Adjust the URL

        // Send the download link via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = "smtp.yourmailserver.com";
            $mail->SMTPAuth = true;
            $mail->Username = "your-email@example.com";
            $mail->Password = "your-email-password";
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom("your-email@example.com", "Jyotirgamay Support");
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Your Talent Manual Download Link";
            $mail->Body = "Click <a href='$download_link'>here</a> to download the Talent Manual.";

            $mail->send();
            echo json_encode(["status" => "success", "message" => "OTP verified! Download link sent to your email."]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error sending email: " . $mail->ErrorInfo]);
        }

        // Remove OTP after successful verification
        $stmt = $conn->prepare("DELETE FROM otp_verification WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid OTP!"]);
    }
}
?>
