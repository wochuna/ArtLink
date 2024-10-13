<?php 
// Start session to track logged-in user
session_start();
echo "Current Session ID: " . session_id(); // Debugging line
echo "User ID: " . (isset($_SESSION['id']) ? $_SESSION['id'] : 'Not set'); // Debugging line

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

// Check if a user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$id = $_SESSION['id']; // Logged in user's id

// Handle Follow action (assuming it's done via POST request)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['artist_id'])) {
    $artist_id = $_POST['artist_id'];

    // Insert follow relationship into the fans database
    $sql = "INSERT INTO fans (follower_id, followed_id, follow_date) VALUES (?, ?, NOW())";  
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $artist_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to follow the artist']);
    }
    $stmt->close();
    exit();
}

// Fetch all artists from the artwork database
$sql = "SELECT u.id, u.username, a.profile_picture FROM users u
        JOIN artwork a ON u.id = a.id";  
$artistResult = $conn->query($sql);

if (!$artistResult) {
    die("Query failed: " . $conn->error); // Debugging line
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audience Dashboard</title>
    <link rel="stylesheet" href="art.css"> 
    <link rel="stylesheet" href="audience.css">
</head>
<body>

<nav>
        <img src="spice-it-up/Capture.PNG" alt="ArtLink Logo" class="logo"> <!-- Replace with your logo image -->
        <label class="logo">ArtLink Entertainment</label>
        <ul>
            <li><a href="index.php">HOME</a></li>
            <li><a href="about.php">ABOUT</a></li>
            <li><a href="signup.php">SIGN UP</a></li>
            <li><a href="login.php">LOGIN</a></li>
        </ul>
    </nav>

    <div class="container">
        <!-- Sidebar for navigation -->
        <div class="sidebar">
            <a href="#">Profile</a>
            <a href="#">Followed Artists</a>
            <a href="#">Events</a>
        </div>

        <!-- Main content area -->
        <div class="content">
            <h2>Browse Artists</h2>

            <div class="artist-list">
                <?php
                // Display artists in a grid format
                if ($artistResult->num_rows > 0) {
                    while ($artist = $artistResult->fetch_assoc()) {
                        ?>
                        <div class="artist">
                            <img src="uploads/<?php echo $artist['profile_picture']; ?>" alt="Artist Picture">
                            <h3><?php echo htmlspecialchars($artist['username']); ?></h3>
                            <button onclick="followArtist(<?php echo $artist['id']; ?>)">Follow</button>
                        </div>
                        <?php
                    }
                } else {
                    echo "No artists found.";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
    function followArtist(artistId) {
        fetch('audience.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                artist_id: artistId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("You are now following the artist!");
            } else {
                alert("Failed to follow artist: " + data.message);
            }
        });
    }
    </script>
</body>
</html>


