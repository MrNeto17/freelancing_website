<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../utils/utils.php');
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/user.class.php');

// Headers de segurança
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

$session = new Session();
$db = getDatabaseConnection();

// Verificar se usuário está logado
if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'You must be logged in to edit your profile.');
    header('Location: /pages/index.php');
    exit();
}

$user = User::getUserByID($db, $session->getId());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proteção CSRF
    if (!isset($_POST['csrf_token']) || !$session->validateCSRFToken($_POST['csrf_token'])) {
        $session->addMessage('error', 'Invalid CSRF token.');
        header('Location: /pages/profile_edit_page.php');
        exit();
    }

    // Em vez de invalidar, podemos regenerar o token
    $session->regenerateCSRFToken();

    // Validação de entrada
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validação do nome
    if (strlen($name) > 100 || !preg_match('/^[\p{L}\s\'-]+$/u', $name)) {
        $session->addMessage('error', 'Invalid name. Only letters, spaces, hyphens and apostrophes are allowed (max 100 chars).');
        header('Location: /pages/profile_edit_page.php');
        exit();
    }

    // Validação de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
        $session->addMessage('error', 'Invalid email address.');
        header('Location: /pages/profile_edit_page.php');
        exit();
    }

    // Validação de username
    if (!preg_match('/^[a-zA-Z0-9_]{4,30}$/', $username)) {
        $session->addMessage('error', 'Username must be 4-30 chars and contain only letters, numbers and underscores.');
        header('Location: /pages/profile_edit_page.php');
        exit();
    }

    // Validação de telefone
    if (!empty($phone) && !preg_match('/^\+?[\d\s-]{6,20}$/', $phone)) {
        $session->addMessage('error', 'Invalid phone number (6-20 digits, may include +, spaces or hyphens).');
        header('Location: /pages/profile_edit_page.php');
        exit();
    }

    // Sanitização da descrição
    $description = !empty($description) ?
        htmlspecialchars($description, ENT_QUOTES, 'UTF-8') :
        null;

    // Validação de senha
    if (!empty($password) && !is_password_complex($password)) {
        $session->addMessage('error', 'Password must be at least 8 characters, include uppercase, lowercase, and numbers.');
        header('Location: /pages/profile_edit_page.php');
        exit();
    }

    // Verificar se email já está em uso
    $existingUser = User::getUserByEmail($db, $email);
    if ($existingUser && $existingUser->getId() !== $user->getId()) {
        $session->addMessage('error', 'Email already in use.');
        header('Location: /pages/profile_edit_page.php');
        exit();
    }

    // Atualizar dados do usuário
    $user->setName($db, $name);
    $user->setEmail($db, $email);
    $user->setUsername($db, $username);

    if (!empty($password)) {
        $user->setPassword($db, $password);
    }

    $user->updateProfile($db, $phone ?: null, $description ?: null);
    $session->setName($name);
    $session->addMessage('success', 'Profile updated successfully!');
    header('Location: /pages/profile.php?user_id=' . $session->getId());
    exit();
}