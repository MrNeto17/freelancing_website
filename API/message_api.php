<?php
require_once('../database/connection.php');
require_once('../database/classes/message.class.php');
require_once('../database/classes/user.class.php');
require_once('../utils/session.php');

header('Content-Type: application/json');

$db = getDatabaseConnection();
$session = new Session();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$action = $request[0] ?? '';

$data = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'get-between':
        if ($method === 'GET' && isset($_GET['user1']) && isset($_GET['user2'])) {
            $lastId = isset($_GET['last_id']) ? intval($_GET['last_id']) : PHP_INT_MAX;
            getMessagesBetweenUsers($db, intval($_GET['user1']), intval($_GET['user2']), $lastId);
        } else {
            jsonError('Invalid GET request for get-between');
        }
        break;

    case 'send':
        if ($method === 'POST') {
            if (!isset($data['sender_id'], $data['receiver_id'], $data['content'])) {
                jsonError('Missing fields');
            } else {
                sendMessage($db, $session, $data['sender_id'], $data['receiver_id'], $data['content']);
            }
        } else {
            jsonError('Invalid POST request for send');
        }
        break;

    case 'contacts':
        if ($method === 'GET' && isset($_GET['user_id'])) {
            getRecentContacts($db, intval($_GET['user_id']));
        } else {
            jsonError('Missing user_id for contacts');
        }
        break;

    case 'delete-message':
        if ($method === 'DELETE') {
            
            if (!isset($data['message_id'])) {
                jsonError('Missing message_id');
            } else {
                deleteMessage($db, $session, intval($data['message_id']));
            }
        } else {
            jsonError('Invalid DELETE request for delete');
        }
        break;

    default:
        jsonError('Invalid action');
        break;
}

function getMessagesBetweenUsers(PDO $db, int $user1Id, int $user2Id, int $lastId) {
    $user1 = User::getUserByID($db, $user1Id);
    $user2 = User::getUserByID($db, $user2Id);
    if (!$user1 || !$user2) {
        jsonError('One or both users not found');
        return;
    }

    $messages = Message::getMessages($db, $user1, $user2, $lastId);
    $jsonMessages = array_map(fn($m) => [
        'id' => $m->getId(),
        'datetime' => $m->getDatetime(),
        'content' => $m->getContent(),
        'sender_id' => $m->getSender()->getId(),
        'receiver_id' => $m->getReceiver()->getId()
    ], $messages);

    echo json_encode(['messages' => $jsonMessages]);
}

function sendMessage(PDO $db, Session $session, int $senderId, int $receiverId, string $content) {
    if (!$session->isLoggedIn() || $session->getId() !== $senderId) {
        jsonError('Unauthorized');
        return;
    }

    $sender = User::getUserByID($db, $senderId);
    $receiver = User::getUserByID($db, $receiverId);

    if (!$sender || !$receiver) {
        jsonError('Invalid user IDs');
        return;
    }

    $msg = new Message(null, time(), $content, $sender, $receiver);
    $msg->upload($db);

    echo json_encode(['message' => 'Message sent', 'id' => $msg->getId()]);
}

function getRecentContacts(PDO $db, int $userId) {
    $user = User::getUserByID($db, $userId);
    if (!$user) {
        jsonError('User not found');
        return;
    }

    $contacts = Message::getRecentContacts($db, $user);
    echo json_encode(['contacts' => $contacts]);
}

function deleteMessage(PDO $db, Session $session, int $messageId) {
    if (!$session->isLoggedIn()) {
        jsonError('Unauthorized');
        return;
    }

    $success = Message::deleteById($db, $messageId);
    if ($success) {
        echo json_encode(['message' => 'Message deleted']);
    } else {
        jsonError('Failed to delete message');
    }
}


function jsonError(string $msg) {
    echo json_encode(['error' => $msg]);
}
