<?php

class rqlite {

  private $node;
  private $port;

  public function __construct($node="127.0.0.1",$port=4001) {
    $this->node = $node;
    $this->port = $port;
  }

  public function fetchData($url,$method = "GET",$postfields = NULL,$raw=False,$timeout=20) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,$timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    if ($method == "POST") {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTREDIR, 3);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    }
    $result['content'] = curl_exec($ch);
    $result['http'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($raw) { return $result; }
    if ($result['http'] == 200) {
      $result['content'] = json_decode($result['content'],true);
      $result = $this->checkForErrors($result);
      return $result;
    } else {
      return False;
    }
  }

  private function checkForErrors($result) {
    foreach ($result['content']['results'] as $entry) {
      foreach ($entry as $key => $row) {
        if ($key == "error") { return array("error" => $row); }
      }
    }
    return $result;
  }

  public function init() {
    $result = $this->insert("CREATE TABLE services (id INTEGER NOT NULL PRIMARY KEY,name TEXT NOT NULL,status INTEGER NOT NULL, method TEXT NOT NULL,target TEXT NOT NULL,timeout INTEGER NOT NULL,httpcodes TEXT NOT NULL)");
    if (!$result) { return $result; }
    $result = $this->insert("CREATE TABLE outages (id INTEGER NOT NULL PRIMARY KEY,serviceID INTEGER NOT NULL,status INTEGER NOT NULL, timestamp INTEGER NOT NULL,FOREIGN KEY(serviceID) REFERENCES services(id))");
    if (!$result) { return $result; }
    $result = $this->insert("CREATE TABLE uptime (serviceID INTEGER NOT NULL PRIMARY KEY, detailed TEXT NOT NULL,oneDay DECIMAL(7,4) NOT NULL, sevenDays DECIMAL(7,4) NOT NULL, fourteenDays DECIMAL(7,4) NOT NULL, thirtyDays DECIMAL(7,4) NOT NULL, ninetyDays DECIMAL(7,4) NOT NULL, FOREIGN KEY(serviceID) REFERENCES services(id))");
    if (!$result) { return $result; }
    return $result;
  }

  public function insert($input) {
    $command = SQLite3::escapeString($input);
    $result = $this->fetchData('http://'.$this->node.':'.$this->port.'/db/execute?pretty&timings','POST','['.json_encode($command).']');
    if (!$result) { return $result; }
    return $result;
  }

  public function update($input) {
    return $this->insert($input);
  }

  public function select($input) {
    $command = SQLite3::escapeString($input);
    $command = urlencode($command);
    $result = $this->fetchData('http://'.$this->node.':'.$this->port.'/db/query?level=none&pretty&timings&q='.$command);
    if (!$result) { return $result; }
    if (isset($result['error'])) { return $result; } else { return $result['content']['results'][0]; }
  }

  public function delete($input) {
    return $this->select($input);
  }

}

?>
