<?php
declare(strict_types = 1);

// Headers de segurança devem vir primeiro
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/user.class.php');
require_once(__DIR__ . '/../utils/utils.php');

$session = new Session();
$db = getDatabaseConnection();

error_log("CSRF Session: " . $session->getCSRFToken());
error_log("CSRF POST: " . $_POST['csrf_token']);

// Verificação CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$session->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $session->addMessage('error', 'Token CSRF inválido. Atualize a página e tente novamente.');
        header('Location: /index.php');
        exit();
    }
}

// Controle de tentativas de login
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

$block_time = 300; // 5 minutos
if ($_SESSION['login_attempts'] >= 7 && (time() - $_SESSION['last_attempt_time']) < $block_time) {
    die("Muitas tentativas. Tente novamente em 5 minutos.");
}

// Validação de entrada
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    $session->addMessage('error', 'Email ou senha inválidos.');
    header('Location: /index.php');
    exit;
}

// Autenticação
$user = User::getUserByEmail($db, $email);

if (!$user || !$user->validatePassword($password)) {
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    $session->addMessage('error', 'Email ou senha inválidos.');
    header('Location: /index.php');
    exit;
}

// Login bem-sucedido
$_SESSION['login_attempts'] = 0;
$session->setId($user->getId());
$session->setName($user->getName());
$session->setRole($user->getRole());
$session->addMessage('success', 'Login realizado com sucesso!');

// Redirecionamento seguro
$redirectUrl = '/index.php'; // Página padrão após login
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/index.php') === false) {
    $redirectUrl = $_SERVER['HTTP_REFERER'];
}

header("Location: $redirectUrl");
exit;
?>