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
    $role = $_POST['role'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    
    $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkUser->bind_param('ss', $username, $email);
    $checkUser->execute();
    $checkUser->store_result();

    if ($checkUser->num_rows > 0) {
        echo "Username or email already exists.";
    } else {
       
        $stmt = $conn->prepare("INSERT INTO users (role, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $role, $username, $email, $password);

        if ($stmt->execute()) {
            
            $user_id = $stmt->insert_id;

            if ($role === 'artist') {
                $stmt = $conn->prepare("INSERT INTO artwork (id, bio, profile_picture, x_link, instagram_link, facebook_link, linkedin_link) VALUES (?, '', '', '', '', '', '')");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
            }

           
            if ($role === 'artist') {
                $_SESSION['id'] = $user_id;  
                header("Location: artist.php");
                exit();
            } elseif ($role === 'audience') {
                header("Location: audience.php");
                exit();
            } elseif ($role === 'institution') {
                header("Location: institution.php");
                exit();
            }
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $checkUser->close();
}

$conn->close();
?>
