<?php
declare(strict_types = 1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

require_once(__DIR__ . '/../database/connection.php');

require_once(__DIR__ . '/../database/classes/service.class.php');
require_once(__DIR__ . '/../database/classes/user.class.php');
require_once(__DIR__ . '/../database/classes/serviceImage.class.php');
require_once(__DIR__ . '/../database/classes/category.class.php'); // Make sure Category is required if used by Service

require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/service.tpl.php');
require_once(__DIR__ . '/../templates/transactions.tpl.php');

  $db = getDatabaseConnection();

output_header($session, ['../css/order_service.css']);

// --- CHANGES START HERE ---

// 1. Validate if 'service_id' exists in $_GET
if (!isset($_GET['service_id'])) {
    // If not, redirect or show an error message
    $session->addMessage('error', 'Service ID not provided.');
    header('Location: ../index.php'); // Redirect to home page or a services listing
    exit(); // Always exit after a header redirect
}

$service_id = (int)$_GET['service_id']; // Cast to int for safety

// 2. Get the service. Handle the case where it might be null.
$service = Service::getServiceByID($db, $service_id);

if ($service === null) {
    // If no service is found with that ID, redirect or show an error
    $session->addMessage('error', 'Service not found.');
    header('Location: ../index.php'); // Redirect to home page
    exit(); 
}

$transactions = Transactions::getTransactionsAssociatedWithService($db, $service);
foreach ($transactions as $transaction) {
    if($transaction->getClient()->getId() === $session->getId()) {
        $session->addMessage('error', 'You have already purchased this service.');
        header('Location: ../index.php'); // Redirect to home page
        exit(); 
    }
}

// Now that we're sure $service is a Service object, proceed
$images = ServiceImage::getImagesByService($db, $service);

drawServiceDetails($service, $service->getFreelancer(), $session, $images);

drawTransactionForm($service, $session);

output_footer();
?>