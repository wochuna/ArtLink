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

// Fetch messages for the logged-in user
$sql = "SELECT * FROM messages WHERE sender_id = ? OR recipient_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$messages_result = $stmt->get_result();

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

        .profile-container button,
        .profile-container input[type="submit"] {
            background: linear-gradient(45deg, #ff3d00, #ff9a00);
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        .profile-container button:hover,
        .profile-container input[type="submit"]:hover {
            background: linear-gradient(45deg, #ff9a00, #ff3d00);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .content {
                max-width: 100%;
                padding: 20px;
            }
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
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#" id="profile-link" class="active">Profile</a>
        <a href="#" id="collaboration-link">Collaboration</a>
        <a href="#" id="partnership-link">Partnership</a>
        <a href="#" id="messages-link">Messages</a>
    </div>

    <!-- Right Content Area -->
    <div class="content">
        <!-- Profile Section -->
        <div id="profile-section" class="content-section active">
            <h2>Your Profile</h2>
            <div class="profile-container">
                <!-- Profile Picture -->
                <img src="uploads/<?php echo $profile_picture; ?>" alt="Profile Picture" />
                
                <!-- Form to update profile picture -->
                <form action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
                    <label for="profile_picture">Change Profile Picture:</label>
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" onchange="this.form.submit()">
                </form>
                
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?php echo $username; ?>" required>
                
                <label for="bio">Bio:</label>
                <textarea name="bio" id="bio" required><?php echo $bio; ?></textarea>
                
                <!-- Social Media Links -->
                <label for="x_link">X:</label>
                <input type="url" name="x_link" id="x_link" value="<?php echo $x_link; ?>">
                
                <label for="instagram_link">Instagram:</label>
                <input type="url" name="instagram_link" id="instagram_link" value="<?php echo $instagram_link; ?>">
                
                <label for="facebook_link">Facebook:</label>
                <input type="url" name="facebook_link" id="facebook_link" value="<?php echo $facebook_link; ?>">
                
                <label for="linkedin_link">LinkedIn:</label>
                <input type="url" name="linkedin_link" id="linkedin_link" value="<?php echo $linkedin_link; ?>">
                
                <!-- Submit button for updating profile -->
                <input type="submit" value="Update Profile">
            </div>
        </div>

        <!-- Collaboration Section -->
        <div id="collaboration-section" class="content-section">
            <h2>Collaboration</h2>
            <form method="POST">
                <label for="collab_artist_id">Collaborate with Artist ID:</label>
                <input type="text" id="collab_artist_id" name="collab_artist_id" required>
                <input type="submit" name="submit_collaboration" value="Send Collaboration Request">
            </form>
        </div>

        <!-- Partnership Section -->
        <div id="partnership-section" class="content-section">
            <h2>Partnerships</h2>
            <form method="POST">
                <label for="partner_name">Partner Name:</label>
                <input type="text" id="partner_name" name="partner_name" required>
                
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
                
                <input type="submit" name="submit_partnership" value="Submit Partnership">
            </form>
        </div>

        <!-- Messages Section -->
        <div id="messages-section" class="content-section">
            <h2>Your Messages</h2>
            <div class="messages-container">
                <?php while ($message = $messages_result->fetch_assoc()) : ?>
                    <div class="message">
                        <strong><?php echo $message['sender_id'] == $user_id ? "You" : "Artist {$message['sender_id']}"; ?>:</strong>
                        <p><?php echo $message['message']; ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
            <form method="POST">
                <label for="receiver_id">Send Message to Artist ID:</label>
                <input type="text" id="receiver_id" name="receiver_id" required>
                
                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea>
                
                <input type="submit" name="send_message" value="Send Message">
            </form>
        </div>
    </div>
</div>

<script>
    // JavaScript to switch between sections
    const profileLink = document.getElementById('profile-link');
    const collaborationLink = document.getElementById('collaboration-link');
    const partnershipLink = document.getElementById('partnership-link');
    const messagesLink = document.getElementById('messages-link');

    const sections = document.querySelectorAll('.content-section');

    function showSection(sectionId) {
        sections.forEach(section => {
            section.classList.remove('active');
            if (section.id === sectionId) {
                section.classList.add('active');
            }
        });
    }

    profileLink.addEventListener('click', () => showSection('profile-section'));
    collaborationLink.addEventListener('click', () => showSection('collaboration-section'));
    partnershipLink.addEventListener('click', () => showSection('partnership-section'));
    messagesLink.addEventListener('click', () => showSection('messages-section'));
</script>
</body>
</html>
