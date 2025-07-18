<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../database/connection.php');
  require_once(__DIR__ . '/../database/classes/service.class.php');
  require_once(__DIR__ . '/../database/classes/user.class.php');
  require_once(__DIR__ . '/../database/classes/category.class.php');

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/service.tpl.php');

  $db = getDatabaseConnection();



  output_header($session, ['/css/edit_service.css']);


  $categories = Category::getAll($db);

  $service_id = (int)$_GET['service_id'];

  $service = Service::getServiceByID($db, $service_id);


  drawEditServiceForm($service,$session,$categories);


  output_footer();
?>