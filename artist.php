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
} else {
    echo "Artist profile not found.";
    exit();
}

// Handle sending messages
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
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

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/"; // Specify your upload directory
    $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check !== false) {
        // File is an image
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["profile_picture"]["size"] > 500000) { // Limit to 500KB
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // If everything is ok, try to upload file
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Update the profile picture in the database
            $stmt = $conn->prepare("UPDATE artwork SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $user_id);

            if ($stmt->execute()) {
                echo "The file ". htmlspecialchars(basename($_FILES["profile_picture"]["name"])). " has been uploaded.";
                header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
                exit();
            } else {
                echo "Error updating database: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Fetch messages for the logged-in user
$sql = "SELECT * FROM messages WHERE sender_id = ? OR recipient_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$messages_result = $stmt->get_result();

// Fetch followers of the artist
$followers_sql = "SELECT u.id, u.username FROM fans f JOIN users u ON f.follower_id = u.id WHERE f.followed_id = ?";
$followers_stmt = $conn->prepare($followers_sql);
$followers_stmt->bind_param("i", $user_id);
$followers_stmt->execute();
$followers_result = $followers_stmt->get_result();

// Get the total number of followers
$total_followers_sql = "SELECT COUNT(*) as total_followers FROM fans WHERE followed_id = ?";
$total_followers_stmt = $conn->prepare($total_followers_sql);
$total_followers_stmt->bind_param("i", $user_id);
$total_followers_stmt->execute();
$total_followers_result = $total_followers_stmt->get_result();
$total_followers = $total_followers_result->fetch_assoc()['total_followers'];

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Dashboard</title>
    <link rel="stylesheet" href="art.css">
    <script type="text/javascript" src="artist.js"></script>
    <style>
        /* Add your existing styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff9a00, #ff3d00);
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 300px;
            background: linear-gradient(45deg, #ff9a00, #ff3d00);
            color: white;
            padding: 10px;
            display: flex;
            flex-direction: column;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px 10px;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: background-color 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
        }

        .content {
            flex: 1;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            margin: auto;
            overflow-y: auto;
            max-width: 800px;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: auto;
            width: 100%;
        }

        .profile-container img {
            width: 150px;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .profile-container label {
            font-weight: bold;
            color: #3e3e3e;
        }

        .profile-container input,
        .profile-container textarea {
            width: 100%;
            padding: 12px 20px;
            margin: 10px 0;
            border: 2px solid #ff3d00;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .profile-container input:focus, 
        .profile-container textarea:focus {
            border-color: #ff9a00;
            outline: none;
        }

        .followers-list {
            display: flex;
            flex-direction: column;
        }

        .follower-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .messages-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .message {
            margin: 5px 0;
        }

        .message.sent {
            text-align: right;
            color: green;
        }

        .message.received {
            text-align: left;
            color: blue;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="sidebar">
        <h1>Dashboard</h1>
        <a href="#profile" class="active">Profile</a>
        <a href="#collaboration">Collaboration</a>
        <a href="#partnership">Partnership</a>
        <a href="#followers">Followers</a>
    </div>

    <div class="content">
        <!-- Profile Section -->
        <div class="content-section active" id="profile">
            <div class="profile-container">
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
                <label for="bio">Bio:</label>
                <textarea id="bio" readonly><?php echo $bio; ?></textarea>
                <form method="post" enctype="multipart/form-data">
                    <label for="profile_picture">Upload New Profile Picture:</label>
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                    <input type="submit" value="Upload">
                </form>
            </div>
        </div>

        <!-- Collaboration Section -->
        <div class="content-section" id="collaboration">
            <h3>Collaborate with Me</h3>
            <form method="post" action="">
                <label for="message">Send a Message:</label>
                <input type="hidden" name="receiver_id" value="<?php echo $user_id; ?>">
                <textarea id="message" name="message" required></textarea>
                <input type="submit" name="send_message" value="Send">
            </form>

            <h4>Messages:</h4>
            <div class="messages-container">
                <?php while ($message = $messages_result->fetch_assoc()): ?>
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
    </div>
</div>

<script>
    // JavaScript for handling tab navigation
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelector(this.getAttribute('href')).classList.add('active');

            document.querySelectorAll('.sidebar a').forEach(item => {
                item.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    function startConversation(followerId) {
        document.querySelector('input[name="receiver_id"]').value = followerId;
        document.querySelector('#collaboration').classList.add('active');
        document.querySelector('#profile').classList.remove('active');
        document.querySelector('#partnership').classList.remove('active');
        document.querySelector('#followers').classList.remove('active');
    }
</script>

</body>
</html>
