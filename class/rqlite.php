<?php

class rqlite {

  private $node;
  private $port;

  public function __construct($node="127.0.0.1",$port=4001) {
        $this->node = $node;
        $this->port = $port;
    }

  public function fetchData($url,$method = "GET",$postfields = NULL) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,20);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
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
    return $result;
  }

  public function init() {
    //services
    $result = $this->fetchData('http://'.$this->node.':'.$this->port.'/db/execute?pretty&timings','POST',
    '["CREATE TABLE services (id INTEGER NOT NULL PRIMARY KEY,name TEXT NOT NULL, method TEXT NOT NULL,target TEXT NOT NULL)"]');
    return $result;
  }

  public function insert($input) {
    $command = SQLite3::escapeString($input);
    $result = $this->fetchData('http://'.$this->node.':'.$this->port.'/db/execute?pretty&timings','POST','["'.$command.'"]');
    $result['content'] = json_decode($result['content'],true);
    return $result;
  }

  public function update($input) {
    return $this->insert($input);
  }

  public function select($input) {
    $command = SQLite3::escapeString($input);
    $command = urlencode($command);
    $result = $this->fetchData('http://'.$this->node.':'.$this->port.'/db/query?level=none&pretty&timings&q='.$command);
    $result['content'] = json_decode($result['content'],true);
    return $result;
  }

  public function delete($input) {
    return $this->select($input);
  }

}

?>
