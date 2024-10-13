<?php 
// Start session to track logged-in user
session_start();

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

// Check if a user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['id']; // Logged in user's id

// Handle Follow action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['artist_id'])) {
    $artist_id = $_POST['artist_id'];

    // Insert follow relationship into the database
    $sql = "INSERT INTO follows (id, followed_id) VALUES (?, ?)";  // 'id' for user and 'followed_id' for artist
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $artist_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to follow the artist']);
    }
    $stmt->close();
    exit();
}

// Fetch all artists from the database
$sql = "SELECT u.id, u.username, a.profile_picture FROM users u
        JOIN artwork a ON u.id = a.id";  // Joining users and artwork tables
$artistResult = $conn->query($sql);

// Fetch all events from the database
$sql_events = "SELECT * FROM events";  // Correct table for events
$eventResult = $conn->query($sql_events);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution Page</title>
    <link rel="stylesheet" href="art.css">
    <link rel="stylesheet" href="audience.css">
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

    <!-- Search Bar -->
    <div class="search-bar">
        <input type="text" id="artistSearch" placeholder="Search for artists...">
        <button onclick="searchArtist()">Search</button>
    </div>

    <!-- Artist List -->
    <div class="artist-list">
        <?php
        if ($artistResult->num_rows > 0) {
            // Output data for each artist
            while ($row = $artistResult->fetch_assoc()) {
                echo '<div class="artist">';
                echo '<img src="uploads/' . $row['profile_picture'] . '" alt="Profile Picture" class="profile-pic">';
                echo '<span>' . $row['username'] . '</span>';
                echo '<button class="follow-btn" data-artist-id="' . $row['id'] . '">Follow</button>';
                echo '<button class="message-btn">Message</button>'; // Button to message artist
                echo '</div>';
            }
        } else {
            echo "No artists found.";
        }
        ?>
    </div>

    <!-- Event Section -->
    <div class="event-section">
        <h2>Upcoming Events</h2>
        <div class="events-list">
            <?php
            if ($eventResult->num_rows > 0) {
                while ($row = $eventResult->fetch_assoc()) {
                    echo '<div class="event">';
                    echo '<h3>' . $row['event_name'] . '</h3>';
                    echo '<p>' . $row['event_date'] . '</p>';
                    echo '<button>Attend</button>'; // Attend event button
                    echo '</div>';
                }
            } else {
                echo "No events found.";
            }
            ?>
        </div>
    </div>

    <script>
    // JavaScript for search functionality
    function searchArtist() {
        let input = document.getElementById('artistSearch').value.toLowerCase();
        let artists = document.getElementsByClassName('artist');
        
        for (let i = 0; i < artists.length; i++) {
            let artistName = artists[i].getElementsByTagName('span')[0].innerText.toLowerCase();
            if (artistName.includes(input)) {
                artists[i].style.display = "";
            } else {
                artists[i].style.display = "none";
            }
        }
    }

    // JavaScript for Follow button functionality
    document.querySelectorAll('.follow-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            let artistId = this.getAttribute('data-artist-id');
            // Perform an AJAX request to follow the artist
            fetch('institution.php', {  // Make sure this matches your page
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    artist_id: artistId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Followed successfully!');
                } else {
                    alert('Failed to follow the artist.');
                }
            });
        });
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>
