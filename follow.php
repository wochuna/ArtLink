<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Generate a CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$artist_id = $_POST['artist_id'];
$follower_id = $_SESSION['id']; // Logged in user's ID

// Database connection
$conn = new mysqli("localhost", "root", "", "artlink_entertainment");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for existing follow
$checkFollowSql = "SELECT * FROM fans WHERE follower_id = ? AND followed_id = ?";
$checkFollowStmt = $conn->prepare($checkFollowSql);
$checkFollowStmt->bind_param('ii', $follower_id, $artist_id);
$checkFollowStmt->execute();
$checkFollowResult = $checkFollowStmt->get_result();

if ($checkFollowResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You are already following this artist.']);
    exit();
}

// Insert follow record
$stmt = $conn->prepare("INSERT INTO fans (follower_id, followed_id, follow_date) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $follower_id, $artist_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Successfully followed the artist.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error following artist: ' . $stmt->error]);
}

// Close statements and connection
$stmt->close();
$checkFollowStmt->close();
$conn->close();
?>