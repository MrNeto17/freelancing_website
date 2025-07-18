<?php
declare(strict_types=1);

// Basic security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ .'/../utils/utils.php');
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/service.class.php');
require_once(__DIR__ . '/../database/classes/transactions.class.php');
require_once(__DIR__ . '/../database/classes/user.class.php');

$session = new Session();
$db = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic CSRF protection (without breaking flow)
    if (empty($_POST['csrf_token']) || !$session->validateCSRFToken($_POST['csrf_token'])) {
        $session->addMessage('error', 'Invalid request. Please refresh the page.');
        header('Location: /pages/order_service.php');
        exit();
    }

    // Input processing (keeping functionality but safer)
    $user_id = $session->getId();
    $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $firstName = isset($_POST['firstName']) ? htmlspecialchars(substr(trim($_POST['firstName']), 0, 100), ENT_QUOTES, 'UTF-8') : '';
    $lastName = isset($_POST['lastName']) ? htmlspecialchars(substr(trim($_POST['lastName']), 0, 100), ENT_QUOTES, 'UTF-8') : '';
    $subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : 0.0;
    $currency = in_array($_POST['currency'] ?? 'USD', ['USD', 'EUR', 'GBP']) ? $_POST['currency'] : 'USD';

    try {
        // Validate essential inputs
        if (!$service_id || !$email || !$firstName || !$lastName || $subtotal <= 0) {
            throw new Exception('Please fill all required fields correctly.');
        }

        // Get service and user with basic validation
        $service = Service::getServiceByID($db, $service_id);
        $user = User::getUserByID($db, $user_id);

        if (!$service || !$user) {
            throw new Exception('Invalid service or user.');
        }


        // Create and process transaction (original functionality preserved)
        $transaction = new Transactions(
            null,
            $subtotal,
            $firstName,
            $lastName,
            $email,
            $user,
            null,
            $service
        );

        $transaction->upload($db);
        $service->markAsInProgress($db);

        // Success - maintain original flow
        $session->addMessage('success', 'Transaction successful!');
        header('Location: /pages/index.php');
        exit();

    } catch (Exception $e) {
        // Error handling - preserve original behavior but sanitize output
        $session->addMessage('error', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        header('Location: /pages/order_service.php?service_id=' . urlencode((string)$service_id));
        exit();
    }
} else {
    // Non-POST requests - original behavior
    header('Location: /pages/order_service.php?service_id=' . urlencode((string)$service_id));
    exit();
}
?>