<?php 
// Start session
session_start();

// Database connection details
$servername = "localhost";
$dbUsername = "root";  // Database username (usually 'root' in XAMPP)
$dbPassword = "";      // Database password (empty in XAMPP)
$dbName = "artlink_entertainment"; // Your database name

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security

    // Check if the username or email already exists
    $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkUser->bind_param('ss', $username, $email);
    $checkUser->execute();
    $checkUser->store_result();

    if ($checkUser->num_rows > 0) {
        echo "Username or email already exists.";
    } else {
        // Insert the new user into the users table
        $stmt = $conn->prepare("INSERT INTO users (role, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $role, $username, $email, $password);

        if ($stmt->execute()) {
            // Get the newly inserted user's ID
            $user_id = $stmt->insert_id;

            // Insert a new artist profile if the role is 'artist'
            if ($role === 'artist') {
                $stmt = $conn->prepare("INSERT INTO artwork (id, bio, profile_picture, x_link, instagram_link, facebook_link, linkedin_link) VALUES (?, '', '', '', '', '', '')");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
            }

            // Redirect based on the role
            if ($role === 'artist') {
                $_SESSION['id'] = $user_id;  // Set user ID in session
                header("Location: artist.php"); // Redirect to artist profile page
                exit();
            } elseif ($role === 'audience') {
                header("Location: audience.php");
                exit();
            } elseif ($role === 'institution') {
                header("Location: institution.php");
                exit();
            }
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $checkUser->close();
}

$conn->close();
?>
