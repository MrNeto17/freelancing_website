<?php
require_once('../database/connection.php');
require_once('../database/classes/category.class.php');

header('Content-Type: application/json');
$db = getDatabaseConnection();

$method = $_SERVER['REQUEST_METHOD'];

$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$action = $request[0] ?? '';

$data = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'add-category':
        if ($method === 'POST' && isset($data['name'])) {
            addCategory($db, $data['name']);
        } else {
            echo json_encode(['error' => 'Invalid request']);
        }
        break;

    case 'delete-category':
        if ($method === 'POST' && isset($data['name'])) {
            deleteCategory($db, $data['name']);
        } else {
            echo json_encode(['error' => 'Invalid request']);
        }
        break;

    default:
        echo json_encode(['error' => 'Action not found']);
        break;
}

function addCategory(PDO $db, string $name) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM CATEGORY WHERE name = :name");
        $stmt->bindParam(":name", $name);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['error' => 'Category already exists']);
            return;
        }

        $category = new Category($name);
        $category->upload($db);

        echo json_encode(['message' => 'Category added successfully']);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to add category: ' . $e->getMessage()]);
    }
}

function deleteCategory(PDO $db, string $name) {
    try {
        $category = Category::getCategory($db, $name);
        
        if ($category) {
            Category::deleteCategory($db, $name);
            echo json_encode(['message' => 'Category deleted successfully']);
        } else {
            echo json_encode(['error' => 'Category not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to delete category: ' . $e->getMessage()]);
    }
}
?>
