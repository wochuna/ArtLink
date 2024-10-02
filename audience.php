<?php
// Start the session
session_start();

// Check if the user is logged in as an audience
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'audience') {
    header('Location: login.php');
    exit();
}

// Connect to the database (modify with your own connection details)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "artlink_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch artworks
$artworkQuery = "SELECT * FROM artworks";
$artworks = $conn->query($artworkQuery);

// Fetch artists
$artistQuery = "SELECT * FROM artists";
$artists = $conn->query($artistQuery);

// Fetch events
$eventQuery = "SELECT * FROM events WHERE event_type = 'virtual'";
$events = $conn->query($eventQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audience Dashboard - ArtLink Entertainment</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

    <h1>Welcome to Your Dashboard</h1>

    <!-- Browse Artwork Section -->
    <section id="artworks">
        <h2>Browse Artworks</h2>
        <div class="artwork-gallery">
            <?php if ($artworks->num_rows > 0): ?>
                <?php while ($row = $artworks->fetch_assoc()): ?>
                    <div class="artwork-item">
                        <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['title']; ?>" width="200px">
                        <h3><?php echo $row['title']; ?></h3>
                        <p><?php echo $row['description']; ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No artworks available at the moment.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Follow Artists Section -->
    <section id="artists">
        <h2>Follow Artists</h2>
        <div class="artist-list">
            <?php if ($artists->num_rows > 0): ?>
                <?php while ($row = $artists->fetch_assoc()): ?>
                    <div class="artist-item">
                        <h3><?php echo $row['name']; ?></h3>
                        <button onclick="followArtist(<?php echo $row['id']; ?>)">Follow</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No artists available to follow.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Attend Virtual Events Section -->
    <section id="events">
        <h2>Attend Virtual Events</h2>
        <div class="event-list">
            <?php if ($events->num_rows > 0): ?>
                <?php while ($row = $events->fetch_assoc()): ?>
                    <div class="event-item">
                        <h3><?php echo $row['event_name']; ?></h3>
                        <p><?php echo $row['description']; ?></p>
                        <p>Date: <?php echo $row['event_date']; ?></p>
                        <a href="<?php echo $row['event_link']; ?>" target="_blank">Join Event</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No virtual events at the moment.</p>
            <?php endif; ?>
        </div>
    </section>

    <script>
        function followArtist(artistId) {
            // Ajax request to follow the artist
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "follow-artist.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert("You are now following the artist!");
                }
            };
            xhr.send("artistId=" + artistId);
        }
    </script>

</body>
</html>

<?php
$conn->close();
?>
