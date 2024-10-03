<?php
// Start session to store user login data
session_start();

// Database connection details
$servername = "localhost";
$dbUsername = "root";  // Database username (usually 'root' in XAMPP)
$dbPassword = "";      // Database password (empty in XAMPP)
$dbName = "artlink_entertainment"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Query to check if user exists
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    
    // Check if user exists and verify the password
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Login successful, set session variables
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // Save user's role
            header("Location: dashboard.php"); // Redirect to dashboard or home
            exit();
        } else {
            // Invalid password
            echo "Invalid username or password. Please try again.";
        }
    } else {
        // Invalid username
        echo "Invalid username or password. Please try again.";
    }
}

// Close connection
$conn->close();
?>
