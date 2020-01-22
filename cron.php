
require_once 'vendor/autoload.php';
$cron = new xvmpCron($_SERVER['argv']);
$cron->run();
$cron->logout();
