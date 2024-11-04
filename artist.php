<?php
// Start session to track logged-in user
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo "User not logged in. Please log in to edit your profile.";
    exit();
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

// Get the user ID from the session
$user_id = $_SESSION['id'];

// Fetch the artist profile details from the database
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
    $recipient_check = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $recipient_check->bind_param("i", $receiver_id);
    $recipient_check->execute();
    $recipient_check_result = $recipient_check->get_result();

    if ($recipient_check_result->num_rows === 0) {
        echo "Error: Invalid recipient user ID.";
    } else {
        // Insert message
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message);
        if ($stmt->execute()) {
            echo "Message sent successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $recipient_check->close();
}
// Check if recipient_id is set in the URL parameters
if (isset($_GET['recipient_id'])) {
    $recipient_id = $_GET['recipient_id']; // Get recipient ID from query parameter

    // SQL query to fetch messages between the user and the recipient
    $sql = "SELECT * FROM messages WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?) ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);

    // Check if the statement prepared successfully
    if ($stmt) {
        // Bind parameters (current user ID and recipient ID pairs)
        $stmt->bind_param("iiii", $user_id, $recipient_id, $recipient_id, $user_id);

        // Execute the query and fetch the result
        if ($stmt->execute()) {
            $all_messages_result = $stmt->get_result();

            // Check if the result has messages
            if ($all_messages_result->num_rows > 0) {
                while ($row = $all_messages_result->fetch_assoc()) {
                    $from = $row['sender_id'] === $user_id ? 'You' : 'Them';
                    echo "<p><strong>{$from}:</strong> " . htmlspecialchars($row['message']) . "<br>";
                    echo "<strong>Sent at:</strong> " . htmlspecialchars($row['created_at']) . "</p>";
                }
            } else {
                echo "No messages found.";
            }
        } else {
            echo "Error executing the SQL statement: " . $stmt->error;
        }

        // Close the statement after execution
        $stmt->close();
    } else {
        echo "Error preparing the SQL statement: " . $conn->error;
    }
} else {
    echo "Select a recipient to view messages.";
}

// Handle follower count and listing
$follower_count_query = $conn->prepare("SELECT COUNT(*) AS total_followers FROM fans WHERE followed_id = ?");
$follower_count_query->bind_param("i", $user_id);
$follower_count_query->execute();
$follower_count_result = $follower_count_query->get_result();
$total_followers = $follower_count_result->fetch_assoc()['total_followers'] ?? 0;
$follower_count_query->close();
echo "Your Followers (" . $total_followers . ")<br>";

$followers_query = $conn->prepare("SELECT users.username FROM users JOIN fans ON users.id = fans.follower_id WHERE fans.followed_id = ?");
$followers_query->bind_param("i", $user_id);
$followers_query->execute();
$followers_result = $followers_query->get_result();
while ($follower = $followers_result->fetch_assoc()) {
    echo "<p>" . htmlspecialchars($follower['username']) . "</p>";
}
$followers_query->close();

