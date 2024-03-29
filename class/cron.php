<?php

class cron {

  private $statibus;
  private $rqlite;
  private $uptime;

  public function __construct($rqliteIP,$rqlitePort) {
    $this->rqlite = new rqlite($rqliteIP,$rqlitePort);
    $this->statibus = new statibus($rqliteIP,$rqlitePort);
  }

  private function setRemote($id,$status) {
    return $this->rqlite->update(['UPDATE remotes SET status = ?,lastrun = ? WHERE id=?',$status,time(),$id]);
  }

  public function run() {
    print("Checking Remotes\n");
    $remotes = $this->rqlite->select(['SELECT * FROM remotes'],True);
    if (isset($remotes['rows'][0])) {
      foreach ($remotes['rows'] as $remote) {
        $response = $this->rqlite->fetchData($remote['url'],"GET",NULL,True,2);
        if ($response['http'] == 200 && json_last_error() === 0) {
          $content = json_decode($response['content'],true);
          if (isset($content['status']) && $content['status'] == "ok") {
            $this->setRemote($remote['id'],1);
            continue;
          }
        }
        $this->setRemote($remote['id'],0);
      }
    } else {
      echo "No Remotes found, skipping\n";
    }

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
    if (empty($services)) { echo "No Services found.\n"; die(); }
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
         $rss .= '<pubDate>' . date(_timeFormatRSS, $event['timestamp']) . '</pubDate></item>'."\r\n";
    }
    $rss .= "</channel>\r\n</rss>";
    file_put_contents('feed.rss', $rss);

