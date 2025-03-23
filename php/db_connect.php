<?php
$host = "localhost"; // Change this if using a remote server
$dbname = "otp_verification"; // Replace with your actual database name
$username = "root"; // Default for XAMPP
$password = ""; // Default for XAMPP (empty password)

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
