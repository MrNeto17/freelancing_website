<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../database/connection.php');

  require_once(__DIR__ . '/../database/classes/user.class.php');
  require_once(__DIR__ . '/../database/classes/category.class.php');

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');

  if($session->getRole() !== 'admin') {
    die(header('Location: /'));
  }

  $db = getDatabaseConnection();

  output_header($session, ['/css/dashboard.css']);

  drawAdminDashboard($db, Category::getAll($db), $session);

  output_footer();
?>