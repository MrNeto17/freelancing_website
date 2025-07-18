<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ .'/../utils/utils.php');
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/user.class.php');

$session = new Session();
$db = getDatabaseConnection();

if (!isset($_SESSION['register_attempts'])) {
    $_SESSION['register_attempts'] = 0;
    $_SESSION['last_register_attempt'] = time();
}

if ($_SESSION['register_attempts'] > 8 && (time() - $_SESSION['last_register_attempt']) < 300) {
    die("Muitas tentativas. Espere 5 minutos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Correção CSRF
    if (!isset($_POST['csrf_token']) || !$session->validateCSRFToken($_POST['csrf_token'])) {
        $session->addMessage('error', 'Token de segurança inválido. Recarregue a página.');
        header('Location: /pages/register.php');
        exit();
    }

    // 2. Validação de entrada robusta
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8') : '';
    $role = isset($_POST['role']) ? htmlspecialchars(trim($_POST['role']), ENT_QUOTES, 'UTF-8') : 'client'; // valor padrão
    $phone = isset($_POST['phone']) ? filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING) : null;
    $username = getDefaultUsername($email);

    // 3. Removido session_start() duplicado (já é chamado no construtor de Session)

    // 4. Sanitização adicional
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8') : null;

    // Validação de campos obrigatórios
    if (!$email || empty($password) || empty($name) || empty($username)) {
        $_SESSION['register_attempts']++;
        $_SESSION['last_register_attempt'] = time();
        $session->addMessage('error', 'Todos os campos obrigatórios devem ser preenchidos corretamente.');
        header('Location: /pages/register.php');
        exit();
    }

    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $newUser = new User(
            null,
            $username,
            $email,
            $hashedPassword,
            $name,
            $phone,
            $role,
            $description,
            null  // rating
        );

        $newUser->upload($db);

        $_SESSION['register_attempts'] = 0;
        $session->addMessage('success', 'Registro realizado com sucesso! Você já pode fazer login.');
        header('Location: /pages/index.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['register_attempts']++;
        $_SESSION['last_register_attempt'] = time();
        $session->addMessage('error', 'Erro no registro: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        header('Location: /pages/register.php');
        exit();
    }
} else {
    header('Location: /pages/register.php');
    exit();
}
?>