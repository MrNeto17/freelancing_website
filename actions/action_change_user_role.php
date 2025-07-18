<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

// 1. Authentication & Authorization
if (!$session->isLoggedIn() || $session->getRole() !== 'admin') {
    die(header('Location: /'));
}

// 2. CSRF Protection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$session->validateCSRFToken($_POST['csrf_token'] ?? '')) {
    die("Invalid CSRF token");
}

require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/user.class.php');

$db = getDatabaseConnection();

// 3. Input Validation
$username = $_POST['username'] ?? '';
$newRole = $_POST['new_role'] ?? '';

$allowed_roles = ['client', 'freelancer', 'admin'];
if (!in_array($newRole, $allowed_roles)) {
    $session->addMessage('error', 'Invalid role specified');
    header('Location: /pages/admin_dashboard_page.php');
    exit;
}

// 4. Business Logic
$user = User::getUserByUsername($db, $username);
if (!$user) {
    $session->addMessage('error', 'User not found');
    header('Location: /pages/admin_dashboard_page.php');
    exit;
}

if ($user->getRole() === $newRole) {
    $session->addMessage('error', 'User already has this role');
    header('Location: /pages/admin_dashboard_page.php');
    exit;
}

if ($user->getRole() === 'freelancer' && $newRole === 'client') {
    $session->addMessage('error', 'Cannot downgrade freelancer to client');
    header('Location: /pages/admin_dashboard_page.php');
    exit;
}

// Apply changes
$user->setType($db, $newRole);
$session->addMessage('success', 'Role updated successfully');
header('Location: /pages/admin_dashboard_page.php');
exit;
?>