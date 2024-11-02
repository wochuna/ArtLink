<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

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
        echo "Connected successfully\n"; // Debugging line, can be removed in production
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Log received message for debugging
        error_log("Received message: " . $msg);

        // Assuming the message is JSON formatted
        $data = json_decode($msg, true); // Decode the JSON message
        $sender_id = 1; // Example sender ID; replace with actual logic if needed
        $recipient_id = $data['recipient_id'] ?? null; // Optional: get recipient_id from message
        $sender_username = $data['sender_username'] ?? 'unknown'; // Default username
        $recipient_username = $data['recipient_username'] ?? 'unknown'; // Default username
        $message = $data['message'] ?? ''; // Extract the actual message

        // Prepare SQL statement
        $stmt = $this->conn->prepare("INSERT INTO messages (sender_id, recipient_id, sender_username, recipient_username, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $sender_id, $recipient_id, $sender_username, $recipient_username, $message);

        if ($stmt->execute()) {
            error_log("Message stored successfully.");
            // Broadcast the message to all other clients
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send($msg); // Send the original message to other clients
                }
            }
        } else {
            error_log("Error storing message: " . $stmt->error);
        }

        $stmt->close();
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }

    public function __destruct() {
        $this->conn->close(); // Close database connection
    }
}

$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new ChatServer()
        )
    ),
    8080
);

$server->run();
?>