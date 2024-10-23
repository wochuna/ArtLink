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

$id = $_SESSION['id']; // Logged-in user's id

// Fetch all artists from the artwork database
$sql = "SELECT u.id, u.username, a.profile_picture FROM users u
        JOIN artwork a ON u.id = a.id";  
$artistResult = $conn->query($sql);

// Fetch partnerships related to the logged-in user (either as artist or institution)
$sqlPartnership = "SELECT p.id, u1.username AS artist_name, u2.username AS institution_name, p.start_date, p.end_date, p.description
                   FROM partnerships p
                   JOIN users u1 ON p.artist_id = u1.id
                   JOIN users u2 ON p.institution_id = u2.id
                   WHERE p.institution_id = ? OR p.artist_id = ?";

$stmtPartnership = $conn->prepare($sqlPartnership);
$stmtPartnership->bind_param("ii", $id, $id);
$stmtPartnership->execute();
$partnershipResult = $stmtPartnership->get_result();

if (!$artistResult) {
    die("Query failed: " . $conn->error); // Debugging line
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution Dashboard</title>
    <link rel="stylesheet" href="art.css"> 
    <link rel="stylesheet" href="audience.css">
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
        <a href="#" onclick="showSection('partnership')">Partnership</a> <!-- Updated to Partnership -->
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
            <div id="chat-box">
                <!-- Display chat messages here -->
            </div>
            <form id="message-form">
                <input type="hidden" id="artist_id" name="artist_id" value="">
                <input type="text" id="message" placeholder="Type your message here" required>
                <button type="submit">Send</button>
            </form>
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
                            <button onclick="startChat(<?php echo $artist['id']; ?>)">Message</button>
                        </div>
                        <?php
                    }
                } else {
                    echo "No artists found.";
                }
                ?>
            </div>
        </div>

        <!-- Partnership Section -->
        <div id="partnership" class="section">
            <h2>Partnerships</h2>
            <ul>
                <?php
                if ($partnershipResult->num_rows > 0) {
                    while ($partnership = $partnershipResult->fetch_assoc()) {
                        echo "<li>Partnership between <strong>" . htmlspecialchars($partnership['artist_name']) . "</strong> and <strong>" . htmlspecialchars($partnership['institution_name']) . "</strong> 
                        starting on <strong>" . htmlspecialchars($partnership['start_date']) . "</strong></li>";
                    }
                } else {
                    echo "No partnerships found.";
                }
                ?>
            </ul>
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

// Function to start a chat
function startChat(artistId) {
    document.getElementById('artist_id').value = artistId;
    showSection('messages');
}

// Handling message sending
document.getElementById('message-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const artistId = document.getElementById('artist_id').value;
    const message = document.getElementById('message').value;

    fetch('send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            artist_id: artistId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Append message to chat box
            const chatBox = document.getElementById('chat-box');
            const newMessage = document.createElement('div');
            newMessage.textContent = message;
            chatBox.appendChild(newMessage);

            // Clear input field
            document.getElementById('message').value = '';
        } else {
            alert("Failed to send message: " + data.message);
        }
    });
});
</script>

</body>
</html>
