<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use mysqli;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $conn; // Database connection

    public function __construct() {
        $this->clients = new \SplObjectStorage;

        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "artlink_entertainment";

        $this->conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        echo "Connected to the database successfully.\n"; // Debugging line, can be removed in production
    }

    public function onOpen(ConnectionInterface $conn) {
        // Attach new client to the list of clients
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Log received message for debugging
        error_log("Received message: " . $msg);
    
        // Decode the JSON message
        $data = json_decode($msg, true);
    
        // Extract sender and recipient details
        $sender_id = $data['sender_id'] ?? null;  // Get sender ID from the message
        $recipient_id = $data['recipient_id'] ?? null;  // Get recipient ID
        $sender_username = $data['sender_username'] ?? 'unknown';
        $recipient_username = $data['recipient_username'] ?? 'unknown';
        $message = $data['message'] ?? '';
    
        // Validate that we have required fields
        if (!$sender_id || !$recipient_id || !$message) {
            error_log("Invalid message data.");
            return;
        }
    
        // Store message in the database
        $stmt = $this->conn->prepare("INSERT INTO messages (sender_id, recipient_id, sender_username, recipient_username, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $sender_id, $recipient_id, $sender_username, $recipient_username, $message);
    
        if ($stmt->execute()) {
            error_log("Message stored successfully.");
    
            // Send message only to the intended recipient
            foreach ($this->clients as $client) {
                // Ensure the client is not the sender and matches the recipient
                if ($from !== $client && $client->resourceId == $recipient_id) {
                    $client->send(json_encode([
                        'type' => 'message',
                        'sender_id' => $sender_id,
                        'sender_username' => $sender_username,
                        'message' => $message
                    ]));
                }
            }
        } else {
            error_log("Error storing message: " . $stmt->error);
        }
    
        $stmt->close();
    }

    public function onClose(ConnectionInterface $conn) {
        // Remove client from the list when they disconnect
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        // Log and handle errors
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Set up the WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080
);

echo "WebSocket server running at ws://localhost:8080\n";

// Run the WebSocket server
$server->run();
