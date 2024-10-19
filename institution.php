<?php 
// Start session to track logged-in user
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

// Check if a user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$id = $_SESSION['id']; // Logged in user's id

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
    <link rel="stylesheet" href="institution.css">
    <style>
        .section { display: none; }
        .active { display: block; }
    </style>
</head>
<body>

<nav>
    <img src="spice-it-up/Capture.PNG" alt="ArtLink Logo" class="logo"> 
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
        <a href="#" onclick="showSection('profile')">Profile</a>
        <a href="#" onclick="showSection('followed-artists')">Followed Artists</a>
        <a href="#" onclick="showSection('events')">Events</a>
        <a href="#" onclick="showSection('messages')">Messages</a>
        <a href="#" onclick="showSection('browse-artists')">Browse Artists</a>
    </div>

    <!-- Main content area -->
    <div class="content">
        <!-- Profile Section -->
        <div id="profile" class="section">
            <h2>Your Profile</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! This is your profile page.</p>
        </div>

        <!-- Followed Artists Section -->
        <div id="followed-artists" class="section">
            <h2>Followed Artists</h2>
            <p>This is the followed artists section. List the artists you are following here.</p>
        </div>

        <!-- Events Section -->
        <div id="events" class="section">
            <h2>Upcoming Events</h2>
            <p>This is the events section. Add upcoming events details here.</p>
        </div>

        <!-- Messages Section -->
        <div id="messages" class="section">
            <h2>Your Messages</h2>
            <p>This is the messages section. Display user messages here.</p>
        </div>

        <!-- Browse Artists Section (default view) -->
        <div id="browse-artists" class="section active">
            <h2>Browse Artists</h2>
            <div class="artist-list">
                <?php
                // Display artists in a grid format
                if ($artistResult->num_rows > 0) {
                    while ($artist = $artistResult->fetch_assoc()) {
                        ?>
                        <div class="artist">
                            <img src="uploads/<?php echo htmlspecialchars($artist['profile_picture']); ?>" alt="Artist Picture">
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
</div>

<script>
// JavaScript to toggle between sections
function showSection(sectionId) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(sectionId).classList.add('active');
}

// Function to follow artist
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
