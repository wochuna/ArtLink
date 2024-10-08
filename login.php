<?php 
session_start(); // Start session to access session variables
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - ArtLink Entertainment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="art.css">
    <script type="text/javascript" src="login.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff9a00, #ff3d00); /* Gradient background */
            height: 100vh;
            display: flex;
            flex-direction: column; /* Allow stacking of nav and login container */
        }
        
        .login-container {
            max-width: 400px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95); /* Semi-transparent white */
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            margin: auto;
            flex-grow: 1; /* Allow login container to grow */
        }

        h1 {
            color: #3e3e3e;
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            color: #3e3e3e;
            font-weight: bold;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px 20px;
            margin: 10px 0;
            border: 2px solid #ff3d00; /* Orange border */
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #ff9a00; /* Lighter orange on focus */
            outline: none;
        }

        button {
            background: linear-gradient(45deg, #ff3d00, #ff9a00); /* Gradient button */
            color: white;
            padding: 14px 20px;
            margin: 10px 0;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: linear-gradient(45deg, #ff9a00, #ff3d00); /* Reverse gradient on hover */
        }

        .signup-link {
            text-align: center;
            margin-top: 10px;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
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

    <div class="login-container">
        <h1>Login to Your Account</h1>
        
        <!-- Display error message if there is any -->
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']); // Clear error after displaying
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="process-login.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Login</button>
        </form>
        <div class="signup-link">
            <a href="signup.php" style="color: #ff3d00; font-weight: bold;">Don't have an account? Sign Up</a>
        </div>
    </div>
</body>
</html>
