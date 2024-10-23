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
    $start_date = $_POST['start_date'] ?? null; // Use null coalescing operator
    $end_date = $_POST['end_date'] ?? null; // Use null coalescing operator

    // Check if start_date is provided
    if (empty($start_date)) {
        echo "Start date is required.";
    } else {
        // Prepare and execute the query to insert the partnership
        $stmt = $conn->prepare("INSERT INTO partnerships (artist_id, partner_name, start_date, end_date, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $partner_name, $start_date, $end_date, $description);

        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch partnerships for the logged-in user
$sql = "SELECT partner_name, start_date, end_date, description FROM partnerships WHERE artist_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$partnerships_result = $stmt->get_result();

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
        /* Add your CSS styles here */
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
                <img src="uploads/<?php echo $profile_picture; ?>" alt="Profile Picture" />
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?php echo $username; ?>" required>
                
                <label for="bio">Bio:</label>
                <textarea name="bio" id="bio" required><?php echo $bio; ?></textarea>
                
                <label for="x_link">X (Twitter) Link:</label>
                <input type="url" name="x_link" id="x_link" value="<?php echo $x_link; ?>">
                
                <label for="instagram_link">Instagram Link:</label>
                <input type="url" name="instagram_link" id="instagram_link" value="<?php echo $instagram_link; ?>">
                
                <label for="facebook_link">Facebook Link:</label>
                <input type="url" name="facebook_link" id="facebook_link" value="<?php echo $facebook_link; ?>">
                
                <label for="linkedin_link">LinkedIn Link:</label>
                <input type="url" name="linkedin_link" id="linkedin_link" value="<?php echo $linkedin_link; ?>">

                <button type="submit" form="update-profile-form">Update Profile</button>
            </div>
        </div>

        <!-- Collaboration Section -->
        <div id="collaboration-section" class="content-section">
            <h2>Collaboration</h2>
            <p>Information about collaborations will be displayed here.</p>
        </div>

        <!-- Partnership Section -->
        <div id="partnership-section" class="content-section">
            <h2>Partnerships</h2>
            <form action="" method="POST" id="partnership-form">
                <label for="partner_name">Partner Name:</label>
                <input type="text" name="partner_name" id="partner_name" required>

                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" required>

                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date">

                <label for="description">Description:</label>
                <textarea name="description" id="description"></textarea>

                <input type="submit" name="submit_partnership" value="Add Partnership">
            </form>

            <div class="partnerships-list">
                <h3>Your Partnerships</h3>
                <?php while ($partnership = $partnerships_result->fetch_assoc()): ?>
                    <div class="partnership">
                        <strong><?php echo $partnership['partner_name']; ?></strong><br>
                        Start Date: <?php echo $partnership['start_date']; ?><br>
                        End Date: <?php echo $partnership['end_date']; ?><br>
                        Description: <?php echo $partnership['description']; ?><br>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Messages Section -->
        <div id="messages-section" class="content-section">
            <h2>Messages</h2>
            <form action="" method="POST" id="message-form">
                <label for="receiver_id">Send Message To (User ID):</label>
                <input type="number" name="receiver_id" id="receiver_id" required>

                <label for="message">Message:</label>
                <textarea name="message" id="message" required></textarea>

                <input type="submit" name="send_message" value="Send Message">
            </form>
        </div>
    </div>
</div>

<script>
    // JavaScript to handle sidebar link clicks and show/hide content sections
    const profileLink = document.getElementById("profile-link");
    const collaborationLink = document.getElementById("collaboration-link");
    const partnershipLink = document.getElementById("partnership-link");
    const messagesLink = document.getElementById("messages-link");

    const sections = document.querySelectorAll(".content-section");

    profileLink.addEventListener("click", () => {
        showSection("profile-section");
    });

    collaborationLink.addEventListener("click", () => {
        showSection("collaboration-section");
    });

    partnershipLink.addEventListener("click", () => {
        showSection("partnership-section");
    });

    messagesLink.addEventListener("click", () => {
        showSection("messages-section");
    });

    function showSection(sectionId) {
        sections.forEach(section => {
            section.classList.remove("active");
        });
        document.getElementById(sectionId).classList.add("active");

        // Remove active class from sidebar links
        [profileLink, collaborationLink, partnershipLink, messagesLink].forEach(link => {
            link.classList.remove("active");
        });

        // Set the clicked link as active
        document.getElementById(sectionId.replace("-section", "-link")).classList.add("active");
    }
</script>
</body>
</html>
