<?php
session_start();

// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "Script started<br>";

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo "User not logged in<br>";
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
    echo "Database connection failed: " . $conn->connect_error . "<br>";
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST request received<br>";

    // Temporarily set static values for testing
    $sender_id = 13; // Replace with a valid user ID
    $recipient_id = 19; // Replace with a valid recipient ID
    $message = "Test message"; // Static test message

    // Debugging output
    echo "Sender ID: $sender_id, Recipient ID: $recipient_id, Message: $message<br>";

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
    $stmt->bind_param("iisss", $sender_id, $recipient_id, $sender_username, $recipient_username, $message);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Message sending failed: ' . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
    // Close database connection
    $conn->close();
    exit();
}

// If the request is not POST, respond with an error
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>