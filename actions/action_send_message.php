<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ .'/../utils/utils.php');
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/service.class.php');
require_once(__DIR__ . '/../database/classes/message.class.php');
require_once(__DIR__ . '/../database/classes/user.class.php');

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

$session = new Session();
$db = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificação CSRF
    if (!$session->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $session->addMessage('error', 'Token de segurança inválido');
        header('Location: /pages/chat_page.php?user_id=' . ($_POST['receiver_id'] ?? ''));
        exit();
    }

    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    // Validação básica
    if ($receiverId <= 0 || empty($content)) {
        $session->addMessage('error', 'Destinatário ou mensagem inválidos');
        header('Location: /pages/chat_page.php?user_id=' . $receiverId);
        exit();
    }

    // Sanitização do conteúdo
    $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    $receiver = User::getUserByID($db, $receiverId);
    $sender = User::getUserByID($db, $session->getId());

    // Verificação de autoenvio
    if ($sender->getId() === $receiver->getId()) {
        $session->addMessage('error', 'Não pode enviar mensagem para si mesmo');
        header('Location: /pages/chat_page.php?user_id=' . $receiverId);
        exit();
    }

    try {
        // Cria e envia a mensagem
        $message = new Message(
            null,
            time(),
            $content,
            $sender,
            $receiver
        );
        $message->upload($db);

        // Redirecionamento CORRETO para a página de chat
        header('Location: /pages/chat_page.php?user_id=' . $receiverId);
        exit();

    } catch (Exception $e) {
        $session->addMessage('error', 'Erro ao enviar mensagem: ' . $e->getMessage());
        header('Location: /pages/chat_page.php?user_id=' . $receiverId);
        exit();
    }
} else {
    // Se não for POST, redireciona para a página inicial
    header('Location: /');
    exit();
}