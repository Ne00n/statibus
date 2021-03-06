<?php

class cron {

  private $statibus;
  private $rqlite;
  private $uptime;

  public function __construct($rqliteIP,$rqlitePort) {
    $this->rqlite = new rqlite($rqliteIP,$rqlitePort);
    $this->statibus = new statibus($rqliteIP,$rqlitePort);
  }

  public function run() {
    $services = $this->rqlite->select('SELECT * FROM services',True);
    if (isset($services['rows'][0])) {
      foreach ($services['rows'] as $row) {
        echo "Running /usr/bin/php cron/runner.php -i ".$row['id']."\n";
        backgroundProcess::startProcess("/usr/bin/php cron/runner.php -i ".$row['id']);
      }
    }

    sleep(20);

    $events = array();

    $services = $this->rqlite->select(['SELECT id FROM services'],True);
    foreach ($services['rows'] as $service) {
      $outages = $this->statibus->getOutagesArray($service['id']);
      $events = array_merge($events,$outages);
    }

    usort($events, function($a, $b) {
        return $b['timestamp'] <=> $a['timestamp'];
    });

    $rss = '<?xml version="1.0" encoding="UTF-8" ?>'."\r\n".'<rss version="2.0">'."\r\n"."<channel>\r\n";
    foreach ($events as $event) {
         $rss .= '<item>';
         $rss .= '<title>'.$event['name'].' '.$event['header'].'</title>';
         $rss .= '<link>https://'._domain.'/index.php?service='.$event['serviceID'].'</link>';
         $rss .= '<description>'.$event['message'].'</description>';
         $rss .= '<pubDate>' . date("D, d M Y H:i:s O", $event['timestamp']) . '</pubDate></item>'."\r\n";
    }
    $rss .= "</channel>\r\n</rss>";
    file_put_contents('feed.rss', $rss);
  }

