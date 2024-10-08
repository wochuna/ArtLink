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

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists and verify the password
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Debugging: Print the fetched password (hashed) from database
        echo "Stored Hashed Password: " . $row['password'] . "<br>";

        if (password_verify($password, $row['password'])) {
            // Login successful, set session variables
            $_SESSION['username'] = $row['username'];
            header("Location: dashboard.php"); // Redirect to dashboard or home
            exit();
        } else {
            // Invalid password
            $_SESSION['error'] = "Invalid username or password. Please try again.";
            header("Location: login.php"); // Redirect back to login
            exit();
        }
    } else {
        // Invalid username
        $_SESSION['error'] = "Invalid username or password. Please try again.";
        header("Location: login.php"); // Redirect back to login
        exit();
    }
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
