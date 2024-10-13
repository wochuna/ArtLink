<?php
session_start();
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
    
    // Check if the email exists in the users table
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $id = $row['id'];  // This is the user identifier

        // Generate a unique token for password reset
        $token = bin2hex(random_bytes(50));

        // Insert the reset attempt into the password_resets table with reset_id and user id (id)
        $stmt = $conn->prepare("INSERT INTO resets (id, token) VALUES (?, ?)");
        $stmt->bind_param("is", $id, $token);
        $stmt->execute();

        // Create the password reset link
        $resetLink = "http://yourdomain.com/reset-password.php?token=$token";

        // Email details
        $to = $email;
        $subject = "Password Reset Request";
        $message = "Click on the following link to reset your password: $resetLink";
        $headers = "From: no-reply@yourdomain.com";

        // Send the email
        if (mail($to, $subject, $message, $headers)) {
            $_SESSION['message'] = "Password reset link has been sent to your email.";
        } else {
            $_SESSION['message'] = "Failed to send email. Please try again.";
        }

    } else {
        // Email not found in users table
        $_SESSION['message'] = "Email not found.";
    }

    // Close statement
    $stmt->close();
}

// Close database connection
$conn->close();

// Redirect back to forgot password page
header("Location: forgot-password.php");
exit();
?>
