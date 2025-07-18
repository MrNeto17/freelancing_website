<?php
declare(strict_types=1);

// 6. Headers de segurança
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) {
    die(header('Location: /'));
}

require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/service.class.php');
require_once(__DIR__ . '/../database/classes/category.class.php');
require_once(__DIR__ . '/../database/classes/serviceImage.class.php');

// Proteção CSRF
if (!isset($_POST['csrf_token']) || !$session->validateCSRFToken($_POST['csrf_token'])) {
    $session->addMessage('error', 'Token de segurança inválido');
    header('Location: /pages/index.php');
    exit();
}

$db = getDatabaseConnection();

// 1. Validação de entrada
$service_id = (int)($_POST['service_id'] ?? 0);
if ($service_id <= 0) {
    $session->addMessage('error', 'ID do serviço inválido');
    header('Location: /pages/service_edit_page.php' . urlencode((string)$service_id));
    exit();
}

$service = Service::getServiceByID($db, $service_id);
if (!$service) {
    $session->addMessage('error', 'Serviço não encontrado');
    header('Location: /pages/service_edit_page.php?service_id=' . urlencode((string)$service_id));
    exit();
}

try {
    // 1. Sanitização e validação dos campos
    $title = isset($_POST['title']) ? htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8') : '';
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8') : '';
    $price = (float)($_POST['price'] ?? 0);
    $deliveryTime = (int)($_POST['delivery_time'] ?? 0);
    $category_name = isset($_POST['category']) ? trim($_POST['category']) : '';
    $imageUrl = isset($_POST['image_url']) ? filter_var(trim($_POST['image_url']), FILTER_VALIDATE_URL) : '';

    // 3. Validações específicas
    if (empty($title) || strlen($title) > 100) {
        throw new Exception('O título deve ter entre 1 e 100 caracteres');
    }

    if ($price <= 0) {
        throw new Exception('O preço deve ser positivo');
    }

    if ($deliveryTime < 1 || $deliveryTime > 365) {
        throw new Exception('O prazo deve ser entre 1 e 365 dias');
    }

    $category = Category::getCategory($db, $category_name);
    if (!$category) {
        throw new Exception('Categoria inválida');
    }

    // Atualização do serviço (funcionalidade original mantida)
    $service->setTitle($title, $db);
    $service->setDescription($description, $db);
    $service->setPrice($price, $db);
    $service->setDeliveryTime($deliveryTime, $db);
    $service->setCategory($category, $db);

    if (!empty($imageUrl)) {
        $serviceImage = new ServiceImage($imageUrl, $service);
        $serviceImage->save($db);
    }

    $session->addMessage('success', 'Serviço atualizado com sucesso!');
    header('Location: ../pages/service_page.php?service_id=' . $service_id);
    exit();

} catch (Exception $e) {
    $session->addMessage('error', $e->getMessage());
    header('Location: /pages/service_edit_page.php?service_id=' . $service_id);
    exit();
}
?>