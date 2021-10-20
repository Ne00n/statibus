<?php

if (!extension_loaded('bcmath')) { echo "bcmath extension missing."; die(); }
if (!extension_loaded('curl')) { echo "curl extension missing."; die(); }

$constants = get_defined_constants(true);
$vars = array('_domain','_cleanup','_remoteThreshold','_remoteChecks','_timeFormat','_timeFormatDetails','_timeFormatRSS');
foreach ($vars as $key) {
  if (!array_key_exists($key,$constants['user'])) { echo $key." not defined, you need to update your config.php"; die(); }
}

?>
