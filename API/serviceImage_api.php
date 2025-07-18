<?php
require_once('../database/connection.php');
require_once('../database/classes/serviceimage.class.php');

header('Content-Type: application/json');
$db = getDatabaseConnection();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$action = $request[0] ?? '';

$data = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'delete-image':
        if ($method === 'POST' && isset($data['url'])) {
            deleteImageByUrl($db, $data['url']);
        } else {
            echo json_encode(['error' => 'Invalid request for delete-image']);
        }
        break;

    default:
        echo json_encode(['error' => 'Action not found']);
        break;
}

function deleteImageByUrl(PDO $db, string $url): void {
    try {
        ServiceImage::deleteImage($db, $url);
        echo json_encode(['message' => 'Image deleted successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to delete image: ' . $e->getMessage()]);
    }
}
