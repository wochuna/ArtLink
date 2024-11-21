<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "artlink_entertainment";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT * FROM users";
$result = $conn->query($sql);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST["user_id"];
    
  
    $delete_sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>User deleted successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error deleting user: " . $stmt->error . "</p>";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="art.css"> 
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #ff9a00, #ff3d00);
        }

        .content {
            padding: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #ff3d00;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        input[type="submit"] {
            background-color: #ff3d00;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #ff9a00;
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }

            table {
                font-size: 14px;
            }
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

    <div class="content">
        <h1>Admin Dashboard</h1>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $username = isset($row["username"]) ? $row["username"] : "N/A";
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . $username . "</td>";
                    echo "<td>" . $row["email"] . "</td>";
                    echo "<td>
                            <form method='post' action=''>
                                <input type='hidden' name='user_id' value='" . $row["id"] . "'>
                                <input type='submit' value='Delete'>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No users found.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>