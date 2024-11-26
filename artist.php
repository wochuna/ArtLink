<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo "User not logged in. Please log in to edit your profile.";
    exit();
}

// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "artlink_entertainment";
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['id'];

// Fetch artist profile
$sql = "SELECT * FROM artwork WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $artist = $result->fetch_assoc();
    $username = $artist['username'];
    $bio = $artist['bio'];
    $profile_picture = $artist['profile_picture'];
    $x_link = $artist['x_link'];
    $instagram_link = $artist['instagram_link'];
    $facebook_link = $artist['facebook_link'];
    $linkedin_link = $artist['linkedin_link'];
    $tiktok_link = $artist['tiktok_link'];
} else {
    echo "Artist profile not found.";
    exit();
}

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $receiver_id = $_POST['recipient_id'];
    $message = $_POST['message'];

    $recipient_check = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
    $recipient_check->bind_param("i", $receiver_id);
    $recipient_check->execute();
    $recipient_check_result = $recipient_check->get_result();

    if ($recipient_check_result->num_rows === 0) {
        echo "<p>Error: Invalid recipient user ID.</p>";
    } else {
        $recipient_data = $recipient_check_result->fetch_assoc();
        $recipient_name = $recipient_data['username'];

        $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, sender_username, recipient_username, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisss", $user_id, $receiver_id, $username, $recipient_name, $message);
        if ($stmt->execute()) {
            echo "<p>Message sent successfully!</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
    $recipient_check->close();
}

// Fetch follower count
$follower_count_query = $conn->prepare("SELECT COUNT(*) AS total_followers FROM fans WHERE followed_id = ?");
$follower_count_query->bind_param("i", $user_id);
$follower_count_query->execute();
$follower_count_result = $follower_count_query->get_result();
$total_followers = $follower_count_result->fetch_assoc()['total_followers'] ?? 0;
$follower_count_query->close();

// Fetch followers
$followers_array = [];
$followers_query = $conn->prepare("SELECT users.id, users.username FROM users JOIN fans ON users.id = fans.follower_id WHERE fans.followed_id = ?");
$followers_query->bind_param("i", $user_id);
$followers_query->execute();
$followers_result = $followers_query->get_result();

if ($followers_result->num_rows > 0) {
    while ($follower = $followers_result->fetch_assoc()) {
        $followers_array[] = $follower;
    }
}
$followers_query->close();

// Fetch messages with a recipient
$messages = [];
if (isset($_GET['recipient_id'])) {
    $recipient_id = $_GET['recipient_id'];
    $sql = "SELECT sender_username, recipient_username, message, created_at FROM messages WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?) ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iiii", $user_id, $recipient_id, $recipient_id, $user_id);
        if ($stmt->execute()) {
            $all_messages_result = $stmt->get_result();
            if ($all_messages_result->num_rows > 0) {
                while ($row = $all_messages_result->fetch_assoc()) {
                    $messages[] = $row;
                }
            }
        }
        $stmt->close();
    }
}

// Handle profile picture upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $original_file_name = basename($_FILES["profile_picture"]["name"]);
    $imageFileType = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
    $target_file = $target_dir . uniqid() . '.' . $imageFileType;

    if ($_FILES["profile_picture"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
    } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    } elseif (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("UPDATE artwork SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $target_file, $user_id);
        if ($stmt->execute()) {
            echo "Profile picture updated.";
        }
        $stmt->close();
    } else {
        echo "Error uploading your file.";
    }
}

