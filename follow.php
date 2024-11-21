<?php
// Start the session
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Validate the CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

// Get the follower ID from the session and artist ID from the POST request
$follower_id = $_SESSION['id'];
$artist_id = isset($_POST['artist_id']) ? (int)$_POST['artist_id'] : 0;

// Validate the artist ID
if ($artist_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid artist ID']);
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "artlink_entertainment");

// Check the database connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Check if the user is already following the artist
$checkFollowSql = "SELECT * FROM fans WHERE follower_id = ? AND followed_id = ?";
$checkFollowStmt = $conn->prepare($checkFollowSql);
if (!$checkFollowStmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
    $conn->close();
    exit();
}

$checkFollowStmt->bind_param('ii', $follower_id, $artist_id);
$checkFollowStmt->execute();
$checkFollowResult = $checkFollowStmt->get_result();

if ($checkFollowResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You are already following this artist.']);
    $checkFollowStmt->close();
    $conn->close();
    exit();
}
$checkFollowStmt->close();

// Insert the follow action into the fans table
$insertFollowSql = "INSERT INTO fans (follower_id, followed_id, follow_date) VALUES (?, ?, NOW())";
$insertFollowStmt = $conn->prepare($insertFollowSql);
if (!$insertFollowStmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare insert statement: ' . $conn->error]);
    $conn->close();
    exit();
}

$insertFollowStmt->bind_param("ii", $follower_id, $artist_id);
if ($insertFollowStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Successfully followed the artist.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error following artist: ' . $insertFollowStmt->error]);
}

// Close the statement and connection
$insertFollowStmt->close();
$conn->close();
?>
