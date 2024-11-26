<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $userConnections;
    protected $conn; // Database connection

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];

        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "artlink_entertainment";

        $this->conn = new mysqli($servername, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
        echo "Connected to the database successfully.\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        // Extract user_id from query parameters
        $queryParams = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryParams, $params);

        $user_id = $params['user_id'] ?? null;
        if ($user_id) {
            $user_id = (int)$user_id;
            $this->userConnections[$user_id] = $conn;
            $conn->user_id = $user_id; // Attach user_id to connection object
            echo "User {$user_id} connected (resourceId: {$conn->resourceId}).\n";
        }

        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        error_log("Received message: " . $msg);

        $data = json_decode($msg, true);

        $sender_id = isset($data['sender_id']) ? (int)$data['sender_id'] : null;
        $recipient_id = isset($data['recipient_id']) ? (int)$data['recipient_id'] : null;
        $sender_username = isset($data['sender_username']) ? trim($data['sender_username']) : 'unknown';
        $message = isset($data['message']) ? trim($data['message']) : '';

        // Validate data
        if (!$sender_id || !$recipient_id || empty($message)) {
            error_log("Invalid message data.");
            $from->send(json_encode(['type' => 'error', 'message' => 'Invalid message data']));
            return;
        }

        // Ensure sender_id matches the authenticated connection
        if ($from->user_id !== $sender_id) {
            error_log("Sender ID mismatch.");
            $from->send(json_encode(['type' => 'error', 'message' => 'Unauthorized sender']));
            return;
        }

        // Store message in the database
        $stmt = $this->conn->prepare("INSERT INTO messages (sender_id, recipient_id, sender_username, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("iiss", $sender_id, $recipient_id, $sender_username, $message);
            if ($stmt->execute()) {
                error_log("Message stored successfully.");

                // Send the message only to the intended recipient
                if (isset($this->userConnections[$recipient_id])) {
                    $recipientConn = $this->userConnections[$recipient_id];
                    $recipientConn->send(json_encode([
                        'type' => 'message',
                        'sender_id' => $sender_id,
                        'sender_username' => $sender_username,
                        'message' => $message,
                        'recipient_id' => $recipient_id,
                        'timestamp' => date('Y-m-d H:i:s'),
                    ]));
                } else {
                    error_log("Recipient not connected.");
                }

                // Also notify the sender of success
                $from->send(json_encode([
                    'type' => 'confirmation',
                    'message' => 'Message sent successfully.',
                ]));
            } else {
                error_log("Error storing message: " . $stmt->error);
                $from->send(json_encode(['type' => 'error', 'message' => 'Failed to store message']));
            }
            $stmt->close();
        } else {
            error_log("Error preparing statement: " . $this->conn->error);
            $from->send(json_encode(['type' => 'error', 'message' => 'Database error']));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Remove user connection from the mapping
        if (isset($conn->user_id)) {
            unset($this->userConnections[$conn->user_id]);
            echo "User {$conn->user_id} disconnected (resourceId: {$conn->resourceId}).\n";
        }

        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
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

$server->run();
