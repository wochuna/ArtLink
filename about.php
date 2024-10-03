<?php?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Artist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="art.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
        }

        .video-background {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: -1;
            transform: translate(-50%, -50%);
            background: no-repeat;
            background-size: cover;
        }
        main {
            position: relative;
            z-index: 1;
            padding: 20px;
            color: white;
            overflow-y: auto;
            height: calc(100% - 90px);
        }

        section {
            margin-bottom: 30px;
        }

        h1, h2 {
            color: white;
        }

        ul {
            margin: 0;
            padding: 0 20px;
        }
    </style>
</head>
<body>
    <video autoplay muted loop class="video-background">
        <source src="spice-it-up/WhatsApp Video 2024-09-23 at 8.05.48 PM.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

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

    <main>
        <h1>About ArtLink</h1>
        <section>
            <h2>Our Mission, Vision, and Values</h2>
            <p>
                <strong>Mission:</strong> ArtLink is dedicated to empowering artists and fostering a thriving creative community. We aim to provide a platform that connects artists with audiences, facilitates collaboration, and enables artistic growth and discovery.
            </p>
            <p>
                <strong>Vision:</strong> Our vision is to revolutionize the way artists and audiences interact, creating a world where creativity is celebrated, and artistic expression flourishes. We strive to be the go-to platform for artists to showcase their work, connect with like-minded individuals, and find opportunities to grow their careers.
            </p>
            <p>
                <strong>Values:</strong>
                <ul>
                    <li>Inclusivity</li>
                    <li>Collaboration</li>
                    <li>Innovation</li>
                    <li>Accessibility</li>
                    <li>Excellence</li>
                </ul>
            </p>
        </section>

        <section>
            <h2>Key Features and Benefits</h2>
            <ul>
                <li>Comprehensive Artist Profiles</li>
                <li>Virtual Exhibitions and Events</li>
                <li>Collaboration Tools</li>
                <li>Audience Engagement</li>
                <li>Discovery and Promotion</li>
            </ul>
        </section>
    </main>
</body>
</html>
