<?php 
// Start session to track logged-in user
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Generate a CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

// Fetch followed artists for the logged-in user
$followedArtistsSql = "SELECT followed_id FROM fans WHERE follower_id = ?";
$followedArtistsStmt = $conn->prepare($followedArtistsSql);
$followedArtistsStmt->bind_param('i', $id);
$followedArtistsStmt->execute();
$followedArtistsResult = $followedArtistsStmt->get_result();
$followedArtists = [];
while ($row = $followedArtistsResult->fetch_assoc()) {
    $followedArtists[] = $row['followed_id'];
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .section { display: none; }
        .active { display: block; }

        /* Chat message styles */
        #chat-box {
            border: 1px solid #ccc;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .message {
            margin: 5px 0;
            padding: 8px;
            border-radius: 5px;
            max-width: 70%;
            clear: both;
        }
        .message.sent {
            background-color: #dcf8c6; /* Light green for sent messages */
            margin-left: auto; /* Align to the right */
            text-align: right;
        }
        .message.received {
            background-color: #f1f0f0; /* Light gray for received messages */
            margin-right: auto; /* Align to the left */
            text-align: left;
        }
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
            <div class="artist-list">
                <?php
                if (!empty($followedArtists)) {
                    foreach ($followedArtists as $followed_id) {
                        $artistSql = "SELECT u.username, a.profile_picture FROM users u
                                      JOIN artwork a ON u.id = a.id WHERE u.id = ?";
                        $artistStmt = $conn->prepare($artistSql);
                        $artistStmt->bind_param('i', $followed_id);
                        $artistStmt->execute();
                        $artistResult = $artistStmt->get_result();
                        while ($artist = $artistResult->fetch_assoc()) {
                            ?>
                            <div class="artist">
                                <img src="uploads/<?php echo htmlspecialchars($artist['profile_picture']); ?>" alt="Artist Picture">
                                <h3><?php echo htmlspecialchars($artist['username']); ?></h3>
                            </div>
                            <?php
                        }
                    }
                } else {
                    echo "You are not following any artists.";
                }
                ?>
            </div>
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
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"> <!-- Add CSRF token -->
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
                            <form id="follow-form-<?php echo $artist['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"> <!-- CSRF Token -->
                                <button type="button" onclick="followArtist(<?php echo $artist['id']; ?>)" data-following="false">Follow</button>
                            </form>
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
    </div>
</div>

<script>
function showSection(sectionId) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(sectionId).classList.add('active');
}

// Function to follow artist
function followArtist(artistId) {
    const csrfToken = document.querySelector(`#follow-form-${artistId} input[name="csrf_token"]`).value; // Get CSRF token

    fetch('follow.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            artist_id: artistId,
            csrf_token: csrfToken // Pass CSRF token here
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const followButton = document.querySelector(`#follow-form-${artistId} button`);
            followButton.innerText = 'Following';
            followButton.disabled = true; // Disable button after following
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
        // Initialize WebSocket connection
        const socket = new WebSocket('ws://localhost:8080');

        socket.onopen = function() {
            console.log("Connected to WebSocket server");
        };

        socket.onmessage = function(event) {
            const chatBox = document.getElementById('chat-box');
            const receivedMessage = document.createElement('div');
            receivedMessage.className = 'message received';
            receivedMessage.textContent = event.data;
            chatBox.appendChild(receivedMessage);
            chatBox.scrollTop = chatBox.scrollHeight;
        };

        // Function to send a message
        document.getElementById('message-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const messageInput = document.getElementById('message');
            const message = messageInput.value;
            const artistId = document.getElementById('artist_id').value;

            // Send message via WebSocket
            socket.send(JSON.stringify({ artist_id: artistId, message }));

            // Display sent message
            const chatBox = document.getElementById('chat-box');
            const sentMessage = document.createElement('div');
            sentMessage.className = 'message sent';
            sentMessage.textContent = message;
            chatBox.appendChild(sentMessage);
            chatBox.scrollTop = chatBox.scrollHeight;

            messageInput.value = '';
        });
    </script>
</body>
</html>

</script>
</body>
</html>
