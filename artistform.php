<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Artwork</title>
    <link rel="stylesheet" href="art.css"> <!-- Link to your external CSS file -->
    <script type="text/javascript" src="artist.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff9a00, #ff3d00); /* Gradient background */
            height: 100vh;
            display: flex;
            flex-direction: column; /* Allow stacking of nav and form container */
        }
        .upload-container {
            max-width: 800px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95); /* Semi-transparent white */
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            margin: auto; /* Center the upload container */
            flex-grow: 1; /* Allow upload container to grow */
        }

        h2 {
            color: #3e3e3e; /* Dark gray for the title */
            text-align: center;
        }

        label {
            color: #3e3e3e; /* Dark gray for labels */
            font-weight: bold;
        }

        input[type="text"], input[type="url"], input[type="file"], textarea {
            width: 100%;
            padding: 12px 20px;
            margin: 10px 0;
            border: 2px solid #ff3d00; /* Orange border */
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus, input[type="url"]:focus, input[type="file"]:focus, textarea:focus {
            border-color: #ff9a00; /* Lighter orange on focus */
            outline: none;
        }

        input[type="submit"] {
            background: linear-gradient(45deg, #ff3d00, #ff9a00); /* Gradient button */
            color: white;
            padding: 14px 20px;
            margin-top: 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
        }

        input[type="submit"]:hover {
            background: linear-gradient(45deg, #ff9a00, #ff3d00); /* Reverse gradient on hover */
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

    <div class="upload-container">
        <h2>Upload Your Artwork</h2>
        <form action="upload-artwork.php" method="post" enctype="multipart/form-data">
            <label for="artist_id">Artist ID:</label>
            <input type="text" name="artist_id" id="artist_id" required>

            <label for="title">Title:</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="artwork_image">Upload Artwork Image:</label>
            <input type="file" name="artwork_image" id="artwork_image" required>

            <h3>Social Media Links (optional):</h3>
            <label for="x_link">X:</label>
            <input type="url" name="x_link" id="x_link">

            <label for="instagram_link">Instagram:</label>
            <input type="url" name="instagram_link" id="instagram_link">

            <label for="facebook_link">Facebook:</label>
            <input type="url" name="facebook_link" id="facebook_link">

            <label for="linkedin_link">LinkedIn:</label>
            <input type="url" name="linkedin_link" id="linkedin_link">

            <input type="submit" value="Upload Artwork">
        </form>
    </div>
</body>
</html>
