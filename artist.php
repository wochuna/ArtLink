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
    background: linear-gradient(135deg, #ff9a00, #ff3d00); /* Gradient background */
    height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Container setup for sidebar and content */
.container {
    display: flex;
    height: 100vh;
}

/* Left Sidebar */
.sidebar {
    width: 300px;
    background: linear-gradient(45deg, #ff9a00, #ff3d00); /* Matching gradient */
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
    background: rgba(255, 255, 255, 0.2); /* Light white on hover */
}

/* Right Content Area */
.content {
    flex: 1;
    padding: 40px;
    background: rgba(255, 255, 255, 0.95); /* Semi-transparent white background */
    border-radius: 20px; /* Rounded corners */
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2); /* Form-style shadow */
    margin: auto; /* Center the content vertically and horizontally */
    overflow-y: auto;
    max-width: 800px;
}

/* Content Sections */
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
    border: 2px solid #ff3d00; /* Orange border */
    border-radius: 10px;
    box-sizing: border-box;
    font-size: 16px;
    transition: border-color 0.3s;
}

.profile-container input:focus, 
.profile-container textarea:focus {
    border-color: #ff9a00; /* Lighter orange on focus */
    outline: none;
}

/* Button Styles */
.profile-container button,
.profile-container input[type="submit"] {
    background: linear-gradient(45deg, #ff3d00, #ff9a00); /* Gradient button */
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
    background: linear-gradient(45deg, #ff9a00, #ff3d00); /* Reverse gradient on hover */
}

/* Responsive Design */
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

<!-- Navigation Bar (assuming you have this in your existing setup) -->
<nav>
    <!-- Your existing navigation bar content -->
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
                <button>Change Profile Picture</button> <!-- Button to change profile picture -->
                
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
                
                <!-- Update Profile Button -->
                <input type="submit" name="update" value="Update Profile"> <!-- Update profile button -->
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
