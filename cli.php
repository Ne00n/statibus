<?php

if (php_sapi_name() != 'cli') { exit(); }
include 'configs/config.php';

function dat_loader($class) {
    include 'class/' . $class . '.php';
}

spl_autoload_register('dat_loader');

$statibus = new statibus(_rqliteIP,_rqlitePort);

if (count($argv) == 1) {
  print("service, group, remote\n");
} else {

  if ($argv[1] == "init") {
    $statibus->sql()->init();
  } elseif ($argv[1] == 'service') {
    if (isset($argv[2])) {
      switch ($argv[2]) {
        case "add":
            $statibus->serviceAdd($argv);
            break;
        case "list":
            $statibus->list("services");
            break;
        case "delete":
            $statibus->delete("services",$argv);
            break;
      }
    } else {
      print("service add <group> <name> <method> <target> <timeout> <httpcode(s)> <keyword>\n");
      print("service list\n");
      print("service delete <name>\n");
    }
  } elseif ($argv[1] == 'group') {
    if (isset($argv[2])) {
      switch ($argv[2]) {
        case "add":
            $statibus->groupAdd($argv);
            break;
        case "list":
            $statibus->list("groups");
            break;
        case "delete":
            $statibus->delete("groups",$argv);
            break;
      }
    } else {
      print("group add <name>\n");
      print("group list\n");
      print("group delete <name>\n");
    }
  } elseif ($argv[1] == 'remote') {
    if (isset($argv[2])) {
      switch ($argv[2]) {
        case "add":
            $statibus->remoteAdd($argv);
            break;
        case "list":
            $statibus->list("remotes");
            break;
        case "delete":
            $statibus->delete("remotes",$argv);
            break;
      }
    } else {
      print("remote add <name> <url>\n");
      print("remote list\n");
      print("remote delete <name>\n");
    }
  }

}

?>
