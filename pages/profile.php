<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) {
    header('Location: /pages/login.php');
    exit();
}

require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/user.class.php');
require_once(__DIR__ . '/../database/classes/service.class.php');
require_once(__DIR__ . '/../database/classes/transactions.class.php');
require_once(__DIR__ . '/../database/classes/review.class.php');

require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/profile.tpl.php');

$db = getDatabaseConnection();



// Get current user
$user = User::getUserByID($db, (int)$_GET['user_id'] ?? $session->getId());

// Get user's services if they're a freelancer
$services = [];
$reviews = [];
if ($user->getRole() === 'freelancer') {
    $services = Service::getServicesByFreelancer($db, $user->getId());
    $reviews = Review::getReviewsByFreelancer($db, $user->getId());
}

$transactions = Transactions::getTransactionsByClient($db, $user);
output_header($session, ['../css/profile.css']);
drawProfile($user, $services, $session, $transactions, $reviews);
output_footer();
?>