  public function check($options) {
    $data = $this->rqlite->select(['SELECT * FROM services WHERE id=?',$options['i']],True);
    if (!isset($data['rows'][0])) { echo "Entry not found.\n"; die(); }
    $data = $data['rows'][0];
    print("Checking ".$data['id']."\n");
    if ($data['method'] == "ping") {
      exec("ping -c 3 " . $data['target'], $output, $result);
      if ($result == 0) { $status = 1; } else { $status = 0; }
      $this->updateStatus($data['id'],$status,$data['status']);
    } elseif ($data['method'] == "port") {
      if (filter_var($data['target'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        list($ip, $port) = explode("]:", $data['target']);
        $fp = fsockopen("[".$ip."]",$port, $errno, $errstr, $service[5]);
      } else {
        list($ip, $port) = explode(":", $data['target']);
        $fp = fsockopen($ip,$port, $errno, $errstr, $data['timeout']);
      }
      if ($fp) { $status = 1; } else { $status = 0; }
      $this->updateStatus($data['id'],$status,$data['status']);
    } elseif ($data['method'] == "http") {
      $response = $this->rqlite->fetchData($data['target'],"GET",NULL,True,$data['timeout']);
      if (strpos($data['httpcodes'], ',') !== false) {  $statusCodes = explode( ',', $data['httpcodes']); } else { $statusCodes = array($data['httpcodes']); }
      if (in_array($response['http'], $statusCodes) && $data['keyword'] == "") {
        $status = 1;
      } elseif (in_array($response['http'], $statusCodes) && strpos($response['content'], $data['keyword']) !== false) {
        $status = 1;
      } else {
        $status = 0;
      }
      $this->updateStatus($data['id'],$status,$data['status']);
    } else {
      echo "Method not supported.\n";
    }
  }

  private function updateStatus($id,$current,$oldState) {
    if ($current == 0 && $oldState == 1) {
      print($id." went offline\n");
      $this->rqlite->insert(['INSERT INTO outages (serviceID,status,timestamp) VALUES(?,?,?)',$id,0,time()]);
      $this->rqlite->update(['UPDATE services SET status = ?,lastrun = ? WHERE id=?',0,time(),$id]);
    } elseif ($current == 1 && $oldState == 0) {
      print($id." went is back online\n");
      $this->rqlite->insert(['INSERT INTO outages (serviceID,status,timestamp) VALUES(?,?,?)',$id,1,time()]);
      $this->rqlite->update(['UPDATE services SET status = ?,lastrun = ? WHERE id=?',1,time(),$id]);
    } else {
      $this->rqlite->update(['UPDATE services SET lastrun = ? WHERE id=?',time(),$id]);
      print($id." no change\n");
    }
  }

  private function calcWindow($outages,$window=1) {
    $last = 0; $total = 0;
    #If the selected window is 1 = 24h, downtimes will be only count from midnight
    if ($window == 1) { $line = strtotime('today'); } else { $line = time() - (86400 * $window); }
    for ($i = 0; $i <= count($outages['values']) -1; $i++) {
      $row = $outages['values'][$i];
      if ($row[3] > $line) {
        if ($row[2] == 1 && $last != 0) { $total = $total + ($row[3] - $last); }
        if ($row[2] == 0) { $last = $row[3]; } else { $last = 0; }
      }
    }
    if ($last != 0) { $total = $total + (time() - $last); }
    return bcdiv($total,60,2);
  }

  private function calcUptime($outages) {
    $response = array();
    $response[1] = 100 - bcmul( bcdiv($this->calcWindow($outages,1),1440 * 1,6) ,100,6);
    $response[7] = 100 - bcmul( bcdiv($this->calcWindow($outages,7),1440 * 7,6) ,100,6);
    $response[14] = 100 - bcmul( bcdiv($this->calcWindow($outages,14),1440 * 14,6) ,100,6);
    $response[30] = 100 - bcmul( bcdiv($this->calcWindow($outages,30),1440 * 30,6) ,100,6);
    $response[90] = 100 - bcmul( bcdiv($this->calcWindow($outages,90),1440 * 90,6) ,100,6);
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
    $uptime = $this->rqlite->select(['SELECT * FROM uptime']);
    foreach ($uptime['values'] as $row) {
      $outages = $this->rqlite->select(['SELECT * FROM outages WHERE serviceID = ? AND flag is null',$row[0]]);
      if (!isset($outages['values'])) {
        $response = $this->generateDetailed($row,NULL);
        $response = $this->rqlite->update(['UPDATE uptime SET detailed = ?, oneDay = ?,sevenDays = ?,fourteenDays = ?,thirtyDays = ?,ninetyDays = ? WHERE serviceID = ?',$response['detailed'],100.00,100.00,100.00,100.00,100.00,$row[0]]);
      } else {
        $response = $this->generateDetailed($row,$outages);
        $response = $this->rqlite->update(['UPDATE uptime SET detailed = ?, oneDay = ?,sevenDays = ?,fourteenDays = ?,thirtyDays = ?,ninetyDays = ? WHERE serviceID = ?',$response['detailed'],$response['data'][1],$response['data'][7],$response['data'][14],$response['data'][30],$response['data'][90],$row[0]]);
      }
    }
  }

  public function findFalsePositives() {
    $uptime = $this->rqlite->select(['SELECT * FROM uptime'],True);
    #Check entries within 24 hours
    $line = time() - 86400;
    $outages = $this->rqlite->select(['SELECT * FROM outages WHERE timestamp > ? AND status = 0',$line],True);
    if (isset($outages['rows'])) {
      foreach ($outages['rows'] as $row) {
        $start = $row['timestamp'] -5; $end = $row['timestamp'] + 5;
        $matches = $this->searchScope($start,$end,$outages);
        foreach ($matches as $key => $match) {
          if ( $match / count($uptime['rows']) * 100 > 50) {
            $this->rqlite->update(['UPDATE outages SET flag = 1 WHERE timestamp = ?',$key]);
          }
        }
      }
    }
  }

  private function searchScope($start,$end,$outages) {
    $response = array();
    foreach ($outages['rows'] as $row) {
      if ($row['timestamp'] > $start && $row['timestamp'] < $end) {
        if (!isset($response[$row['timestamp']])) { $response[$row['timestamp']] = 1; } else { $response[$row['timestamp']]++; }
      }
    }
    return $response;
  }

}

?>
