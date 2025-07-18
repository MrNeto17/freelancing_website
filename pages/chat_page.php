<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../database/connection.php');

  require_once(__DIR__ . '/../database/classes/message.class.php');
  require_once(__DIR__ . '/../database/classes/user.class.php');

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/messages.tpl.php');

  $db = getDatabaseConnection();

  $otheruser = User::getUserByID($db, (int)$_GET['user_id']);
  $currentUser = User::getUserByID($db, $session->getId());
  $contacts = Message::getRecentContacts($db, $currentUser);

  output_header($session, ['/css/messages.css']);

  drawChatSection($session, $db, $otheruser);

  output_footer();
?>