<?php
require_once('../database/connection.php');
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
    case 'get-user':
        if ($method === 'GET' && isset($_GET['user_id'])) {
            getUser($db, intval($_GET['user_id']));
        } else {
            jsonError('Invalid GET request');
        }
        break;
    case 'elevate-user':
        if ($method === 'POST') {
            if (!isset($data['user_id'])) {
                jsonError('Missing user_id in payload');
            } else {
                ElevateUser($db, $session, intval($data['user_id']));
            }
        } else {
            jsonError('Invalid POST request');
        }
        break;
    
    default:
        jsonError('Action not found');
        break;
}

function getUser(PDO $db, int $userId) {
    $user = User::getUserByID($db, $userId);
    if (!$user) {
        jsonError('User not found');
        return;
    }

    echo json_encode([
        'id' => $user->getId(),
        'username' => $user->getUsername(),
        'email' => $user->getEmail(),
        'role' => $user->getRole(),
        'name' => $user->getName(),
        'rating' => $user->getRating(),
        'description' => $user->getDescription(),
        'phone' => $user->getPhone()
    ]);
}


function ElevateUser(PDO $db, Session $session, int $userId) {
    if (!$session->isLoggedIn() || $session->getRole() !== 'admin') {
        jsonError('Unauthorized');
        return;
    }

    $user = User::getUserByID($db, $userId);
    if (!$user) {
        jsonError('User not found');
        return;
    }

    $user->ElevateToAdmin($db);
    echo json_encode(['message' => 'User elevated to admin successfully']);
}


function jsonError(string $msg) {
    echo json_encode(['error' => $msg]);
}
