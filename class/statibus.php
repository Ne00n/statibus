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
    $outages = $this->rqlite->select('SELECT o.id,o.status,o.timestamp,o.flag,s.name,s.id as serviceID FROM outages as o JOIN services as s ON s.id=o.serviceID WHERE serviceID='.$serviceID.' ORDER BY timestamp DESC ',True);

    $response = array();

    if (isset($outages['rows'][0])) {
      $closed = False;
      for ($i = 0; $i <= count($outages['rows']) -1; $i++) {
        $row = $outages['rows'][$i]; $before = $outages['rows'][($i == 0 ? 0 : $i -1)];
        if ($row['status'] == 0 && !$closed) {
           $response[$row['id']]['header'] = 'Downtime';
           $response[$row['id']]['message'] = 'since '.date('d M H:i', $outages['rows'][$i]['timestamp']);
           $response[$row['id']]['timestamp'] = $row['timestamp'];
           $response[$row['id']]['downtime'] = 'ongoing';
           $response[$row['id']]['name'] = $row['name'];
           $response[$row['id']]['serviceID'] = $row['serviceID'];
         } elseif ($row['status'] == 0) {
             $diff = round( ($outages['rows'][$i -1]['timestamp'] - $outages['rows'][$i]['timestamp']) / 60);
           $response[$before['id']]['message'] = date('d M H:i', $outages['rows'][$i]['timestamp']).' until '.date('d M H:i', $outages['rows'][$i -1]['timestamp']);
           $response[$before['id']]['downtime'] = tools::escape($diff);
           $closed = False;
         } elseif ($row['status'] == 1) {
           $response[$row['id']]['header'] = ($outages['rows'][$i +1]['flag'] != NULL ? 'Origin Network issue' : 'Downtime');
           $response[$row['id']]['timestamp'] = $row['timestamp'];
           $response[$row['id']]['serviceID'] = $row['serviceID'];
           $response[$row['id']]['name'] = $row['name'];
           $closed = True;
        }
      }
    }
    return $response;
  }

  public function sql() {
    return $this->rqlite;
  }

}

?>
