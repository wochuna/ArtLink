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

// Fetch messages for the selected artist (if applicable)
$messages = [];
if (isset($_POST['artist_id'])) {
    $artistId = $_POST['artist_id'];

    $messagesSql = "SELECT m.message, m.timestamp, u.username 
                    FROM messages m 
                    JOIN users u ON m.sender_id = u.id 
                    WHERE (m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?)
                    ORDER BY m.timestamp ASC";
    
    $messagesStmt = $conn->prepare($messagesSql);
    $messagesStmt->bind_param('iiii', $id, $artistId, $artistId, $id);
    $messagesStmt->execute();
    $messagesResult = $messagesStmt->get_result();

    while ($row = $messagesResult->fetch_assoc()) {
        $messages[] = $row;
    }
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $artistId = $_POST['artist_id'];

    // Insert message into the database
    $insertSql = "INSERT INTO messages (sender_id, recipient_id, message) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param('iis', $id, $artistId, $message);
    $insertStmt->execute();

    echo json_encode(['success' => true, 'message' => 'Message sent']);
    exit();
}

// Follow artist logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artist_id'])) {
    $artistId = $_POST['artist_id']; // The ID of the artist to follow
    $followerId = $id; // This is the logged-in user's ID

    // Ensure both the follower and followed IDs are valid
    if ($followerId === $artistId) {
        echo json_encode(['success' => false, 'message' => 'You cannot follow yourself.']);
        exit();
    }

    // Check if the artist exists
    $checkArtistSql = "SELECT id FROM users WHERE id = ?";
    $checkArtistStmt = $conn->prepare($checkArtistSql);
    $checkArtistStmt->bind_param('i', $artistId);
    $checkArtistStmt->execute();
    $checkArtistResult = $checkArtistStmt->get_result();

    if ($checkArtistResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Artist does not exist']);
        exit();
    }

    // Insert follow relationship into fans table
    $insertFollowSql = "INSERT INTO fans (follower_id, followed_id) VALUES (?, ?)";
    $insertFollowStmt = $conn->prepare($insertFollowSql);
    $insertFollowStmt->bind_param('ii', $followerId, $artistId); // Correctly bind the follower and followed IDs

    if ($insertFollowStmt->execute()) {
        // Fetch the followed artist details to return
        $artistDetailsSql = "SELECT id, username, profile_picture FROM users WHERE id = ?";
        $artistDetailsStmt = $conn->prepare($artistDetailsSql);
        $artistDetailsStmt->bind_param('i', $artistId);
        $artistDetailsStmt->execute();
        $artistDetailsResult = $artistDetailsStmt->get_result();
        $artistDetails = $artistDetailsResult->fetch_assoc();

        echo json_encode(['success' => true, 'message' => 'Followed successfully', 'artist' => $artistDetails]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to follow artist']);
    }
    exit();
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
        <a href="#" onclick="showSection('browse-artists')">Browse Artists</a>
        <a href="#" onclick="showSection('followed-artists')">Followed Artists</a>
        <a href="#" onclick="showSection('events')">Events</a>
        <a href="#" onclick="showSection('messages')">Messages</a>
    </div>

    <!-- Main content area -->
    <div class="content">
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
                                <button onclick="startChat(<?php echo $artist['id']; ?>)">Message</button>
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
                <?php
                // Display fetched messages if they exist
                if (!empty($messages)) {
                    foreach ($messages as $message) {
                        $class = ($message['sender_id'] == $id) ? 'sent' : 'received';
                        ?>
                        <div class="message <?php echo $class; ?>">
                            <strong><?php echo htmlspecialchars($message['username']); ?>:</strong> <?php echo htmlspecialchars($message['message']); ?>
                            <span class="timestamp"><?php echo date('Y-m-d H:i', strtotime($message['timestamp'])); ?></span>
                        </div>
                        <?php
                    }
                }
                ?>
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
                    <img src="<?php echo htmlspecialchars($artist['profile_picture']); ?>" alt="Artist Picture">
                    <h3><?php echo htmlspecialchars($artist['username']); ?></h3>
                    <form id="follow-form-<?php echo $artist['id']; ?>" method="POST">
                        <input type="hidden" name="artist_id" value="<?php echo $artist['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"> <!-- CSRF Token -->
                        <button type="button" onclick="followArtist(<?php echo $artist['id']; ?>)">Follow</button>
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
</div>
<script>
    // WebSocket connection
const conn = new WebSocket('ws://localhost:8080');

conn.onopen = function() {
    console.log("WebSocket connection established!");
};

conn.onerror = function(error) {
    console.error("WebSocket Error: ", error);
};

conn.onclose = function() {
    console.log("WebSocket connection closed");
};

conn.onmessage = function(event) {
    const data = JSON.parse(event.data);
    const chatBox = document.getElementById('chat-box');
    
    if (chatBox) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message received';
        messageDiv.innerHTML = `<strong>${data.sender_username}:</strong> ${data.message} <span class="timestamp">${new Date(data.timestamp).toLocaleString()}</span>`;
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    } else {
        console.error("Chat box not found");
    }
};

// Show specific section
function showSection(sectionId) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => section.classList.remove('active'));
    const sectionToShow = document.getElementById(sectionId);
    if (sectionToShow) {
        sectionToShow.classList.add('active');
    } else {
        console.error(`Section with ID ${sectionId} not found`);
    }
}

// Start chat with artist
function startChat(artistId) {
    document.getElementById('artist_id').value = artistId;
    showSection('messages');

    fetch('messages.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            artist_id: artistId,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const chatBox = document.getElementById('chat-box');
            if (chatBox) {
                chatBox.innerHTML = '';
                data.messages.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message ' + (msg.sender_id == <?php echo $id; ?> ? 'sent' : 'received');
                    messageDiv.innerHTML = `<strong>${msg.username}:</strong> ${msg.message} <span class="timestamp">${new Date(msg.timestamp).toLocaleString()}</span>`;
                    chatBox.appendChild(messageDiv);
                });
                chatBox.scrollTop = chatBox.scrollHeight;
            } else {
                console.error("Chat box not found");
            }
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error('Error:', err));
}

// Follow artist
function followArtist(artistId) {
    console.log("Follow button clicked for artist ID:", artistId);

    fetch('follow.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            artist_id: artistId,
            csrf_token: document.querySelector(`#follow-form-${artistId} input[name="csrf_token"]`).value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("You are now following this artist!");
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => console.error('Error:', err));
}

// Send message
const messageForm = document.getElementById('message-form');
if (messageForm) {
    messageForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const messageInput = document.getElementById('message');
        const message = messageInput.value;
        const artistId = document.getElementById('artist_id').value;
        const chatBox = document.getElementById('chat-box');
        
        if (chatBox) {
            const sentMessage = document.createElement('div');
            sentMessage.className = 'message sent';
            sentMessage.innerHTML = `<strong>You:</strong> ${message}`;
            chatBox.appendChild(sentMessage);
            chatBox.scrollTop = chatBox.scrollHeight;
        } else {
            console.error("Chat box not found");
        }

        const messageData = {
            sender_id: <?php echo $id; ?>,
            recipient_id: artistId,
            sender_username: "You",
            message: message,
            timestamp: new Date().toISOString()
        };
        
        conn.send(JSON.stringify(messageData));
        messageInput.value = '';
    });
} else {
    console.error("Message form not found");
}
</script>
</body>
</html>