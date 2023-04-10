<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

// Initialize the Slim app
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
// Initialize the SQLite database connection
$pdo = new PDO('sqlite:chat.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create the "users" table if it does not exist
$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
)');

// Create the "messages" table if it does not exist
$pdo->exec('CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sender_id INTEGER NOT NULL,
    receiver_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(sender_id) REFERENCES users(id),
    FOREIGN KEY(receiver_id) REFERENCES users(id)
)');


// Endpoint for creating a new user
$app->post('/users', function (Request $request, Response $response, array $args) use ($pdo) {
    $data = $request->getParsedBody();
    $name = $data['name'];
    $stmt = $pdo->prepare('INSERT INTO users (name) VALUES (:name)');
    $stmt->execute(['name' => $name]);
    $user = [
        'id' => $pdo->lastInsertId(),
        'name' => $name
    ];
    $response->getBody()->write(json_encode($user));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

// Endpoint for getting all users
$app->get('/users', function (Request $request, Response $response, array $args) use ($pdo) {
    $stmt = $pdo->query('SELECT id, name FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});

// Endpoint for sending a message
$app->post('/messages', function (Request $request, Response $response, array $args) use ($pdo) {
    $data = $request->getParsedBody();
    $sender_id = $data['sender_id'];
    $receiver_id = $data['receiver_id'];
    $content = $data['content'];
    $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, content) VALUES (:sender_id, :receiver_id, :content)');
    $stmt->execute(['sender_id' => $sender_id, 'receiver_id' => $receiver_id, 'content' => $content]);
    $message = [
        'id' => $pdo->lastInsertId(),
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id,
        'content' => $content,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    $response->getBody()->write(json_encode($message));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

// Endpoint for getting all messages for a given user
$app->get('/messages/{user_id}', function (Request $request, Response $response, array $args) use ($pdo) {
// Extract the user ID from the URL parameter
    $user_id = $args['user_id'];
// Query the database for all messages received by the user
    $stmt = $pdo->prepare('SELECT messages.id, messages.sender_id, messages.content, messages.timestamp, users.name AS sender_name
FROM messages
JOIN users ON messages.sender_id = users.id
WHERE messages.receiver_id = :user_id
ORDER BY messages.timestamp ASC');
    $stmt->execute(['user_id' => $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($messages));
    return $response->withHeader('Content-Type', 'application/json');
});

// Run the Slim app
$app->run();
