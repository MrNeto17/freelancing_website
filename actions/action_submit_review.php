<?php

declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../utils/utils.php');
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/service.class.php');
require_once(__DIR__ . '/../database/classes/user.class.php');
require_once(__DIR__ . '/../database/classes/review.class.php');

$session = new Session();
$db = getDatabaseConnection();

// Proteção CSRF
if (!$session->validateCSRFToken($_POST['csrf_token'])) {
    $session->addMessage('error', 'Invalid security token');
    header('Location: ../pages/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = (int)$_POST['service_id'];
    $rating = (int)$_POST['rating'];
    $comment = $_POST['comment'];

    try {
        $service = Service::getServiceByID($db, $service_id);
        $user = User::getUserByID($db, $session->getId());

        if ($rating < 1 || $rating > 5) {
            throw new Exception('Invalid rating. It must be between 1 and 5.');
        }

        $review = new Review($service, $user, $rating, $comment);
        
        $review->upload($db);

        $service->getFreelancer()->setRating($db, 
        Review::calculateFreelancerAverageRating($db, $service->getFreelancer()->getId()));

        $session->addMessage('success', 'Your review has been submitted successfully!');
        header('Location: ../pages/service_page.php?service_id=' . $service_id);
        exit();
    } catch (Exception $e) {
        $session->addMessage('error', $e->getMessage());
        header('Location: ../pages/service_page.php?service_id=' . $service_id);
        exit();
    }
}
?>
