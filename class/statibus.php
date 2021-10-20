<?php

class statibus {

  private $rqlite;

  public function __construct($rqliteIP,$rqlitePort) {
    $this->rqlite = new rqlite($rqliteIP,$rqlitePort);
  }

  public function isDownTimeHuh($data) {
    if (isset($data['rows'])) {
      foreach ($data['rows'] as $row) {
        if ($row['status'] == 0) { return True; }
      }
    }
    return False;
  }

  public function serviceAdd($params) {
    $response = $this->rqlite->select(['SELECT id FROM groups WHERE name=?',$params[3]],True);
    if (isset($response['rows'])) { $groupID = $response['rows'][0]['id']; } else { echo "Error: Group not found\n"; return False; }

    if (!isset($params[7])) { $params[7] = 3; }
    if (!isset($params[8])) { $params[8] = 200; }
    if (!isset($params[9])) { $params[9] = ""; }

    $response = $this->rqlite->insert(['INSERT INTO services(groupID,name,status,method,target,timeout,httpcodes,keyword) VALUES(?, ?, ?, ?, ?, ?, ?, ?)',$groupID,$params[4],1,$params[5],$params[6],$params[7],strval($params[8]),$params[9]]);
    if (isset($response['error']) && $response != False) { print("Error: ".($response != False ? $response['error'] : "rqlite not reachable.")."\n"); return False; }

    $response = $this->rqlite->insert(['INSERT INTO uptime(serviceID,detailed,oneDay,sevenDays,fourteenDays,thirtyDays,ninetyDays) VALUES(?, ?, ?, ? ,? ,? ,?)',$response["content"]["results"][0]["last_insert_id"],"W10=","100.00","100.00","100.00","100.00","100.00"]);
    $status = tools::checkResult($response);
    print($status."\n"); if ($status != "Success") { return False; }
    return True;
  }

  public function groupAdd($params) {
    $response = $this->rqlite->insert(['INSERT INTO groups(name) VALUES(?)',$params[3]]);

    $status = tools::checkResult($response);
    print($status."\n"); if ($status != "Success") { return False; }
    return True;
  }

  public function remoteAdd($params) {
    $response = $this->rqlite->insert(['INSERT INTO remotes(name,url) VALUES(?,?)',$params[3],$params[4]]);

    $status = tools::checkResult($response);
    print($status."\n"); if ($status != "Success") { return False; }
    return True;
  }

  public function list($table) {
    $response = $this->rqlite->select(['SELECT * FROM '.$table],True);
    if (empty($response)) { echo json_encode(array('error' => 'Nothing added.'),JSON_PRETTY_PRINT)."\n"; return False; }

    tools::checkRow($response);
    echo json_encode($response['rows'],JSON_PRETTY_PRINT)."\n";
    return True;
  }

  public function delete($table,$params) {
    $response = $this->rqlite->delete(['DELETE FROM '.$table.' WHERE name=?',$params[3]]);

    $status = tools::checkResult($response);
    print($status."\n"); if ($status != "Success") { return False; }
    return True;
  }

  public function gimmahDowntimePercentaaages($data) {
    $response = array('oneDay' => 0,'sevenDays' => 0,'thirtyDays' => 0);
    if (isset($data['rows'])) {
      foreach ($data['rows'] as $row) {
        $response['oneDay'] += $row['oneDay'];
        $response['sevenDays'] += $row['sevenDays'];
        $response['thirtyDays'] += $row['thirtyDays'];
      }
    } else {
      return array('oneDay' => 100,'sevenDays' => 100,'thirtyDays' => 100);
    }
    $response['oneDay'] = round($response['oneDay'] / count($data['rows']),2);
    $response['sevenDays'] = round($response['sevenDays'] / count($data['rows']),2);
    $response['thirtyDays'] = round($response['thirtyDays'] / count($data['rows']),2);
    return $response;
  }

  public function getOutagesArray($serviceID=0) {
    $outages = $this->rqlite->select(['SELECT o.id,o.status,o.timestamp,o.flag,s.name,s.id as serviceID FROM outages as o JOIN services as s ON s.id=o.serviceID WHERE serviceID=? ORDER BY timestamp DESC',$serviceID],True);

    $response = array();

    if (isset($outages['rows'][0])) {
      $closed = False;
      for ($i = 0; $i <= count($outages['rows']) -1; $i++) {
        $row = $outages['rows'][$i]; $before = $outages['rows'][($i == 0 ? 0 : $i -1)];
        if ($row['status'] == 0 && !$closed) {
          $response[$row['id']] = $row;
          $response[$row['id']]['header'] = 'Downtime';
          $response[$row['id']]['message'] = 'since '.date(_timeFormatDetails, $outages['rows'][$i]['timestamp']);
          $response[$row['id']]['downtime'] = 'ongoing';
        } elseif ($row['status'] == 0) {
          $diff = round( ($outages['rows'][$i -1]['timestamp'] - $outages['rows'][$i]['timestamp']) / 60);
          $response[$before['id']]['message'] = date(_timeFormatDetails, $outages['rows'][$i]['timestamp']).' until '.date(_timeFormatDetails, $outages['rows'][$i -1]['timestamp']);
          $response[$before['id']]['downtime'] = tools::escape($diff);
          $closed = False;
         } elseif ($row['status'] == 1) {
          $response[$row['id']] = $row;
          $response[$row['id']]['header'] = ($outages['rows'][$i +1]['flag'] != NULL ? 'Origin Network issue' : 'Downtime');
          $closed = True;
        }
      }
    }
    return $response;
  }

  public function getColor($percentage) {
    if ($percentage == 100) {
      return "green";
    } elseif ($percentage < 100 && $percentage > 99) {
       return "darkgreen";
    } elseif ($percentage < 99 && $percentage > 97) {
      return "orange";
    } else {
      return "red";
    }
  }

  public function sql() {
    return $this->rqlite;
  }

}

?>
