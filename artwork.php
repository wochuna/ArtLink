<?php
// Include database connection
include 'artistform.php'; // Replace with your actual database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $artist_id = $_POST['artist_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Handle the uploaded artwork image
    $artwork_image = $_FILES['artwork_image'];
    $image_path = 'uploads/' . basename($artwork_image['name']); // Set the path for the uploaded image

    // Move the uploaded file to the specified directory
    if (move_uploaded_file($artwork_image['tmp_name'], $image_path)) {
        // Prepare social media links
        $x_link = $_POST['x_link'];
        $instagram_link = $_POST['instagram_link'];
        $facebook_link = $_POST['facebook_link'];
        $linkedin_link = $_POST['linkedin_link'];

        // Insert artwork details into the database
        $stmt = $conn->prepare("INSERT INTO artworks (artist_id, title, description, image, x_link, instagram_link, facebook_link, linkedin_link) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $artist_id, $title, $description, $image_path, $x_link, $instagram_link, $facebook_link, $linkedin_link);

        if ($stmt->execute()) {
            echo "Artwork uploaded successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Failed to upload image.";
    }
}

$conn->close(); // Close the database connection
?>
