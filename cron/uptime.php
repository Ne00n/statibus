<?php

if (php_sapi_name() != 'cli') { exit(); }
include 'configs/config.php';

function dat_loader($class) {
    include 'class/' . $class . '.php';
}

spl_autoload_register('dat_loader');

$Cron = new cron(_rqliteIP,_rqlitePort);
$Cron->uptime();

?>