// Handle partnership submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_partnership'])) {
    $institution_id = $_POST['institution_id'];
    $partner_name = $_POST['partner_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'];

    if (empty($partner_name) || empty($start_date) || empty($description)) {
        echo "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO partnerships (artist_id, institution_id, partner_name, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $user_id, $institution_id, $partner_name, $start_date, $end_date, $description);

        if ($stmt->execute()) {
            echo "Partnership added successfully!";
        } else {
            echo "Error adding partnership: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch events
$events_query = $conn->prepare("SELECT * FROM events WHERE artist_id = ? ORDER BY created_at DESC");
$events_query->bind_param("i", $user_id);
$events_query->execute();
$events_result = $events_query->get_result();

$events = [];
if ($events_result->num_rows > 0) {
    while ($event = $events_result->fetch_assoc()) {
        $events[] = $event;
    }
}
$events_query->close();

// Add event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $event_name = $_POST['event_name'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? null;
    $event_link = $_POST['event_link'] ?? null;
    $description = $_POST['description'] ?? '';

    if (empty($event_name) || empty($event_date) || empty($description)) {
        echo "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO events (event_name, event_date, event_time, event_link, description, artist_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssi", $event_name, $event_date, $event_time, $event_link, $description, $user_id);

        if ($stmt->execute()) {
            echo "Event added successfully!";
        } else {
            echo "Error adding event: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Dashboard</title>
    <link rel="stylesheet" href="art.css">
    <link rel="stylesheet" href="artist.css">
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
    <nav class="sidebar" role="navigation">
        <h1>Dashboard</h1>
        <a href="#" onclick="showSection('profile')" class="active" aria-current="page">Profile</a>
        <a href="#" onclick="showSection('collaboration')">Collaboration</a>
        <a href="#" onclick="showSection('partnership')">Partnership</a>
        <a href="#" onclick="showSection('followers')">Followers</a>
        <a href="#" onclick="showSection('events')">Events</a>
        <a href="#" onclick="showSection('messages')">Messages</a>
    </nav>

    <div class="content">
        <div class="content-section active" id="profile">
            <div class="profile-container">
                <div>
                    <?php if (!empty($profile_picture)): ?>
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="width: 150px; height: auto; margin-bottom: 20px;">
                    <?php else: ?>
                        <p>No profile picture uploaded.</p>
                    <?php endif; ?>
                </div>
                
                <form method="post" enctype="multipart/form-data" action="">
                    <label for="profile_picture">Upload New Profile Picture:</label>
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                    <input type="submit" value="Upload">

                    <label for="bio">Bio:</label>
                    <textarea id="bio" name="bio" required><?php echo htmlspecialchars($bio); ?></textarea>

                    <label for="artwork">Upload Artwork (Image/Video):</label>
                    <input type="file" name="artwork" id="artwork" accept="image/*,video/*">
                    <input type="submit" name="upload_artwork" value="Upload Artwork">

                    <label for="x_link">X Link:</label>
                    <input type="url" id="x_link" name="x_link" value="<?php echo htmlspecialchars($x_link); ?>">

                    <label for="instagram_link">Instagram Link:</label>
                    <input type="url" id="instagram_link" name="instagram_link" value="<?php echo htmlspecialchars($instagram_link); ?>">

                    <label for="facebook_link">Facebook Link:</label>
                    <input type="url" id="facebook_link" name="facebook_link" value="<?php echo htmlspecialchars($facebook_link); ?>">

                    <label for="linkedin_link">LinkedIn Link:</label>
                    <input type="url" id="linkedin_link" name="linkedin_link" value="<?php echo htmlspecialchars($linkedin_link); ?>">

                    <label for="tiktok_link">TikTok Link:</label>
                    <input type="url" id="tiktok_link" name="tiktok_link" placeholder="Enter your TikTok link here" value="<?php echo htmlspecialchars($tiktok_link); ?>">

                    <input type="submit" name="save_changes" value="Save Changes">
                </form>
            </div>
        </div>

        <div class="content-section" id="collaboration">
            <h3>Collaborate with Me</h3>
        </div>

        <div class="content-section" id="partnership">
    <h3>Submit Partnership Request</h3>
    <form method="post" action="">
        
        <label for="partner_name">Partner Name (Institution):</label>
        <input type="text" id="partner_name" name="partner_name" required>

        
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>

        
        <label for="description">Partnership Description:</label>
        <textarea id="description" name="description" required></textarea>

        
        <label for="contact_info">Institution Contact Info:</label>
        <input type="text" id="contact_info" name="contact_info">

        <input type="submit" name="submit_partnership" value="Submit Partnership">
    </form>
</div>

<div class="content-section" id="followers">
    <h3>Your Followers</h3>
    <div class="followers-list">
        <p><strong>Total Followers:</strong> <?php echo $total_followers; ?></p>
        <?php if (empty($followers_array)): ?>
            <p>No followers found.</p>
        <?php else: ?>
            <?php foreach ($followers_array as $follower): ?>
                <div class="follower-item">
                    <span><?php echo htmlspecialchars($follower['username']); ?></span>
                    <button onclick="startConversation(<?php echo $follower['id']; ?>, '<?php echo htmlspecialchars($follower['username']); ?>')">
                        Message
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<div class="content-section" id="events">
    <h3>Events</h3>
   
    
    <form method="post" action="" enctype="multipart/form-data" class="event-form">
        <label for="event_name">Event Name:</label>
        <input type="text" id="event_name" name="event_name" required>

        <label for="event_date">Event Date:</label>
        <input type="date" id="event_date" name="event_date" required>

        <label for="event_time">Event Time:</label>
        <input type="time" id="event_time" name="event_time">

        <label for="event_link">Event Link:</label>
        <input type="url" id="event_link" name="event_link" placeholder="Enter online event link">

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>

        <button type="submit" name="add_event" class="btn-submit">Add Event</button>
    </form>

    
    <div class="events-list">
        <h4>Your Events</h4>
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
                        
                      
                        <form method="post" action="" style="display: inline;">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <button type="submit" name="delete_event" class="btn-delete">Delete</button>
                        </form>

                        
                        <form method="post" action="" style="display: inline;">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <button type="submit" name="edit_event" class="btn-edit">Edit</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No events found.</p>
        <?php endif; ?>
    </div>
</div>

<div class="content-section" id="messages">
    <h3>Messages</h3>
    <div id="chatHeader">
        <h4>Chat with <span id="recipientUsername"><?php echo isset($recipient_username) ? htmlspecialchars($recipient_username) : 'Unknown User'; ?></span></h4>
    </div>
    <div class="messages-container">
        <div id="chat-box" class="message-thread">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message <?php echo $message['sender_username'] === $username ? 'sent' : 'received'; ?>">
                        <strong><?php echo htmlspecialchars($message['sender_username']); ?>:</strong>
                        <?php echo htmlspecialchars($message['message']); ?>
                        <span class="timestamp"><?php echo date('Y-m-d H:i:s', strtotime($message['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="message"><em>No messages yet.</em></div>
            <?php endif; ?>
        </div>
    </div>

    <form method="post" action="messages.php" id="messageForm">
        <input type="hidden" id="recipientId" name="recipient_id" value="<?php echo isset($recipient_id) ? htmlspecialchars($recipient_id) : ''; ?>">
        <textarea id="message" name="message" placeholder="Type a message..." required></textarea>
        <input type="submit" name="messages" value="Send">
    </form>
</div>


<script>
function showSection(sectionId) {
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => section.classList.remove('active'));
    document.getElementById(sectionId).classList.add('active');
}

function startConversation(followerId, followerUsername) {
    window.location.href = `artist.php?recipient_id=${followerId}`;
}


document.addEventListener('DOMContentLoaded', function() {
    showSection('profile'); 
});
</script>
<script src="artist.js" defer></script>
</body>
</html>