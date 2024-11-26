<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if artist_id is provided
if (!isset($_POST['artist_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Artist ID not provided']);
    exit();
}

$artistId = intval($_POST['artist_id']); // Sanitize the input
$userId = $_SESSION['id']; // Get the logged-in user's ID

// Database connection parameters
$servername = "localhost"; // Change if necessary
$dbUsername = "root";       // Your database username
$dbPassword = "";           // Your database password
$dbName = "artlink_entertainment"; // Your database name

// Create a database connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// Check the connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Prepare the SQL query to retrieve messages
$messagesSql = "SELECT m.message, m.timestamp, u.username 
                FROM messages m 
                JOIN users u ON m.sender_id = u.id 
                WHERE (m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?)
                ORDER BY m.timestamp ASC";

// Prepare the statement
$stmt = $conn->prepare($messagesSql);
$stmt->bind_param('iiii', $userId, $artistId, $artistId, $userId);
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch messages into an array
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Set the correct header for JSON response
header('Content-Type: application/json');

// Return the messages as JSON
echo json_encode(['success' => true, 'messages' => $messages]);
exit();