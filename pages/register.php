<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/classes/user.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/register.tpl.php');

$session = new Session();
$db = getDatabaseConnection();


output_header($session, ['../css/register.css']);

?>

<?php
drawRegisterForm($session);

output_footer();
?>