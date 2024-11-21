<?php 

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "artlink_entertainment";


$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$id = $_SESSION['id']; 


$sql = "SELECT u.id, u.username, a.profile_picture FROM users u
        JOIN artwork a ON u.id = a.id";  
$artistResult = $conn->query($sql);

if (!$artistResult) {
    die("Query failed: " . $conn->error); 
}


$followedArtistsSql = "SELECT followed_id FROM fans WHERE follower_id = ?";
$followedArtistsStmt = $conn->prepare($followedArtistsSql);
$followedArtistsStmt->bind_param('i', $id);
$followedArtistsStmt->execute();
$followedArtistsResult = $followedArtistsStmt->get_result();
$followedArtists = [];
while ($row = $followedArtistsResult->fetch_assoc()) {
    $followedArtists[] = $row['followed_id'];
}


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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $artistId = $_POST['artist_id'];

    
    $insertSql = "INSERT INTO messages (sender_id, recipient_id, message) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param('iis', $id, $artistId, $message);
    $insertStmt->execute();

    echo json_encode(['success' => true, 'message' => 'Message sent']);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artist_id'])) {
    $artistId = $_POST['artist_id']; 
    $followerId = $id; 

    if ($followerId === $artistId) {
        echo json_encode(['success' => false, 'message' => 'You cannot follow yourself.']);
        exit();
    }

    
    $checkArtistSql = "SELECT id FROM users WHERE id = ?";
    $checkArtistStmt = $conn->prepare($checkArtistSql);
    $checkArtistStmt->bind_param('i', $artistId);
    $checkArtistStmt->execute();
    $checkArtistResult = $checkArtistStmt->get_result();

    if ($checkArtistResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Artist does not exist']);
        exit();
    }

   
    $insertFollowSql = "INSERT INTO fans (follower_id, followed_id) VALUES (?, ?)";
    $insertFollowStmt = $conn->prepare($insertFollowSql);
    $insertFollowStmt->bind_param('ii', $followerId, $artistId);

    if ($insertFollowStmt->execute()) {
      
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

$events = [];
if (!empty($followedArtists)) {
    $followedArtistsIds = implode(',', $followedArtists); 

    // SQL query to fetch events for followed artists, including event_time and event_link
    $eventsSql = "SELECT e.id, e.event_name, e.event_date, e.event_time, e.description, e.event_link 
                  FROM events e
                  WHERE e.artist_id IN ($followedArtistsIds)
                  ORDER BY e.event_date DESC";
    
    // Execute the query
    $eventsResult = $conn->query($eventsSql);

    // Fetch the results into the $events array
    if ($eventsResult) {
        while ($row = $eventsResult->fetch_assoc()) {
            $events[] = $row;
        }
    }
}

// Assuming $artistResult contains the list of artists the audience follows (retrieved earlier in the code)
while ($artist = $artistResult->fetch_assoc()) {
    $artistId = isset($artist['id']) ? $artist['id'] : 'null'; 
    $username = htmlspecialchars($artist['username']);
    $profilePicture = htmlspecialchars($artist['profile_picture']);

    echo "<div class='artist'>
            <img src='$profilePicture' alt='$username' />
            <p>$username</p>
            <button onclick='startChat($artistId)'>Message</button>
            <button onclick='followArtist($artistId)'>Follow</button>
          </div>";
}

// Display upcoming events section
echo "<h3>Upcoming Events from Artists You Follow</h3>";
if (!empty($events)) {
    echo "<ul>";
    foreach ($events as $event) {
        // Display event details
        echo "<li>
                <strong>" . htmlspecialchars($event['event_name']) . "</strong><br>
                Date: " . htmlspecialchars($event['event_date']) . "<br>
                Time: " . htmlspecialchars($event['event_time']) . "<br>
                Description: " . htmlspecialchars($event['description']) . "<br>";
        
        // Display event link if it exists
        if (!empty($event['event_link'])) {
            echo "<a href='" . htmlspecialchars($event['event_link']) . "' target='_blank'>Event Link</a>";
        }
        
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No events found.</p>";
}


$conn->close();
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
            background-color: #dcf8c6;
            margin-left: auto; 
            text-align: right;
        }
        .message.received {
            background-color: #f1f0f0;
            margin-right: auto; 
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
   
    <div class="sidebar">
        <a href="#" onclick="showSection('browse-artists')">Browse Artists</a>
        <a href="#" onclick="showSection('followed-artists')">Followed Artists</a>
        <a href="#" onclick="showSection('events')">Events</a>
        <a href="#" onclick="showSection('messages')">Messages</a>
    </div>

  
    <div class="content">
       
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
                                <img src="<?php echo htmlspecialchars($artist['profile_picture']); ?>" alt="Artist Picture">
                                <h3><?php echo htmlspecialchars($artist['username']); ?></h3>
                                <button onclick="startChat(<?= json_encode($artist['id']); ?>)">Message</button>
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

        
        <div class="content-section" id="events">
    <h3>Events</h3>
    <div class="events-list">
        <h4>Upcoming Events</h4>
        <?php if (!empty($events)): ?>
            <ul>
                <?php foreach ($events as $event): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($event['event_name']); ?></strong><br>
                        Date: <?php echo htmlspecialchars($event['event_date']); ?><br>
                        Time: <?php echo htmlspecialchars($event['event_time'] ?? 'Not specified'); ?><br>
                        <?php if (!empty($event['event_link'])): ?>
                            Link: <a href="<?php echo htmlspecialchars($event['event_link']); ?>" target="_blank">Join</a><br>
                        <?php endif; ?>
                        Description: <?php echo htmlspecialchars($event['description']); ?><br>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No events found.</p>
        <?php endif; ?>
    </div>
</div>


<div id="messages" class="section">
    <h2>Your Messages</h2>
    <div id="chat-box">
        <?php
        
        if (!empty($messages)) {
            foreach ($messages as $message) {
                $class = ($message['sender_id'] == $id) ? 'sent' : 'received';
                ?>
                <div class="message <?php echo $class; ?>">
                    <strong><?php echo htmlspecialchars($message['username']); ?>:</strong>
                    <?php echo htmlspecialchars($message['message']); ?>
                    <span class="timestamp"><?php echo date('Y-m-d H:i', strtotime($message['timestamp'])); ?></span>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <form id="message-form">
        <input type="hidden" id="artist_id" name="artist_id" value="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"> <!-- CSRF token -->
        <input type="text" id="message" placeholder="Type your message here" required>
        <button type="submit">Send</button>
    </form>
</div>


      
<div id="browse-artists" class="section active">
    <h2>Browse Artists</h2>
    <div class="artist-list">
        <?php
        
        if ($artistResult->num_rows > 0) {
            while ($artist = $artistResult->fetch_assoc()) {
                ?>
                <div class="artist">
                    <img src="<?php echo htmlspecialchars($artist['profile_picture']); ?>" alt="Artist Picture">
                    <h3><?php echo htmlspecialchars($artist['username']); ?></h3>
                    <form id="follow-form-<?php echo $artist['id']; ?>" method="POST">
                        <input type="hidden" name="artist_id" value="<?php echo $artist['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"> 
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
    
    const currentUserId = <?php echo isset($currentUserId) ? json_encode($currentUserId) : 'null'; ?>;
    const currentUsername = <?php echo isset($currentUsername) ? json_encode($currentUsername) : 'null'; ?>;

    
    const conn = new WebSocket('ws://localhost:8080'); 

    conn.onopen = function () {
        console.log("WebSocket connection established!");
    };

    conn.onerror = function (error) {
        console.error("WebSocket Error: ", error);
    };

    conn.onclose = function () {
        console.log("WebSocket connection closed");
    };

    
    conn.onmessage = function (event) {
        try {
            const data = JSON.parse(event.data);
            const chatBox = document.getElementById('chat-box');

            if (chatBox) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message ' + (data.sender_id === currentUserId ? 'sent' : 'received');
                messageDiv.innerHTML = `<strong>${data.username}:</strong> ${data.message}`;
                chatBox.appendChild(messageDiv);
                chatBox.scrollTop = chatBox.scrollHeight; 
            }
        } catch (e) {
            console.error("Error parsing message data:", e);
        }
    };

    
    document.getElementById('message-form').addEventListener('submit', function (event) {
        event.preventDefault();

        const messageInput = document.getElementById('message');
        const message = messageInput.value;
        const artistId = document.getElementById('artist_id').value;

        if (message.trim() !== "") {
            conn.send(JSON.stringify({
                type: 'message',
                sender_id: currentUserId,
                recipient_id: artistId,
                message: message
            }));

           
            messageInput.value = '';
        }
    });

    
    function showSection(sectionId) {
        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active');
        });
        const targetSection = document.getElementById(sectionId);
        if (targetSection) targetSection.classList.add('active');
    }

    
    function followArtist(artistId) {
        const csrfTokenElement = document.querySelector(`#follow-form-${artistId} input[name="csrf_token"]`);
        const csrfToken = csrfTokenElement ? csrfTokenElement.value : '';

        $.post('follow.php', { artist_id: artistId, csrf_token: csrfToken })
            .done(function (response) {
                const result = JSON.parse(response);
                alert(result.message);
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                alert("Error: Unable to follow artist. " + textStatus);
                console.error("Follow artist error:", errorThrown);
            });
    }

    
    function startChat(artistId) {
        const artistIdInput = document.getElementById('artist_id');
        if (artistIdInput) artistIdInput.value = artistId;

        showSection('messages');
        loadMessages(artistId);
    }

    
    function loadMessages(artistId) {
        $.post('load_messages.php', { artist_id: artistId })
            .done(function (response) {
                try {
                    const messages = JSON.parse(response);
                    const chatBox = document.getElementById('chat-box');
                    if (chatBox) {
                        chatBox.innerHTML = ''; 
                        messages.forEach(message => {
                            const messageDiv = document.createElement('div');
                            messageDiv.className = 'message ' + (message.sender_id === currentUserId ? 'sent' : 'received');
                            messageDiv.innerHTML = `<strong>${message.username}:</strong> ${message.message}`;
                            chatBox.appendChild(messageDiv);
                        });

                        chatBox.scrollTop = chatBox.scrollHeight; 
                    }
                } catch (e) {
                    console.error("Error parsing messages:", e);
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                alert("Error: Unable to load messages. " + textStatus);
                console.error("Load messages error:", errorThrown);
            });
    }
</script>

</body>
</html>