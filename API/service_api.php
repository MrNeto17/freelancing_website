<?php
require_once('../database/connection.php');
require_once('../database/classes/service.class.php');

// Set the header to return JSON responses
header('Content-Type: application/json');
$db = getDatabaseConnection();

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Parse the URL to get the action (e.g., "mark-completed", "get-services")
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$action = $request[0] ?? '';

// Check if we have the necessary data
$data = json_decode(file_get_contents('php://input'), true);

// Handle different actions based on the HTTP method and action
switch ($action) {
    case 'mark-completed':
        if ($method === 'POST' && isset($data['service_id'])) {
            markServiceAsCompleted($db,$data['service_id']);
        } else {
            echo json_encode(['error' => 'Invalid request']);
        }
        break;
    
    case 'get-services':
        if ($method === 'GET') {
            getServices();
        } else {
            echo json_encode(['error' => 'Invalid request']);
        }
        break;
    case 'delete-service':
        if ($method === 'POST' && isset($data['service_id'])) {
            deleteService($db, $data['service_id']);
        } else {
            echo json_encode(['error' => 'Invalid request']);
        }
        break;
    case 'search-services':
        if ($method === 'GET' && isset($_GET['query'])) {
            searchServicesByTitle($db, $_GET['query']);
        } else {
            echo json_encode(['error' => 'Missing search query']);
        }
        break;

   
    default:
        echo json_encode(['error' => 'Action not found']);
        break;
}

function markServiceAsCompleted(PDO $db ,$serviceId) {
    try {
        $service = Service::getServiceByID($db, $serviceId);

        if ($service !== null) {
            $service->markAsCompleted($db);
            echo json_encode(['message' => 'Service marked as completed']);
        } else {
            echo json_encode(['error' => 'Service not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to update service: ' . $e->getMessage()]);
    }
}

function getServices() {
    global $db;
    try {
        $services = Service::getServices($db);
        $servicesArray = [];

        foreach ($services as $service) {
            $servicesArray[] = [
                'id' => $service->getId(),
                'title' => $service->getTitle(),
                'description' => $service->getDescription(),
                'price' => $service->getPrice(),
                'delivery_time' => $service->getDeliveryTime(),
                'category' => $service->getCategory()->getName(), 
                'freelancer' => [
                    'id' => $service->getFreelancer()->getId(),
                    'name' => $service->getFreelancer()->getName(),
                ],
            ];
        }

        echo json_encode(['services' => $servicesArray]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to retrieve services: ' . $e->getMessage()]);
    }
}


function deleteService(PDO $db, int $serviceId) {
    try {
        $service = Service::getServiceByID($db, $serviceId);

        if ($service !== null) {
            $service->delete($db); 
            echo json_encode(['message' => 'Service deleted successfully']);
        } else {
            echo json_encode(['error' => 'Service not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to delete service: ' . $e->getMessage()]);
    }
}


function searchServicesByTitle(PDO $db, string $query) {
    try {
        
        error_log('Search Query: ' . $query);
        
        
        $services = Service::searchByTitle($db, $query);

        $result = [];
        foreach ($services as $service) {
            $result[] = [
                'id' => $service->getId(),
                'title' => $service->getTitle(),
                'description' => $service->getDescription(),
                'price' => $service->getPrice(),
                'delivery_time' => $service->getDeliveryTime(),
                'category' => $service->getCategory()->getName(),
                'freelancer' => [
                    'id' => $service->getFreelancer()->getId(),
                    'name' => $service->getFreelancer()->getName(),
                ],
            ];
        }

        echo json_encode(['services' => $result]);
    } catch (Exception $e) {
        error_log('Error in searchServicesByTitle: ' . $e->getMessage());
        
        echo json_encode(['error' => 'Search failed: ' . $e->getMessage()]);
    }
}


?>


