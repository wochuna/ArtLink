<?php 
// Start session to handle any messages
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #ff9a00, #ff3d00);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 400px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #3e3e3e;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            color: #3e3e3e;
        }

        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #ff3d00;
            border-radius: 10px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #ff3d00, #ff9a00);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        button:hover {
            background: linear-gradient(45deg, #ff9a00, #ff3d00);
        }

        .message {
            text-align: center;
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php
        if (isset($_SESSION['message'])) {
            echo '<p class="message">' . $_SESSION['message'] . '</p>';
            unset($_SESSION['message']);
        }
        ?>
        <form method="POST" action="reset-email.php">
            <label for="email">Enter your email address:</label>
            <input type="email" id="email" name="email" placeholder="Email address" required>
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>
</html>
