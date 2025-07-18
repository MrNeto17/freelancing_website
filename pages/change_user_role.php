<?php
require_once('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $newRole = $_POST['new_role'];

    if (empty($username)) {
        header('Location: profile_page.php?error=Invalid Username');
        exit;
    }

    if (!in_array($newRole, ['freelancer', 'client'])) {
        header('Location: profile_page.php?error=Invalid Role Selected');
        exit;
    }

    $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: profile_page.php?error=User Not Found');
        exit;
    }

    $userId = $user['id'];

    $stmt = $db->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->execute([$newRole, $userId]);

    header('Location: profile_page.php?success=Role changed successfully');
    exit;
}
?>