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
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/edit_profile.tpl.php');

$db = getDatabaseConnection();
$user = User::getUserByID($db, $session->getId());

output_header($session, ['../css/profile_edit_page.css']);
drawEditProfileForm($user, $session);
output_footer();
?>