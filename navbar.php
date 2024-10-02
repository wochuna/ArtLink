<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtLink Entertainment</title>
    <link rel="stylesheet" href="art.css"> <!-- Link to your external CSS file -->
    <style>
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
    </style>
</head>
<body>

    <nav>
        <img src="spice-it-up/Capture.PNG" alt="ArtLink Logo" class="logo"> <!-- Replace with your logo image -->
        <label class="logo">ArtLink Entertainment</label>
        <ul>
            <li><a href="index.php">HOME</a></li>
            <li><a href="about.php">ABOUT</a></li>
            <li><a href="sign-up.php">SIGN UP</a></li>
            <li><a href="login.php">LOGIN</a></li>
        </ul>
    </nav>

