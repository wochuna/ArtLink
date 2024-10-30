<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Database connection details
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

// Validate CSRF token
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

// Get artist ID from the request
$artist_id = intval($_POST['artist_id']);
$follower_id = $_SESSION['id'];

// Check if the follow relationship already exists
$sql = "SELECT * FROM fans WHERE follower_id = ? AND followed_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $follower_id, $artist_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Already following this artist']);
    exit();
}

// Insert follow relationship
$insertSql = "INSERT INTO fans (follower_id, followed_id, follow_date) VALUES (?, ?, NOW())";
$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param('ii', $follower_id, $artist_id);

if ($insertStmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to follow artist']);
}

$conn->close();
?>
