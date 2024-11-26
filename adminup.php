<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "artlink_entertainment";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO admins (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit;
    } else {
        echo "<p style='color: red; text-align: center;'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign-Up</title>
    <link rel="stylesheet" href="art.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff9a00, #ff3d00);
            height: 100vh;
            display: flex;
            flex-direction: column; 
        }
        .signup-container {
            max-width: 400px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            margin: auto;
            flex-grow: 1;
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
        
        input[type="text"], input[type="email"], input[type="password"], select {
            width: 100%;
            padding: 12px 20px;
            margin: 10px 0;
            border: 2px solid #ff3d00;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
       
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, select:focus {
            border-color: #ff9a00; 
            outline: none;
        }
        
        button {
            background: linear-gradient(45deg, #ff3d00, #ff9a00); 
            color: white;
            padding: 14px 20px;
            margin: 10px 0;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: linear-gradient(45deg, #ff9a00, #ff3d00);
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
        <li><a href="adminup.php">SIGN UP</a></li>
        <li><a href="adminlog.php">LOGIN</a></li>
    </ul>
</nav>
    <div class="signup-container">
        <h1>Admin Sign-Up</h1>
        <form method="POST" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter Username" required>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter Email" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html>
