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

$uploadSuccess = false; // To track if upload is successful
$uploadedData = []; // To store uploaded data

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $description = $_POST['description'];

    // Handle profile picture upload
    $profile_picture = $_FILES['profile_picture'];
    $profile_picture_path = null;
    if (!empty($profile_picture['name'])) {
        $profile_picture_path = 'uploads/' . basename($profile_picture['name']);
    }

    // Handle the uploaded artwork image
    $artwork_image = $_FILES['artwork_image'];
    $artwork_image_path = 'uploads/' . basename($artwork_image['name']); // Set the path for the uploaded image

    // Move the uploaded artwork file to the specified directory
    if (move_uploaded_file($artwork_image['tmp_name'], $artwork_image_path)) {
        // If profile picture is uploaded, move it as well
        if ($profile_picture_path && move_uploaded_file($profile_picture['tmp_name'], $profile_picture_path)) {
            // Profile picture uploaded successfully
        }
        // Prepare social media links
        $x_link = $_POST['x_link'];
        $instagram_link = $_POST['instagram_link'];
        $facebook_link = $_POST['facebook_link'];
        $linkedin_link = $_POST['linkedin_link'];

        // Insert artwork details into the database
        $stmt = $conn->prepare("INSERT INTO artworks (description, image, profile_picture, x_link, instagram_link, facebook_link, linkedin_link) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $description, $artwork_image_path, $profile_picture_path, $x_link, $instagram_link, $facebook_link, $linkedin_link);

        if ($stmt->execute()) {
            $uploadSuccess = true;
            // Store uploaded data for display
            $uploadedData = [
                'description' => $description,
                'artwork_image' => $artwork_image_path,
                'profile_picture' => $profile_picture_path,
                'x_link' => $x_link,
                'instagram_link' => $instagram_link,
                'facebook_link' => $facebook_link,
                'linkedin_link' => $linkedin_link
            ];
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Failed to upload artwork image.";
    }
}

$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Profile Picture and Artwork</title>
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
        input[type="file"], input[type="url"], textarea {
            width: 100%;
            padding: 12px 20px;
            margin: 10px 0;
            border: 2px solid #ff3d00; /* Orange border */
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="file"]:focus, input[type="url"]:focus, textarea:focus {
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
        .preview-container {
            margin: 10px 0;
            text-align: center;
        }
        .preview-container img {
            max-width: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <h2>Upload Your Profile Picture and Artwork</h2>
        <form action="artist.php" method="post" enctype="multipart/form-data">
            <h3>Upload Profile Picture:</h3>
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" name="profile_picture" id="profile_picture"><br>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="artwork_image">Upload Artwork Image:</label>
            <input type="file" name="artwork_image" id="artwork_image" required>

            <div class="preview-container">
                <img id="profile_preview" alt="Profile Picture Preview" style="display:none;">
                <img id="artwork_preview" alt="Artwork Preview" style="display:none;">
            </div>

            <h3>Social Media Links<h3>
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

    <?php if ($uploadSuccess): ?>
        <div class="upload-container">
            <h3>Uploaded Details:</h3>
            <p><strong>Description:</strong> <?php echo $uploadedData['description']; ?></p>
            <p><strong>Profile Picture:</strong></p>
            <img src="<?php echo $uploadedData['profile_picture']; ?>" alt="Profile Picture" width="150">
            <p><strong>Artwork Image:</strong></p>
            <img src="<?php echo $uploadedData['artwork_image']; ?>" alt="Artwork Image" width="150">
            <p><strong>X:</strong> <a href="<?php echo $uploadedData['x_link']; ?>" target="_blank"><?php echo $uploadedData['x_link']; ?></a></p>
            <p><strong>Instagram:</strong> <a href="<?php echo $uploadedData['instagram_link']; ?>" target="_blank"><?php echo $uploadedData['instagram_link']; ?></a></p>
            <p><strong>Facebook:</strong> <a href="<?php echo $uploadedData['facebook_link']; ?>" target="_blan
