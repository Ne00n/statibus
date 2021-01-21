<?php

$startTime = microtime(true);
include 'configs/config.php';
include 'content/check.php';

function dat_loader($class) {
    include 'class/' . $class . '.php';
}

spl_autoload_register('dat_loader');

include 'content/header.php';

if (isset($_GET["service"])) {
  include 'content/service.php';
} else {
  include 'content/main.php';
}

include 'content/footer.php';

?>
