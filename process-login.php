<?php 

session_start();


$servername = "localhost";
$dbUsername = "root";  
$dbPassword = "";      
$dbName = "artlink_entertainment"; 


$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

   
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

       
        echo "Stored Hashed Password: " . $row['password'] . "<br>";

        if (password_verify($password, $row['password'])) {
            
            $_SESSION['username'] = $row['username'];
            $_SESSION['id'] = $row['id']; 
            $_SESSION['role'] = $row['role'];

           
            if ($row['role'] === 'artist') {
                header("Location: artist.php");
            } elseif ($row['role'] === 'audience') {
                header("Location: audience.php");
            } elseif ($row['role'] === 'institution') {
                header("Location: institution.php");
            } else {
               
                header("Location: default.php");
            }
            exit();
        } else {
            
            $_SESSION['error'] = "Invalid username or password. Please try again.";
            header("Location: login.php"); 
            exit();
        }
    } else {
       
        $_SESSION['error'] = "Invalid username or password. Please try again.";
        header("Location: login.php");
        exit();
    }
}


$stmt->close();
$conn->close();
?>
