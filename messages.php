<?php
session_start();

// Turn on error reporting for debugging (enable in development only)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);


if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}


$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "artlink_entertainment";


$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);


if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $sender_id = (int)$_SESSION['id']; 
    $recipient_id = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : null; 
    $message = isset($_POST['message']) ? trim($_POST['message']) : null; 

    
    if ($recipient_id === null || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Recipient ID and message are required']);
        exit();
    }

    
    $recipient_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $recipient_stmt->bind_param("i", $recipient_id);
    $recipient_stmt->execute();
    $recipient_stmt->bind_result($recipient_username);
    $recipient_stmt->fetch();
    $recipient_stmt->close();

    
    if (empty($recipient_username)) {
        echo json_encode(['success' => false, 'message' => 'Recipient not found']);
        exit();
    }

  
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, sender_username, recipient_username, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $sender_username = $_SESSION['username']; 
    $stmt->bind_param("iisss", $sender_id, $recipient_id, $sender_username, $recipient_username, $message);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Message sending failed: ' . $stmt->error]);
    }

    
    $stmt->close();
} else {
   
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}


$conn->close();
?>