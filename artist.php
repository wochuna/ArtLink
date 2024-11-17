<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['id'])) {
    echo "User not logged in. Please log in to edit your profile.";
    exit();
}

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "artlink_entertainment";
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['id'];

// Fetch the artist profile details
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

// Handle sending messages
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $receiver_id = $_POST['recipient_id'];
    $message = $_POST['message'];

    // Check if recipient exists
    $recipient_check = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
    $recipient_check->bind_param("i", $receiver_id);
    $recipient_check->execute();
    $recipient_check_result = $recipient_check->get_result();

    if ($recipient_check_result->num_rows === 0) {
        echo "<p>Error: Invalid recipient user ID.</p>";
    } else {
        $recipient_data = $recipient_check_result->fetch_assoc();
        $recipient_name = $recipient_data['username']; // Get recipient name

        // Insert message
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

// Display follower count
$follower_count_query = $conn->prepare("SELECT COUNT(*) AS total_followers FROM fans WHERE followed_id = ?");
$follower_count_query->bind_param("i", $user_id);
$follower_count_query->execute();
$follower_count_result = $follower_count_query->get_result();
$total_followers = $follower_count_result->fetch_assoc()['total_followers'] ?? 0;
$follower_count_query->close();

// Prepare followers data for JavaScript
$followers_array = [];
$followers_query = $conn->prepare("SELECT users.id, users.username FROM users JOIN fans ON users.id = fans.follower_id WHERE fans.followed_id = ?");
$followers_query->bind_param("i", $user_id);
$followers_query->execute();
$followers_result = $followers_query->get_result();

if ($followers_result->num_rows > 0) {
    while ($follower = $followers_result->fetch_assoc()) {
        $followers_array[] = $follower; // Populate the array
    }
}
$followers_query->close();

// Display messages for a selected recipient
$messages = []; // Initialize messages array
if (isset($_GET['recipient_id'])) {
    $recipient_id = $_GET['recipient_id'];

    // SQL query to fetch messages including sender and recipient names
    $sql = "SELECT sender_username, recipient_username, message, created_at FROM messages WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?) ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iiii", $user_id, $recipient_id, $recipient_id, $user_id);
        if ($stmt->execute()) {
            $all_messages_result = $stmt->get_result();
            if ($all_messages_result->num_rows > 0) {
                while ($row = $all_messages_result->fetch_assoc()) {
                    $messages[] = $row; // Store messages for display
                }
            }
        }
        $stmt->close();
    }
}

// Handle profile updates and file uploads...
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $original_file_name = basename($_FILES["profile_picture"]["name"]);
    $imageFileType = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
    $target_file = $target_dir . uniqid() . '.' . $imageFileType;
    $uploadOk = 1;

    if (file_exists($_FILES["profile_picture"]["tmp_name"])) {
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }

    if ($_FILES["profile_picture"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
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
}

// Handle artwork upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['artwork'])) {
    $target_dir = "artworks/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $original_file_name = basename($_FILES["artwork"]["name"]);
    $artworkFileType = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
    $target_file = $target_dir . uniqid() . '.' . $artworkFileType;
    $uploadOk = 1;

    if (file_exists($_FILES["artwork"]["tmp_name"])) {
        $check = getimagesize($_FILES["artwork"]["tmp_name"]);
        if ($check === false && !in_array($artworkFileType, ['mp4', 'mov', 'avi'])) {
            echo "File is not a valid image or video.";
            $uploadOk = 0;
        }
    }

    if ($_FILES["artwork"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    if (!in_array($artworkFileType, ['jpg', 'png', 'jpeg', 'gif', 'mp4', 'mov', 'avi'])) {
        echo "Sorry, only JPG, JPEG, PNG, GIF, MP4, MOV & AVI files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["artwork"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE artwork SET artwork_file = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $user_id);
            if ($stmt->execute()) {
                echo "Artwork uploaded.";
            }
            $stmt->close();
        } else {
            echo "Error uploading your file.";
        }
    }
}

// Close the database connection
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
            <h3>Partnership Requests</h3>
            <form method="post" action="">
                <label for="partner_name">Partner Name:</label>
                <input type="text" id="partner_name" name="partner_name" required>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
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
                            <button onclick="startConversation(<?php echo $follower['id']; ?>, '<?php echo htmlspecialchars($follower['username']); ?>')">Message</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-section" id="events">
            <h3>Events</h3>
        </div>

        <div class="content-section" id="messages">
    <h3>Messages</h3>
    <div id="chatHeader">
        <h4>Chat with <span id="recipientUsername"><?php echo isset($recipient_username) ? htmlspecialchars($recipient_username) : 'Unknown User'; ?></span></h4>
    </div>
    <div class="messages-container">
        <div id="chat-box" class="message-thread">
            <!-- Messages will be dynamically inserted here -->
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

    <form method="post" action="" id="messageForm">
        <input type="hidden" id="recipientId" name="recipient_id" value="<?php echo isset($recipient_id) ? htmlspecialchars($recipient_id) : ''; ?>">
        <textarea id="message" name="message" placeholder="Type a message..." required></textarea>
        <input type="submit" name="send_message" value="Send">
    </form>
</div>
    </div>
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

// Initialize the default section to display
document.addEventListener('DOMContentLoaded', function() {
    showSection('profile'); // Show profile section by default
});
</script>
<script src="artist.js" defer></script>
</body>
</html>