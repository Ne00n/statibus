<?php

if (php_sapi_name() != 'cli') { exit(); }
include 'configs/config.php';

function dat_loader($class) {
    include 'class/' . $class . '.php';
}

spl_autoload_register('dat_loader');

$rqlite = new rqlite(_rqliteIP,_rqlitePort);

if (count($argv) == 1) {
  print("<service> add <name> <method> <target> <timeout> <httpcode(s)>\n");
  print("<group> add <name>\n");
  print("<service/group> delete <id>\n");
  print("<service/group> list\n");
} else {
  if ($argv[1] == "init") {
    $rqlite->init();
  } elseif ($argv[1] == 'service') {
    if ($argv[2] == 'add') {
      if (!isset($argv[7])) { $argv[7] = 3; }
      if (!isset($argv[8])) { $argv[8] = 200; }
      if (!isset($argv[9])) { $argv[9] = null; }
      $response = $rqlite->insert('INSERT INTO services(groupID,name,status,method,target,timeout,httpcodes,keyword) VALUES("'.$argv[3].'","'.$argv[4].'",1,"'.$argv[5].'","'.$argv[6].'","'.$argv[7].'","'.$argv[8].'","'.$argv[9].'")');
      if (isset($response['error']) && $response != False) { print("Error: ".($response != False ? $response['error'] : "rqlite not reachable.")."\n"); die(); }
      $response = $rqlite->insert('INSERT INTO uptime(serviceID,detailed,oneDay,sevenDays,fourteenDays,thirtyDays,ninetyDays) VALUES("'.$response["content"]["results"][0]["last_insert_id"].'","W10=","100.00","100.00","100.00","100.00","100.00")');
      print(tools::checkResult($response));
    } elseif ($argv[2] == 'list') {
      $response = $rqlite->select('SELECT * FROM services',True);
      if (empty($response)) { echo "No services added.\n"; die(); }
      tools::checkRow($response);
      foreach ($response['rows'] as $row) { print_r($row); }
    } elseif ($argv[2] == 'delete') {
      $response = $rqlite->delete('DELETE FROM services WHERE id='.$argv[3]);
      print(tools::checkResult($response));
    }
  } elseif ($argv[1] == 'group') {
    if ($argv[2] == 'add') {
      $response = $rqlite->insert('INSERT INTO groups(name) VALUES("'.$argv[3].'")');
      print(tools::checkResult($response));
    } elseif ($argv[2] == 'list') {
      $response = $rqlite->select('SELECT * FROM groups',True);
      if (empty($response)) { echo "No groups added.\n"; die(); }
      tools::checkRow($response);
      foreach ($response['rows'] as $row) { print_r($row); }
    } elseif ($argv[2] == 'delete') {
      $response = $rqlite->delete('DELETE FROM groups WHERE id='.$argv[3]);
      print(tools::checkResult($response));
    }
  }
}

?>