    if (_cleanup == 0) { return False; }
    echo "Cleaning up history\n";
    $deadline = time() - (86400 * _cleanup);
    $this->rqlite->delete(['DELETE FROM outages WHERE timestamp < ?',$deadline]);
    return True;
  }

  public function check($options) {
    $data = $this->rqlite->select(['SELECT * FROM services WHERE id=?',$options['i']],True);
    if (!isset($data['rows'][0])) { echo "Entry not found.\n"; die(); }
    $remotes = $this->rqlite->select(['SELECT * FROM remotes WHERE status = ?',1],True);

    $data = $data['rows'][0];
    $ipv6 = filter_var($data['target'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    print("Checking ".$data['id']."\n");

    if ($data['method'] == "ping") {
      if ($ipv6) {
        exec("ping6 -c 3 " . $data['target'], $output, $result);
      } else {
        exec("ping -c 3 " . $data['target'], $output, $result);
      }
      if ($result == 0) { $status = 1; } else { $status = 0; }

    } elseif ($data['method'] == "port") {
      if ($ipv6) {
        list($ip, $port) = explode("]:", $data['target']);
        $fp = fsockopen("[".$ip."]",$port, $errno, $errstr, $data['timeout']);
      } else {
        list($ip, $port) = explode(":", $data['target']);
        $fp = fsockopen($ip,$port, $errno, $errstr, $data['timeout']);
      }
      if ($fp) { $status = 1; } else { $status = 0; }

    } elseif ($data['method'] == "http") {
      $response = $this->rqlite->fetchData($data['target'],"GET",NULL,True,$data['timeout']);
      $status = $this->checkHTTPResponse($data['httpcodes'],$response['http'],$data['keyword'],$response['content']);

    } else {
      echo "Method not supported.\n";
      return False;
    }
    $status = $this->remoteCheck($remotes,$status,$data);
    $this->updateStatus($data['id'],$status,$data['status']);
    return True;
  }

  private function checkHTTPResponse($httpcodes,$http,$keyword,$content) {
    if (strpos($httpcodes, ',') !== false) {  $statusCodes = explode( ',', $httpcodes); } else { $statusCodes = array($httpcodes); }
    if (in_array($http, $statusCodes) && $keyword == "") {
      return 1;
    } elseif (in_array($http, $statusCodes) && strpos($content, $keyword) !== false) {
      return 1;
    } else {
      return 0;
    }
  }

  private function getUniqueRemote($remotes,&$checks) {
    for ($i=0; $i < 15; $i++) {
      $check = mt_rand(0,count($remotes) -1);
      if (!in_array($check, $checks)) {
        $checks[] = $check;
        return $check;
      }
    }
    return $check;
  }

  private function remoteCheck($remotes,$status,$service) {
    if (!isset($remotes['rows'][0])) { echo "No Remotes found, skipping\n"; return $status; }
    if ($status != 0) { return $status; }
    $remotes = $remotes['rows'];
    $checks = array(); $failed = 0; $success = 0; $errors = 0;

    for ($i=1; $i <= _remoteChecks; $i++) {
      $check = $this->getUniqueRemote($remotes,$checks);
      $remote = $remotes[$check];

      $payload = json_encode(array('target' => $service['target'],'type' => $service['method'],'timeout' => $service['timeout']));
      $response = $this->rqlite->fetchData($remote['url'],"POST",$payload,True,$service['timeout'] * 3);
      echo $remote['name']." response ".$response['http']."\n";

      if ($response['http'] == 200) {
        $content = json_decode($response['content'],true);
        if ($service['method'] == 'http') {
          $status = $this->checkHTTPResponse($data['httpcodes'],$response['http'],$service['keyword'],$content['content']);
          echo ($status ? "Online" : 'Offline')."\n";
          if ($status) { $success++; } else { $failed++; }
        } else {
          echo ($content['result'] ? "Online" : 'Offline')."\n";
          if ($content['result']) { $success++; } else { $failed++; }
        }
      } elseif ($response['http'] == 0) {
        $errors++;
      } else {
        $failed++;
      }
    }
    if ($errors == _remoteChecks) { return 1; }
    if ($success >= _remoteThreshold) { return 1; }
    return 0;
  }

  private function updateStatus($id,$current,$oldState) {
    if ($current == 0 && $oldState == 1) {
      print($id." went offline\n");
      $this->rqlite->insert(['INSERT INTO outages (serviceID,status,timestamp) VALUES(?,?,?)',$id,0,time()]);
      $this->rqlite->update(['UPDATE services SET status = ?,lastrun = ? WHERE id=?',0,time(),$id]);
    } elseif ($current == 1 && $oldState == 0) {
      print($id." is back online\n");
      $this->rqlite->insert(['INSERT INTO outages (serviceID,status,timestamp) VALUES(?,?,?)',$id,1,time()]);
      $this->rqlite->update(['UPDATE services SET status = ?,lastrun = ? WHERE id=?',1,time(),$id]);
    } else {
      $this->rqlite->update(['UPDATE services SET lastrun = ? WHERE id=?',time(),$id]);
      print($id." no change\n");
    }
  }

  private function calcWindow($outages,$window=1,$detail=False) {
    $last = 0; $total = 0;
    if ($detail) { $line = strtotime('today'); } else { $line = time() - (86400 * $window); }
    for ($i = 0; $i <= count($outages['rows']) -1; $i++) {
      $row = $outages['rows'][$i];
      if ($row['timestamp'] > $line) {
        if ($row['status'] == 1 && $last != 0) { $total = $total + ($row['timestamp'] - $last); }
        if ($row['status'] == 0) { $last = $row['timestamp']; } else { $last = 0; }
      }
      if (count($outages['rows']) -1 == $i and $row['timestamp'] < $line and $row['status'] == 0) {
        $total = $total + (time() - $line);
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
    //Same as 1, however for the details we need a fixed midnight window
    $response[24] = 100 - bcmul( bcdiv($this->calcWindow($outages,1,True),1440 * 1,6) ,100,6);
    $response[30] = 100 - bcmul( bcdiv($this->calcWindow($outages,30),1440 * 30,6) ,100,6);
    $response[90] = 100 - bcmul( bcdiv($this->calcWindow($outages,90),1440 * 90,6) ,100,6);
    return $response;
  }

  private function generateDetailed($row,$outages) {
    if ($outages != NULL) { $data = $this->calcUptime($outages); } else { $data = array(); }
    $detailed = json_decode(base64_decode($row['detailed']),true); $current = strtotime('today midnight');
    if ($outages != NULL) { $detailed[$current] = $data[24]; } else { $detailed[$current] = 100; }
    //Cleanup
    $deadline = time() - (86400 * _cleanup);
    foreach ($detailed as $timestamp => $percentage) {
      if (_cleanup == 0) { break; }
      if ($timestamp < $deadline) { unset($detailed[$timestamp]); }
    }
    $detailed = base64_encode(json_encode($detailed));
    return array("detailed" => $detailed,"data" => $data);
  }

  public function uptime() {
    $uptime = $this->rqlite->select(['SELECT * FROM uptime'],True);
    foreach ($uptime['rows'] as $row) {
      $outages = $this->rqlite->select(['SELECT * FROM outages WHERE serviceID = ? AND flag is null',$row['serviceID']],True);
      if (!isset($outages['rows'][0])) {
        $response = $this->generateDetailed($row,NULL);
        $response = $this->rqlite->update(['UPDATE uptime SET detailed = ?, oneDay = ?,sevenDays = ?,fourteenDays = ?,thirtyDays = ?,ninetyDays = ? WHERE serviceID = ?',$response['detailed'],100.00,100.00,100.00,100.00,100.00,$row['serviceID']]);
      } else {
        $response = $this->generateDetailed($row,$outages);
        $response = $this->rqlite->update(['UPDATE uptime SET detailed = ?, oneDay = ?,sevenDays = ?,fourteenDays = ?,thirtyDays = ?,ninetyDays = ? WHERE serviceID = ?',$response['detailed'],$response['data'][1],$response['data'][7],$response['data'][14],$response['data'][30],$response['data'][90],$row['serviceID']]);
      }
    }
    return True;
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
    return True;
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
