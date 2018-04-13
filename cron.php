<?php
require_once 'classes/class.xvmpCron.php';
$cron = new xvmpCron($_SERVER['argv']);
$cron->run();
$cron->logout();