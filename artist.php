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

    // Prepare and execute the query to insert the message
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $receiver_id, $message);

    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle partnership requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_partnership'])) {
    $partner_name = $_POST['partner_name'];
    $description = $_POST['description'];

    // Prepare and execute the query to insert the partnership
    $stmt = $conn->prepare("INSERT INTO partnerships (artist_id, partner_name, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $partner_name, $description);

    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_changes'])) {
    // Update bio
    $new_bio = $_POST['bio'];

    // Update all social links
    $x_link = $_POST['x_link'];
    $instagram_link = $_POST['instagram_link'];
    $facebook_link = $_POST['facebook_link'];
    $linkedin_link = $_POST['linkedin_link'];
    $tiktok_link = $_POST['tiktok_link'];

    // Update bio and links in the database
    $stmt = $conn->prepare("UPDATE artwork SET bio = ?, x_link = ?, instagram_link = ?, facebook_link = ?, linkedin_link = ?, tiktok_link = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $new_bio, $x_link, $instagram_link, $facebook_link, $linkedin_link, $tiktok_link, $user_id);
    
    if ($stmt->execute()) {
        echo "Profile updated successfully.";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
    $stmt->close();

    // Handle profile picture upload
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

<div class="container">
    <div class="sidebar">
        <h1>Dashboard</h1>
        <a href="#profile" class="active">Profile</a>
        <a href="#collaboration">Collaboration</a>
        <a href="#partnership">Partnership</a>
        <a href="#followers">Followers</a>
        <a href="#messages">Messages</a> 
    </div>

    <div class="content">
        <!-- Profile Section -->
        <div class="content-section active" id="profile">
            <div class="profile-container">
                <div>
                    <?php if (!empty($profile_picture)): ?>
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="width: 150px; height: auto; margin-bottom: 20px;">
                    <?php else: ?>
                        <p>No profile picture uploaded.</p>
                    <?php endif; ?>
                </div>

                <form method="post" enctype="multipart/form-data">
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
                <?php while ($message = $all_messages_result->fetch_assoc()): ?>
                    <div class="message <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                        <strong><?php echo ($message['sender_id'] == $user_id) ? 'You' : 'Follower'; ?>:</strong>
                        <p><?php echo htmlspecialchars($message['message']); ?></p>
                        <small><?php echo $message['created_at']; ?></small>
                    </div>
                <?php endwhile; ?>
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
                        <span><?php echo $follower['username']; ?></span>
                        <button onclick="startConversation(<?php echo $follower['id']; ?>)">Message</button>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Messages Section -->
        <div class="content-section" id="messages">
            <h3>Your Messages</h3>
            <div class="messages-container">
                <?php while ($msg = $all_messages_result->fetch_assoc()): ?>
                    <div class="message <?php echo ($msg['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                        <strong><?php echo ($msg['sender_id'] == $user_id) ? 'You' : 'Follower'; ?>:</strong>
                        <p><?php echo htmlspecialchars($msg['message']); ?></p>
                        <small><?php echo $msg['created_at']; ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>