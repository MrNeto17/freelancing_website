<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../utils/utils.php');
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/service.class.php');
require_once(__DIR__ . '/../database/classes/category.class.php');
require_once(__DIR__ . '/../database/classes/user.class.php');

$session = new Session();
$db = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$session->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $session->addMessage('error', 'Token inválido');
        header('Location: /pages/service_create_page.php');
        exit();
    }

    $title = sanitizeText(trim($_POST['title']));
    $description = sanitizeText(trim($_POST['description']));
    $price = (float)trim($_POST['price']);
    $deliveryTime = (int)trim($_POST['delivery_time']);
    $category_name = sanitizeText(trim($_POST['category']));

    // 2. Validação de Inputs
    if (empty($title) || strlen($title) > 100) {
        $session->addMessage('error', 'Título deve ter entre 1-100 caracteres');
        header('Location: /pages/service_create_page.php');
        exit();
    }

    if ($price <= 0 || $price > 9999.99) {
        $session->addMessage('error', 'Preço deve ser entre 0-9999.99');
        header('Location: /pages/service_create_page.php');
        exit();
    }

    if ($deliveryTime < 1 || $deliveryTime > 365) {
        $session->addMessage('error', 'Prazo de entrega deve ser 1-365 dias');
        header('Location: /pages/service_create_page.php');
        exit();
    }

    $category = Category::getCategory($db, $category_name);
    if (!$category) {
        $session->addMessage('error', 'Categoria inválida');
        header('Location: /pages/service_create_page.php');
        exit();
    }

    // 3. Verificação de Autorização
    $freelancer = User::getUserById($db, $session->getId());
    if ($freelancer->getRole() !== 'freelancer') {
        $session->addMessage('error', 'Apenas freelancers podem criar serviços');
        header('Location: /pages/index.php');
        exit();
    }

    try {
        $newservice = new Service(
            null,
            $title,
            $description,
            $price,
            $deliveryTime,
            $freelancer,
            $category,
            'pending'
        );
        $newservice->upload($db);

        $session->addMessage('success', 'Serviço criado com sucesso!');
        header('Location: /pages/index.php');
        exit();
    } catch (Exception $e) {
        error_log('Falha ao criar serviço: ' . $e->getMessage());
        $session->addMessage('error', 'Falha ao criar serviço');
        header('Location: /pages/service_create_page.php');
        exit();
    }
} else {
    header('Location: /pages/service_create_page.php');
    exit();
}