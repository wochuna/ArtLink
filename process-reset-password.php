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

// Check if the form is submitted via POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = mysqli_real_escape_string($conn, $_POST['token']);
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Verify the token and get the user id from password_resets table
    $stmt = $conn->prepare("SELECT id FROM resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $id = $row['id'];  // Get the user id (from password_resets table)

        // Update the user's password in the users table
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $newPassword, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Password reset successfully.";
            header("Location: login.php");  // Redirect to login page
        } else {
            $_SESSION['message'] = "Failed to reset password.";
            header("Location: reset-password.php?token=$token");  // Redirect back to reset-password page
        }

        // Delete the password reset entry (identified by token) after it is used
        $stmt = $conn->prepare("DELETE FROM resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

    } else {
        // Token is invalid or expired
        $_SESSION['message'] = "Invalid or expired token.";
        header("Location: forgot-password.php");  // Redirect to forgot password page
    }
}

// Close the statement and the connection
$stmt->close();
$conn->close();
exit();
?>
