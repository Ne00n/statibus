<?php

if (php_sapi_name() != 'cli') { exit(); }
include 'configs/config.php';

function dat_loader($class) {
    include 'class/' . $class . '.php';
}

spl_autoload_register('dat_loader');

$statibus = new statibus(_rqliteIP,_rqlitePort);

if (count($argv) == 1) {
  print("service add <group> <name> <method> <target> <timeout> <httpcode(s)> <keyword>\n");
  print("group add <name>\n");
  print("remote add <name> <url>\n");
  print("service/group/remote delete <name>\n");
  print("service/group/remote list\n");
} else {

  if ($argv[1] == "init") {
    $statibus->sql()->init();
  } elseif ($argv[1] == 'service') {
    if ($argv[2] == 'add') {
      $statibus->serviceAdd($argv);
    } elseif ($argv[2] == 'list') {
      $statibus->serviceList();
    } elseif ($argv[2] == 'delete') {
      $statibus->serviceDelete($argv);
    }
  } elseif ($argv[1] == 'group') {
    if ($argv[2] == 'add') {
      $statibus->groupAdd($argv);
    } elseif ($argv[2] == 'list') {
      $statibus->groupList();
    } elseif ($argv[2] == 'delete') {
      $statibus->groupDelete($argv);
    }
  } elseif ($argv[1] == 'remote') {
    if ($argv[2] == 'add') {
      $statibus->remoteAdd($argv);
    } elseif ($argv[2] == 'list') {
      $statibus->remoteList();
    } elseif ($argv[2] == 'delete') {
      $statibus->remoteDelete($argv);
    }
  }

}

?>
