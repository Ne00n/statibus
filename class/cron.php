<?php

class cron {

  private $rqlite;
  private $uptime;

  public function __construct($rqliteIP,$rqlitePort) {
    $this->rqlite = new rqlite($rqliteIP,$rqlitePort);
  }

  public function run() {
    $services = $this->rqlite->select('SELECT * FROM services');
    $this->uptime = $this->rqlite->select('SELECT * FROM uptime');
    foreach ($services['values'] as $service) {
      print("Checking ".$service[4]."\n");
      if ($service[3] == "ping") {
        exec("ping -c 3 " . $service[4], $output, $result);
        if ($result == 0) { $status = 1; } else { $status = 0; }
        $this->updateStatus($service[0],$status,$service[2]);
      } elseif ($service[3] == "port") {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
          list($ip, $port) = explode("]:", $service[4]);
          $fp = fsockopen("[".$ip."]",$port, $errno, $errstr, $service[5]);
        } else {
          list($ip, $port) = explode(":", $service[4]);
          $fp = fsockopen($ip,$port, $errno, $errstr, $service[5]);
        }
        $status = (int)$fp;
        $this->updateStatus($service[0],$status,$service[2]);
      } elseif ($service[3] == "http") {
        $response = $this->rqlite->fetchData($service[4],"GET",NULL,True,$service[5]);
        if (strpos($service[6], ',') !== false) {  $statusCodes = explode( ',', $service[6]); } else { $statusCodes = array($service[6]); }
        if (in_array($response['http'], $statusCodes)) { $status = 1; } else { $status = 0; }
        $this->updateStatus($service[0],$status,$service[2]);
      }
    }
  }

  private function updateStatus($id,$current,$oldState) {
    $this->updateUptime($id);
    if ($current == 0 && $oldState == 1) {
      print($id." went offline\n");
      $this->rqlite->insert('INSERT INTO outages (serviceID,status,timestamp) VALUES("'.$id.'",0,'.time().')');
      $this->rqlite->update('UPDATE services SET status = 0 WHERE id="'.$id.'"');
    } elseif ($current == 1 && $oldState == 0) {
      print($id." went is back online\n");
      $this->rqlite->insert('INSERT INTO outages (serviceID,status,timestamp) VALUES("'.$id.'",1,'.time().')');
      $this->rqlite->update('UPDATE services SET status = 1 WHERE id="'.$id.'"');
    } else {
      print($id." no change\n");
    }
  }

  private function updateUptime($id) {
    $data = tools::getUptimeFromService($id,$this->uptime);
    if ($data == False) {
      $this->rqlite->insert('INSERT INTO uptime(serviceID,detailed,oneDay,sevenDays,fourteenDays,thirtyDays,ninetyDays) VALUES("'.$id.'","W10=","100.00","100.00","100.00","100.00","100.00")');
    }
  }

  private function calcWindow($outages,$window=1) {
    $line = time() - (86400 * $window); $last = 0; $total = 0;
    for ($i = 0; $i <= count($outages['values']) -1; $i++) {
      $row = $outages['values'][$i];
      if ($row[3] > $line) {
        if ($row[2] == 1 && $last != 0) { $total = $total + ($row[3] - $last); }
        if ($row[2] == 0) { $last = $row[3]; } else { $last = 0; }
      }
    }
    if ($last != 0) { $total = $total + (time() - $last); }
    return round($total / 60);
  }

  private function calcUptime($outages) {
    $response = array();
    $response[1] = 100 -bcmul( ($this->calcWindow($outages,1) / (1440 * 1) ),100,2);
    $response[7] = 100 -bcmul( ($this->calcWindow($outages,7) / (1440 * 7) ),100,2);
    $response[14] = 100 - bcmul( ($this->calcWindow($outages,14) / (1440 * 14) ),100,2);
    $response[30] = 100 - bcmul( ($this->calcWindow($outages,30) / (1440 * 30) ),100,2);
    $response[90] = 100 - bcmul( ($this->calcWindow($outages,90) / (1440 * 90) ),100,2);
    return $response;
  }

  private function generateDetailed($row,$outages) {
    if ($outages != NULL) { $data = $this->calcUptime($outages); } else { $data = array(); }
    $detailed = json_decode(base64_decode($row[1]),true); $current = date("d.m");
    if ($outages != NULL) { $detailed[$current] = $data[1]; } else { $detailed[$current] = 100; }
    $detailed = base64_encode(json_encode($detailed));
    return array("detailed" => $detailed,"data" => $data);
  }

  public function uptime() {
    $uptime = $this->rqlite->select('SELECT * FROM uptime');
    foreach ($uptime['values'] as $row) {
      $outages = $this->rqlite->select('SELECT * FROM outages WHERE serviceID = '.$row[0].' ');
      if (!isset($outages['values'])) {
        $response = $this->generateDetailed($row,NULL);
        $response = $this->rqlite->update('UPDATE uptime SET detailed = "'.$response['detailed'].'", oneDay = 100.00,sevenDays = 100.00,fourteenDays = 100.00,thirtyDays = 100.00,ninetyDays = 100.00 WHERE serviceID = '.$row[0].' ');
      } else {
        $response = $this->generateDetailed($row,$outages);
        $response = $this->rqlite->update('UPDATE uptime SET detailed = "'.$response['detailed'].'", oneDay = '.$response['data'][1].',sevenDays = '.$response['data'][7].',fourteenDays = '.$response['data'][14].',thirtyDays = '.$response['data'][30].',ninetyDays = '.$response['data'][90].' WHERE serviceID = '.$row[0].' ');
      }
    }
  }

}

?>
