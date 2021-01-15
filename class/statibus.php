<?php

class statibus {

  private $rqlite;

  public function __construct($rqliteIP,$rqlitePort) {
    $this->rqlite = new rqlite($rqliteIP,$rqlitePort);
  }

  public function isDownTimeHuh($services) {
    if (isset($services['values'])) {
      foreach ($services['values'] as $service) {
        if ($service[2] == 0) { return True; }
      }
    }
    return False;
  }

  public function gimmahDowntimePercentaaages($uptime) {
    $response = array('1day' => 0,'7days' => 0,'30days' => 0);
    if (isset($uptime['values'])) {
      foreach ($uptime['values'] as $row) {
        $response['1day'] += $row[2];
        $response['7days'] += $row[3];
        $response['30days'] += $row[5];
      }
    } else {
      return array('1day' => 100,'7days' => 100,'30days' => 100);
    }
    $response['1day'] = round($response['1day'] / count($uptime['values']),2);
    $response['7days'] = round($response['7days'] / count($uptime['values']),2);
    $response['30days'] = round($response['30days'] / count($uptime['values']),2);
    return $response;
  }

  public function sql() {
    return $this->rqlite;
  }

}

?>
