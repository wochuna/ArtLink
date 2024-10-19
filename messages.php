<?php 
// Start session to track logged-in user
session_start();

// Database connection details
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "artlink_entertainment";

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$id = $_SESSION['id']; // Logged-in user's id

// Handle sending a message (via POST request)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['receiver_id']) && isset($_POST['message'])) {
    $receiver_id = $_POST['receiver_id'];  // The ID of the user receiving the message
    $message = $_POST['message'];  // The message content

    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id, $receiver_id, $message);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }
    $stmt->close();
    exit();
}

// Handle fetching messages (via GET request)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['contact_id'])) {
    $contact_id = $_GET['contact_id'];  // The ID of the user you're chatting with

    $sql = "SELECT * FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
            ORDER BY timestamp ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $id, $contact_id, $contact_id, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode($messages);
    $stmt->close();
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Messaging</title>
    <link rel="stylesheet" href="messaging.css"> <!-- Optional external CSS file -->
</head>
<body>

    <nav>
        <img src="logo.png" alt="Logo" class="logo"> 
        <label class="logo">Messaging System</label>
        <ul>
            <li><a href="index.php">HOME</a></li>
            <li><a href="about.php">ABOUT</a></li>
            <li><a href="signup.php">SIGN UP</a></li>
            <li><a href="login.php">LOGIN</a></li>
        </ul>
    </nav>

    <div class="container">
        <!-- Sidebar for contacts -->
        <div class="sidebar">
            <a href="#">Profile</a>
            <a href="#" onclick="loadMessages(2)">Messages with User 2</a>
            <a href="#" onclick="loadMessages(3)">Messages with User 3</a>
        </div>

        <!-- Main content area -->
        <div class="content">
            <h2>Messages</h2>
            <div id="chat-box" class="chat-box">
                <!-- Chat messages will be loaded here -->
            </div>
            <div class="chat-input">
                <input type="text" id="chat-message" placeholder="Type a message">
                <button onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>

    <script>
    let selectedContactId = null;  // Default to no selected contact

    // Function to send a message
    function sendMessage() {
        const message = document.getElementById('chat-message').value;

        if (selectedContactId && message) {
            fetch('messaging.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    receiver_id: selectedContactId,  // The ID of the contact you're chatting with
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadMessages(selectedContactId);  // Reload messages after sending
                    document.getElementById('chat-message').value = '';  // Clear input
                } else {
                    alert("Failed to send message");
                }
            });
        } else {
            alert("Please select a contact and enter a message");
        }
    }

    // Function to load messages between the logged-in user and the selected contact
    function loadMessages(contactId) {
        selectedContactId = contactId;  // Track selected user for chat
        fetch(`messaging.php?contact_id=${contactId}`)
        .then(response => response.json())
        .then(messages => {
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = '';  // Clear previous messages

            messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message');
                messageDiv.textContent = msg.message;
                chatBox.appendChild(messageDiv);
            });
        });
    }

    // Poll for new messages every 5 seconds
    setInterval(() => {
        if (selectedContactId) {
            loadMessages(selectedContactId);
        }
    }, 5000);
    </script>

    <style>
    /* Basic styles for layout and chat box */
    .container {
        display: flex;
    }

    .sidebar {
        width: 200px;
        background-color: #f4f4f4;
        padding: 20px;
    }

    .sidebar a {
        display: block;
        padding: 10px;
        background-color: #007BFF;
        color: white;
        text-decoration: none;
        margin-bottom: 10px;
        text-align: center;
    }

    .content {
        flex: 1;
        padding: 20px;
    }

    .chat-box {
        height: 300px;
        overflow-y: scroll;
        border: 1px solid #ddd;
        padding: 10px;
        background-color: #f9f9f9;
        margin-bottom: 10px;
    }

    .chat-input {
        display: flex;
    }

    .chat-input input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
    }

    .chat-input button {
        padding: 10px;
        background-color: #007BFF;
        color: white;
        border: none;
        cursor: pointer;
    }

    .message {
        margin-bottom: 10px;
        padding: 5px;
        background-color: #e6f7ff;
        border: 1px solid #b3d9ff;
        border-radius: 5px;
    }
    </style>

</body>
</html>
