<?php
session_start();

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

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// CSRF token verification
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

// Get user ID and artist ID
$follower_id = $_SESSION['id'];
$followed_id = isset($_POST['artist_id']) ? (int)$_POST['artist_id'] : 0;

// Validate artist_id
if ($followed_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid artist ID']);
    exit();
}

// Check if follow relationship already exists
$checkSql = "SELECT * FROM fans WHERE follower_id = ? AND followed_id = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param('ii', $follower_id, $followed_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You are already following this artist']);
    exit();
}

// Insert new follow relationship in fans table
$insertSql = "INSERT INTO fans (follower_id, followed_id, follow_date) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($insertSql);
$stmt->bind_param('ii', $follower_id, $followed_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'You are now following the artist!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to follow artist']);
}

$stmt->close();
$conn->close();
?>
