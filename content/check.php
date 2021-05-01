<?php

if (!extension_loaded('bcmath')) { echo "bcmath extension missing."; die(); }
if (!extension_loaded('curl')) { echo "curl extension missing."; die(); }

$constants = get_defined_constants(true);
if (!array_key_exists('_domain',$constants['user'])) { echo "_domain not defined, you need to update your config.php"; die(); }
if (!array_key_exists('_cleanup',$constants['user'])) { echo "_cleanup not defined, you need to update your config.php"; die(); }

?>
