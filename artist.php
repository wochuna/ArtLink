<?php?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Artist Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="art.css"> <!-- Link to your main styles -->
    <script type="text/javascript" src="artist.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #ff9a00, #ff3d00); /* Bright orange to red gradient */
            padding: 20px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: gold;
            height: 90px;
            width: 100%;
            padding: 0 20px;
        }

        img.logo {
            height: 60px;
            margin-right: 15px;
        }

        label.logo {
            color: rgb(204, 46, 46);
            font-size: 45px;
            line-height: 10px;
            padding: 0;
            font-weight: bold;
            justify-content: center;
            text-align: center;
        }

        nav ul {
            display: flex;
            justify-content: end;
            margin-right: 0;
            margin-bottom: 0;
        }

        nav ul li {
            display: inline-block;
            margin: 5px;
        }

        nav ul li a {
            background-color: gold;
            font-size: 15px;
            color: black;
            text-decoration: none;
            padding: 10px;
        }

        nav ul li a:hover {
            text-decoration: underline;
        }

        form {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            margin: auto;
            flex-grow: 1;
            max-width: 400px;
        }

        input[type="file"], input[type="text"] {
            width: 100%;
            padding: 12px 20px;
            margin: 10px 0;
            border: 2px solid #ff3d00;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
            position: relative;
            z-index: 1;
        }

        button {
            background: linear-gradient(45deg, #ff3d00, #ff9a00);
            color: #fff;
            padding: 14px 20px;
            margin: 10px 0;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
            transition: transform 0.3s, background 0.3s;
            position: relative;
            z-index: 1;
        }

        button:hover {
            transform: translateY(-3px);
            background: linear-gradient(45deg, #ff9a00, #ff3d00);
        }

        h1 {
            color: #3e3e3e;
            text-align: center;
            margin-bottom: 20px;
            z-index: 1;
        }

        label {
            color: #3e3e3e;
            font-weight: bold;
            z-index: 1;
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
            <li><a href="sign-up.php">SIGN UP</a></li>
            <li><a href="login.php">LOGIN</a></li>
        </ul>
    </nav>

    <form id="upload-artwork-form" method="POST" action="artist-dashboard.php" enctype="multipart/form-data">
        <h1>Artist Dashboard</h1>
        
        <label for="profilePicture">Upload Profile Picture:</label>
        <input type="file" id="profilePicture" name="profilePicture" accept="image/*" required>

        <label for="socialMedia">Social Media Handles:</label>
        <input type="text" id="socialMedia" name="socialMedia" placeholder="Enter your social media links" required>

        <label for="artwork">Upload Your Artwork:</label>
        <input type="file" id="artwork" name="artwork[]" accept="image/*" multiple required>

        <button type="submit">Submit</button>
    </form>

    <?php
    // Database connection
    $servername = "localhost";
    $username = "your_db_username";
    $password = "your_db_password";
    $dbname = "your_db_name";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Handling profile picture upload
        $profilePicture = $_FILES['profilePicture'];
        $socialMedia = $_POST['socialMedia'];
        $artworks = $_FILES['artwork'];

        // Directory to store uploaded files
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Upload profile picture
        $profilePicturePath = $targetDir . basename($profilePicture["name"]);
        move_uploaded_file($profilePicture["tmp_name"], $profilePicturePath);

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO artists (profile_picture, social_media) VALUES (?, ?)");
        $stmt->bind_param("ss", $profilePicturePath, $socialMedia);
        $stmt->execute();

        // Get the last inserted artist ID
        $artistId = $stmt->insert_id;

        // Upload artworks
        foreach ($artworks["name"] as $key => $name) {
            $artworkPath = $targetDir . basename($name);
            move_uploaded_file($artworks["tmp_name"][$key], $artworkPath);

            // Insert artwork into database
            $stmt = $conn->prepare("INSERT INTO artworks (artist_id, file_path) VALUES (?, ?)");
            $stmt->bind_param("is", $artistId, $artworkPath);
            $stmt->execute();
        }

        echo "<p>Upload successful!</p>";
        $stmt->close();
    }

    $conn->close();
    ?>
</body>
</html>