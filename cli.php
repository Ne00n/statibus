<?php

if (php_sapi_name() != 'cli') { exit(); }
include 'configs/config.php';

function dat_loader($class) {
    include 'class/' . $class . '.php';
}

spl_autoload_register('dat_loader');

$rqlite = new rqlite(_rqliteIP,_rqlitePort);

if (count($argv) == 1) {
  print("add <name> <ping,port,http> <ip,ip:port,url>\n");
  print("delete <id>\n");
  print("list\n");
} else {
  if ($argv[1] == "init") {
    $response = $rqlite->init();
  } elseif ($argv[1] == "list") {
    print("Loading...\n");
    $response = $rqlite->select('SELECT * FROM services');
    if (!isset($response['error']) && $response != False ) { var_dump($response['values']); } else { print("Error: ".($response != False ? $response['error'] : "rqlite not reachable.")."\n"); }
  } elseif ($argv[1] == "add") {
    if (!isset($argv[5])) { $argv[5] = 3; }
    if (!isset($argv[6])) { $argv[6] = 200; }
    $response = $rqlite->insert('INSERT INTO services(name,status,method,target,timeout,httpcodes) VALUES("'.$argv[2].'",1,"'.$argv[3].'","'.$argv[4].'","'.$argv[5].'","'.$argv[6].'")');
    if (isset($response['error']) && $response != False) { print("Error: ".($response != False ? $response['error'] : "rqlite not reachable.")."\n"); }
    $response = $rqlite->insert('INSERT INTO uptime(serviceID,detailed,oneDay,sevenDays,fourteenDays,thirtyDays,ninetyDays) VALUES("'.$response["content"]["results"][0]["last_insert_id"].'","W10=","100.00","100.00","100.00","100.00","100.00")');
    if (!isset($response['error']) && $response != False) { print("Success\n"); } else { print("Error: ".($response != False ? $response['error'] : "rqlite not reachable.")."\n"); }
  } elseif ($argv[1] == "delete") {
    $response = $rqlite->delete('DELETE FROM services WHERE id='.$argv[2]);
    if (!isset($response['error']) && $response != False ) { print("Success\n"); } else { print("Error: ".($response != False ? $response['error'] : "rqlite not reachable.")."\n"); }
  }
}

?>
