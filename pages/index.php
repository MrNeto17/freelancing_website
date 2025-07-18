<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/service.class.php');
require_once(__DIR__ . '/../database/classes/category.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/service.tpl.php');

$session = new Session();
$db = getDatabaseConnection();

// Obter parâmetros de filtro da URL
$category = $_GET['category'] ?? null;
$maxPrice = isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : null;
$maxDeliveryTime = isset($_GET['maxDeliveryTime']) ? (int)$_GET['maxDeliveryTime'] : null;

// Buscar serviços com filtros
$services = Service::getFilteredServices($db, $category, $maxPrice, $maxDeliveryTime);
$categories = Category::getAll($db); // Para o dropdown de categorias

output_header($session, ["../css/index.css"]);
drawServices($services, $session, $categories); // Agora passa $categories também
output_footer();
?>