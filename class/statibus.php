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

  public function sql() {
    return $this->rqlite;
  }

}

?>
