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
        /* Global styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* Left Sidebar */
        .sidebar {
            width: 300px;
            background-color: #333;
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
            border-bottom: 1px solid #555;
            transition: background-color 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #555;
        }

        /* Right Content Area */
        .content {
            flex: 1;
            padding: 20px;
            background-color: white;
            overflow-y: auto;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        /* Profile Section */
        .profile-container {
            display: flex;
            flex-direction: column;
            max-width: 600px;
            margin: auto;
        }

        .profile-container img {
            width: 150px;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .profile-container label {
            font-weight: bold;
        }

        .profile-container input,
        .profile-container textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .profile-container input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        .profile-container input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<nav>
        <img src="spice-it-up/Capture.PNG" alt="ArtLink Logo" class="logo"> <!-- Replace with your logo image -->
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
                
                <!-- Update Button -->
                <input type="submit" name="update" value="Update Profile">
            </div>
        </div>

        <!-- Collaboration Section -->
        <div id="collaboration-section" class="content-section">
            <h2>Collaboration</h2>
            <p>Details about collaborations go here.</p>
        </div>

        <!-- Partnership Section -->
        <div id="partnership-section" class="content-section">
            <h2>Partnership</h2>
            <p>Details about partnerships go here.</p>
        </div>

        <!-- Messages Section -->
        <div id="messages-section" class="content-section">
            <h2>Messages</h2>
            <p>Your messages will appear here.</p>
        </div>
    </div>
</div>

<script>
// JavaScript to toggle content based on link clicked
document.getElementById('profile-link').addEventListener('click', function() {
    setActiveSection('profile-section');
});

document.getElementById('collaboration-link').addEventListener('click', function() {
    setActiveSection('collaboration-section');
});

document.getElementById('partnership-link').addEventListener('click', function() {
    setActiveSection('partnership-section');
});

document.getElementById('messages-link').addEventListener('click', function() {
    setActiveSection('messages-section');
});

function setActiveSection(sectionId) {
    // Remove active class from all sections
    var sections = document.getElementsByClassName('content-section');
    for (var i = 0; i < sections.length; i++) {
        sections[i].classList.remove('active');
    }
    
    // Hide all sidebar links
    var links = document.querySelectorAll('.sidebar a');
    links.forEach(link => link.classList.remove('active'));

    // Set active class on the selected section and link
    document.getElementById(sectionId).classList.add('active');
    document.getElementById(sectionId.split('-')[0] + '-link').classList.add('active');
}
</script>

</body>
</html>
