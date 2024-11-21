<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "artlink_entertainment";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin["password"])) {
            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_username"] = $admin["username"];
            header("Location: admin.php");
            exit;
        } else {
            $errorMessage = "Invalid password.";
        }
    } else {
        $errorMessage = "Admin not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
       
        .login-container {
            max-width: 400px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95); 
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
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

        input[type="text"], input[type="password"], input[type="username"] {
            width: 100%;
            padding: 12px 20px;
            margin: 10px 0;
            border: 2px solid #ff3d00;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus, input[type="password"]:focus, input[type="username"]:focus {
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
        
        .signup-link {
            text-align: center;
            margin-top: 10px;
        }

        .signup-link a {
            color: #ff3d00;
            text-decoration: none;
        }

        .signup-link a:hover {
            text-decoration: underline;
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
        <li><a href="adminup.php">SIGN UP</a></li>
        <li><a href="adminlog.php">LOGIN</a></li>
    </ul>
</nav>
    <div class="login-container">
        <h1>Admin Login</h1>
        <?php if (!empty($errorMessage)) { ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php } ?>
        <form method="POST" action="">
            <input type="username" name="username" placeholder="Enter Username" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Log In</button>
        </form>
        <div class="signup-link">
             <a href="adminup.php" style="color: #ff3d00; font-weight: bold;">Don't have an account? Sign Up</a>
        </div>
        
    </div>
</body>
</html>
