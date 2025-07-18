<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../database/connection.php');

  require_once(__DIR__ . '/../database/classes/service.class.php');
  require_once(__DIR__ . '/../database/classes/user.class.php');

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/service.tpl.php');

  $db = getDatabaseConnection();



  output_header($session, ['/css/create_service.css']);
  $categories = Category::getAll($db);
  drawCreateServiceForm( $session, $categories);
  output_footer();
?>