// Handle profile updates and file uploads...
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
    $target_dir = "uploads/"; // Specify your upload directory
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true); // Create directory if it doesn't exist
    }

    // Generate a unique filename
    $original_file_name = basename($_FILES["profile_picture"]["name"]);
    $imageFileType = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
    $target_file = $target_dir . uniqid() . '.' . $imageFileType; // Unique filename
    $uploadOk = 1;

    // Check if the uploaded file is an actual image
    if (file_exists($_FILES["profile_picture"]["tmp_name"])) {
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    } else {
        echo "Temporary file does not exist.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["profile_picture"]["size"] > 500000) { // Limit to 500KB
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // Try to upload the file
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Update the profile picture in the database
            $stmt = $conn->prepare("UPDATE artwork SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $user_id);

            if ($stmt->execute()) {
                echo "The file " . htmlspecialchars($original_file_name) . " has been uploaded and profile updated.";
            } else {
                echo "Error updating database: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle artwork upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['artwork'])) {
$target_dir = "artworks/"; // Specify your artwork upload directory
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true); // Create directory if it doesn't exist
}

// Generate a unique filename
$original_file_name = basename($_FILES["artwork"]["name"]);
$artworkFileType = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
$target_file = $target_dir . uniqid() . '.' . $artworkFileType; // Unique filename
$uploadOk = 1;

// Check if file is an actual image or video
if (file_exists($_FILES["artwork"]["tmp_name"])) {
    $check = getimagesize($_FILES["artwork"]["tmp_name"]);
    if ($check === false && !in_array($artworkFileType, ['mp4', 'mov', 'avi'])) {
        echo "File is not a valid image or video.";
        $uploadOk = 0;
    }
} else {
    echo "Temporary file does not exist.";
    $uploadOk = 0;
}

// Check file size (limit to 5MB for artwork)
if ($_FILES["artwork"]["size"] > 5000000) { 
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
if (!in_array($artworkFileType, ['jpg', 'png', 'jpeg', 'gif', 'mp4', 'mov', 'avi'])) {
    echo "Sorry, only JPG, JPEG, PNG, GIF, MP4, MOV & AVI files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
} else {
    // If everything is ok, try to upload file
    if (move_uploaded_file($_FILES["artwork"]["tmp_name"], $target_file)) {
        // Update the artwork_file in the artwork table
        $stmt = $conn->prepare("UPDATE artwork SET artwork_file = ? WHERE id = ?");
        $stmt->bind_param("si", $target_file, $user_id);

        if ($stmt->execute()) {
            echo "The file " . htmlspecialchars($original_file_name) . " has been uploaded.";
        } else {
            echo "Error saving artwork path to database: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
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
    <script type="text/javascript" src="artist.js"></script>
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
        <a href="#" onclick="showSection('messages')">Messages</a> 
    </nav>

    <div class="content">
        <!-- Profile Section -->
        <div class="content-section active" id="profile">
            <div class="profile-container">
                <!-- Profile Picture -->
                <div>
                    <?php if (!empty($profile_picture)): ?>
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="width: 150px; height: auto; margin-bottom: 20px;">
                    <?php else: ?>
                        <p>No profile picture uploaded.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Profile Form -->
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

<!-- Collaboration Section -->
<div class="content-section" id="collaboration">
    <h3>Collaborate with Me</h3>
    <form method="post" action="">
        <label for="message">Send a Message:</label>
        <input type="hidden" name="recipient_id" value="<?php echo $user_id; ?>">
        <textarea id="message" name="message" required></textarea>
        <input type="submit" name="send_message" value="Send">
    </form>

    <h4>Messages:</h4>
    <div class="messages-container">
        <?php if (isset($all_messages_result) && $all_messages_result->num_rows > 0): ?>
            <?php while ($message = $all_messages_result->fetch_assoc()): ?>
                <div class="message <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                    <strong><?php echo ($message['sender_id'] == $user_id) ? 'You' : 'Follower'; ?>:</strong>
                    <p><?php echo htmlspecialchars($message['message']); ?></p>
                    <small><?php echo $message['created_at']; ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No messages found.</p>
        <?php endif; ?>
    </div>
</div>


        <!-- Partnership Section -->
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

        <!-- Followers Section -->
        <div class="content-section" id="followers">
            <h3>Your Followers (<?php echo $total_followers; ?>)</h3>
            <div class="followers-list">
                <?php while ($follower = $followers_result->fetch_assoc()) : ?>
                    <div class="follower-item">
                        <span><?php echo htmlspecialchars($follower['username']); ?></span>
                        <button onclick="startConversation(<?php echo $follower['id']; ?>)">Message</button>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

       <!-- Messages Section -->
<div class="content-section" id="messages">
    <h3>Your Messages</h3>
    <div class="messages-container">
        <?php if (isset($all_messages_result) && $all_messages_result->num_rows > 0): ?>
            <?php while ($msg = $all_messages_result->fetch_assoc()): ?>
                <div class="message <?php echo ($msg['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                    <strong><?php echo ($msg['sender_id'] == $user_id) ? 'You' : 'Follower'; ?>:</strong>
                    <p><?php echo htmlspecialchars($msg['message']); ?></p>
                    <small><?php echo $msg['created_at']; ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No messages found.</p>
        <?php endif; ?>
    </div>
</div>

        </div>
    </div>
</div>

</body>
</html>