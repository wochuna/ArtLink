<?php
session_start();
//PHPMailer
require 'C:/xampp/htdocs/artlink/PHPMailer-master/src/PHPMailer.php';
require 'C:/xampp/htdocs/artlink/PHPMailer-master/src/SMTP.php';
require 'C:/xampp/htdocs/artlink/PHPMailer-master/src/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "artlink_entertainment";

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $id = $row['id']; // Use id not user_id

        // Generate a unique token
        $token = bin2hex(random_bytes(50));

        // Insert token into the password_resets table
        $stmt = $conn->prepare("INSERT INTO resets (id, token) VALUES (?, ?)");
        $stmt->bind_param("is", $id, $token);
        $stmt->execute();

        // Create the reset link
        $resetLink = "http://yourdomain.com/reset-password.php?token=$token";

        // Initialize PHPMailer
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'artlinkentertainment@gmail.com'; // Your email
        $mail->Password = 'nrlq ivfu bsnw yodv'; // Replace with your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email details
        $mail->setFrom('artlinkentertainment@gmail.com', 'ArtLink Entertainment');
        $mail->addAddress($email);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "Click on the following link to reset your password: $resetLink";

        // Send the email
        if ($mail->send()) {
            $_SESSION['message'] = "Password reset link sent to your email.";
        } else {
            $_SESSION['message'] = "Failed to send email. Please try again.";
        }

    } else {
        $_SESSION['message'] = "Email not found.";
    }

    $stmt->close();
}

$conn->close();
header("Location: forgot-password.php");
exit();
?>
