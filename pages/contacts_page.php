<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  if (!$session->isLoggedIn()) {
    header('Location: ../pages/index.php');
    exit;
  }

  require_once(__DIR__ . '/../database/connection.php');

  require_once(__DIR__ . '/../database/classes/message.class.php');
  require_once(__DIR__ . '/../database/classes/user.class.php');

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/messages.tpl.php');

  $db = getDatabaseConnection();


  output_header($session, ['../css/contacts.css']);
  $currentUser = User::getUserByID($db, $session->getId());

  $contacts = Message::getRecentContacts($db, $currentUser);

  drawContactSection($session, $contacts, $db);



  output_footer();
?>