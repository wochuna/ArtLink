<?php
session_start();

// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Sanitize input values
    $recipient_id = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }

    // Check if the message is empty
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
        exit();
    }

    // Insert the message into the database
    $sender_id = $_SESSION['id'];
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $sender_id, $recipient_id, $message);

    if ($stmt->execute()) {
        // Fetch the recipient's username to display in the chat interface
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i", $recipient_id);
        $stmt->execute();
        $stmt->bind_result($recipient_username);
        $stmt->fetch();

        // Close statement after fetching
        $stmt->close();

        // Return the response with message details
        echo json_encode([
            'success' => true,
            'message' => $message,
            'username' => $recipient_username
        ]);
    } else {
        // If there's an error executing the query, return an error message
        echo json_encode(['success' => false, 'message' => 'Message sending failed: ' . $stmt->error]);
    }

    // Close database connection
    $conn->close();
    exit();
}

// If the request is not POST, respond with an error
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>
