<?php
declare(strict_types = 1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/service.class.php');
require_once(__DIR__ . '/../database/classes/user.class.php');
require_once(__DIR__ . '/../database/classes/serviceImage.class.php');

require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/service.tpl.php');

$db = getDatabaseConnection();
output_header($session, ['../css/service_page.css']);

$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

if ($service_id <= 0) {
    echo "<p>Invalid service ID.</p>";
    output_footer();
    exit();
}

$service = Service::getServiceByID($db, $service_id);

if (!$service) {
    echo "<p>Service not found.</p>";
    output_footer();
    exit();
}

$images = ServiceImage::getImagesByService($db, $service);

drawServiceDetails($service, $service->getFreelancer(), $session, $images);

output_footer();
?>
