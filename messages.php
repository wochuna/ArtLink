<?php
session_start();

// Turn on error reporting for debugging (enable in development only)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Database connection parameters
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "artlink_entertainment";

// Create a new database connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// Check if the database connection was successful
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the logged-in user's ID
    $sender_id = (int)$_SESSION['id']; // Ensure this is an integer
    $recipient_id = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : null; // Get recipient ID from POST data
    $message = isset($_POST['message']) ? trim($_POST['message']) : null; // Get message from POST data

    // Validate recipient ID and message
    if ($recipient_id === null || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Recipient ID and message are required']);
        exit();
    }

    // Get recipient username
    $recipient_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $recipient_stmt->bind_param("i", $recipient_id);
    $recipient_stmt->execute();
    $recipient_stmt->bind_result($recipient_username);
    $recipient_stmt->fetch();
    $recipient_stmt->close();

    // Check if recipient username was found
    if (empty($recipient_username)) {
        echo json_encode(['success' => false, 'message' => 'Recipient not found']);
        exit();
    }

    // Insert the message into the database
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, sender_username, recipient_username, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $sender_username = $_SESSION['username']; // Assuming the username is stored in the session
    $stmt->bind_param("iisss", $sender_id, $recipient_id, $sender_username, $recipient_username, $message);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Message sending failed: ' . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    // If the request is not POST, respond with an error
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close database connection
$conn->close();
?>