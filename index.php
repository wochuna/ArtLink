<?php?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Home - ArtLink Entertainment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="art.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff9a00, #ff3d00); /* Gradient background */
            color: #3e3e3e; /* Dark gray text color */
            display: flex;
            flex-direction: column;
            height: 100vh; /* Full viewport height */
        }
        .content {
            flex-grow: 1; /* Allow content area to grow */
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        p {
            font-size: 18px;
            line-height: 1.5;
            max-width: 600px;
            margin: auto;
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

    <div class="content">
        <div>
            <h1>Welcome to ArtLink Entertainment!</h1>
            <p>
                Discover amazing artwork, connect with talented artists, and attend virtual events. 
                Join our community to explore the world of art and creativity.
            </p>
            <p>
                Whether you are an artist, an audience member, or part of an artistic institution, 
                ArtLink offers something for everyone. Start your journey today!
            </p>
        </div>
    </div>
</body>
</html>
