<?php
// Start session if needed
session_start();

// Database connection details
$servername = "localhost";
$dbUsername = "root";  // Change as needed
$dbPassword = "";      // Change as needed
$dbName = "artlink_entertainment"; // Your database name

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all uploaded artwork data from the database
$sql = "SELECT * FROM artwork";
$result = $conn->query($sql);

$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Artwork Gallery</title>
    <link rel="stylesheet" href="art.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff9a00, #ff3d00); /* Gradient background */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .gallery-container {
            max-width: 1200px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95); /* Semi-transparent white */
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            margin: auto; /* Center the gallery container */
        }

        h2 {
            color: #3e3e3e; /* Dark gray for the title */
            text-align: center;
        }

        .artwork-item {
            margin-bottom: 40px;
            text-align: center;
        }

        .artwork-item img {
            max-width: 300px;
            border-radius: 10px;
            margin: 10px 0;
        }

        .artwork-item p {
            margin: 5px 0;
        }

        .social-links a {
            margin: 0 10px;
            text-decoration: none;
            color: #ff3d00;
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
    <div class="gallery-container">
        <h2>Uploaded Artwork Gallery</h2>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="artwork-item">
                    <h3>Description: <?php echo $row['description']; ?></h3>

                    <?php if (!empty($row['profile_picture'])): ?>
                        <p><strong>Profile Picture:</strong></p>
                        <img src="<?php echo $row['profile_picture']; ?>" alt="Profile Picture">
                    <?php endif; ?>

                    <p><strong>Artwork Image:</strong></p>
                    <img src="<?php echo $row['image']; ?>" alt="Artwork Image">

                    <p class="social-links">
                        <?php if (!empty($row['x_link'])): ?>
                            <a href="<?php echo $row['x_link']; ?>" target="_blank">X</a>
                        <?php endif; ?>
                        <?php if (!empty($row['instagram_link'])): ?>
                            <a href="<?php echo $row['instagram_link']; ?>" target="_blank">Instagram</a>
                        <?php endif; ?>
                        <?php if (!empty($row['facebook_link'])): ?>
                            <a href="<?php echo $row['facebook_link']; ?>" target="_blank">Facebook</a>
                        <?php endif; ?>
                        <?php if (!empty($row['linkedin_link'])): ?>
                            <a href="<?php echo $row['linkedin_link']; ?>" target="_blank">LinkedIn</a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No artwork uploaded yet.</p>
        <?php endif; ?>
    </div>

</body>
</html>
